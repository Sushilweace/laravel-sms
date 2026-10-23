<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ErrorLogController extends Controller
{
    protected string $logPath;

    public function __construct()
    {
        $this->logPath = storage_path('logs/laravel.log');
    }

    public function index(Request $request): View
    {
        $entries = $this->parseLogFile();
        $selectedLevel = $request->input('level', 'ALL');
        $search = strtolower(trim($request->input('q', '')));

        // Calculate statistics before filtering
        $totalCount = count($entries);
        $errorCount = 0;
        $criticalCount = 0;
        $todayCount = 0;
        $todayStr = now()->format('Y-m-d');

        foreach ($entries as $e) {
            if (in_array($e['level'], ['ERROR', 'CRITICAL', 'EMERGENCY', 'ALERT'])) {
                $errorCount++;
            }
            if (in_array($e['level'], ['CRITICAL', 'EMERGENCY', 'ALERT'])) {
                $criticalCount++;
            }
            if (str_starts_with($e['timestamp'], $todayStr)) {
                $todayCount++;
            }
        }

        // Apply filtering
        $filtered = array_filter($entries, function ($item) use ($selectedLevel, $search) {
            if ($selectedLevel !== 'ALL' && $item['level'] !== $selectedLevel) {
                return false;
            }
            if ($search !== '') {
                $haystack = strtolower($item['message'].' '.$item['stack'].' '.$item['timestamp']);
                if (! str_contains($haystack, $search)) {
                    return false;
                }
            }

            return true;
        });

        $fileSizeKb = File::exists($this->logPath) ? round(File::size($this->logPath) / 1024, 2) : 0;
        $paginated = array_slice($filtered, 0, 100);

        return view('admin.logs.errors', [
            'entries' => $paginated,
            'totalCount' => $totalCount,
            'errorCount' => $errorCount,
            'criticalCount' => $criticalCount,
            'todayCount' => $todayCount,
            'fileSizeKb' => $fileSizeKb,
            'selectedLevel' => $selectedLevel,
            'search' => $request->input('q', ''),
            'levels' => ['ALL', 'ERROR', 'CRITICAL', 'WARNING', 'INFO', 'DEBUG'],
        ]);
    }

    public function download(): BinaryFileResponse
    {
        if (! File::exists($this->logPath)) {
            File::put($this->logPath, '');
        }

        return response()->download($this->logPath, 'laravel-'.now()->format('Y-m-d-His').'.log');
    }

    public function clear(): RedirectResponse
    {
        if (File::exists($this->logPath)) {
            File::put($this->logPath, '');
        }

        return back()->with('success', 'System error log has been purged and cleared.');
    }

    /**
     * Parse raw Laravel log format into structured objects.
     */
    protected function parseLogFile(): array
    {
        if (! File::exists($this->logPath)) {
            return [];
        }

        $content = File::get($this->logPath);
        if (empty(trim($content))) {
            return [];
        }

        // Standard Laravel Log pattern: [YYYY-MM-DD HH:MM:SS] env.LEVEL: Message
        $pattern = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] ([a-zA-Z0-9_-]+)\.([A-Z]+): (.*?)(?=\n\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]|$)/ms';

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $parsed = [];
        foreach ($matches as $idx => $match) {
            $timestamp = $match[1];
            $env = $match[2];
            $level = strtoupper($match[3]);
            $body = trim($match[4]);

            // Separate main message from stack trace if present
            $stack = '';
            if (str_contains($body, '[stacktrace]')) {
                $parts = explode('[stacktrace]', $body, 2);
                $message = trim($parts[0]);
                $stack = trim($parts[1] ?? '');
            } elseif (str_contains($body, '#0 ')) {
                $pos = strpos($body, '#0 ');
                $message = trim(substr($body, 0, $pos));
                $stack = trim(substr($body, $pos));
            } else {
                $message = $body;
            }

            $parsed[] = [
                'id' => $idx + 1,
                'timestamp' => $timestamp,
                'env' => $env,
                'level' => $level,
                'message' => $message,
                'stack' => $stack,
            ];
        }

        // Return latest first
        return array_reverse($parsed);
    }
}
