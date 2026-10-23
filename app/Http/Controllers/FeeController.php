<?php

namespace App\Http\Controllers;

use App\Actions\Fees\CollectPayment;
use App\Actions\Fees\GenerateInvoices;
use App\Actions\Fees\ReversePayment;
use App\Models\AcademicYear;
use App\Models\FeeHead;
use App\Models\FeeStructure;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FeeController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $currentYear = AcademicYear::current();

        // 1. If user is Student or Parent, show their personalized student fee & dues portal
        if ($user->hasRole(['Student', 'Parent'])) {
            $student = null;
            if ($user->hasRole('Student')) {
                $student = $user->student ?? Student::where('user_id', $user->id)->first();
            } elseif ($user->hasRole('Parent')) {
                $guardian = $user->guardian ?? Guardian::where('user_id', $user->id)->first();
                $student = $guardian?->students()->first();
            }

            $invoices = Invoice::visibleTo($user)
                ->where('academic_year_id', $currentYear?->id)
                ->with(['enrollment.student', 'enrollment.section.schoolClass', 'items.feeHead', 'payments'])
                ->latest('created_at')
                ->get();

            $payments = Payment::whereIn('invoice_id', $invoices->pluck('id'))
                ->where('status', 'completed')
                ->with(['invoice.enrollment.student', 'collector'])
                ->latest()
                ->get();

            $today = now()->toDateString();
            $myTotalInvoiced = $invoices->sum('total_amount');
            $myTotalPaid = $invoices->sum('paid_total');
            $myBalanceDue = $invoices->sum(fn ($i) => (float) $i->balance_due);

            $overdueInvoices = $invoices->filter(fn ($i) => $i->status !== 'paid' && $i->due_on && $i->due_on < $today && (float) $i->balance_due > 0);
            $myOverdueAmount = $overdueInvoices->sum(fn ($i) => (float) $i->balance_due);
            $myOverdueCount = $overdueInvoices->count();

            $studentStats = [
                'totalInvoiced' => $myTotalInvoiced,
                'totalPaid' => $myTotalPaid,
                'balanceDue' => $myBalanceDue,
                'overdueAmount' => $myOverdueAmount,
                'overdueCount' => $myOverdueCount,
            ];

            return view('fees.student', compact('student', 'invoices', 'payments', 'studentStats', 'currentYear', 'overdueInvoices'));
        }

        // 2. Only Accountants and Admins can see the master institutional ledger
        if (! $user->hasAnyRole(['Super Admin', 'School Admin', 'Accountant'])) {
            abort(403, 'Unauthorized. Fee management and institutional financials are reserved for accounts personnel.');
        }

        $feeHeads = FeeHead::all();
        $classes = SchoolClass::orderBy('numeric_level')->get();
        $structures = FeeStructure::where('academic_year_id', $currentYear?->id)
            ->with(['feeHead', 'schoolClass'])
            ->get();

        $stats = [
            'totalInvoiced' => Invoice::where('academic_year_id', $currentYear?->id)->sum('total_amount'),
            'totalCollected' => Payment::where('status', 'completed')->sum('amount'),
            'totalPending' => Invoice::where('academic_year_id', $currentYear?->id)->whereIn('status', ['open', 'partial', 'overdue'])->sum('total_amount'),
        ];

        return view('fees.index', compact('feeHeads', 'classes', 'structures', 'stats', 'currentYear'));
    }

    public function storeHead(Request $request): RedirectResponse
    {
        if (! Auth::user()->hasAnyRole(['Super Admin', 'School Admin', 'Accountant'])) {
            abort(403, 'Unauthorized. Financial operations require Accountant or Administrator permissions.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:80',
            'code' => 'required|string|max:30|unique:fee_heads,code',
            'description' => 'nullable|string|max:255',
            'is_refundable' => 'boolean',
        ]);

        FeeHead::create($data);

        return back()->with('success', "Fee Head {$data['name']} created.");
    }

    public function storeStructure(Request $request): RedirectResponse
    {
        if (! Auth::user()->hasAnyRole(['Super Admin', 'School Admin', 'Accountant'])) {
            abort(403, 'Unauthorized. Financial operations require Accountant or Administrator permissions.');
        }

        $data = $request->validate([
            'fee_head_id' => 'required|exists:fee_heads,id',
            'school_class_id' => 'required|exists:school_classes,id',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:once,monthly,quarterly,yearly',
        ]);

        $data['academic_year_id'] = AcademicYear::current()?->id;

        FeeStructure::updateOrCreate(
            [
                'fee_head_id' => $data['fee_head_id'],
                'school_class_id' => $data['school_class_id'],
                'academic_year_id' => $data['academic_year_id'],
            ],
            $data
        );

        return back()->with('success', 'Fee structure updated successfully.');
    }

    public function invoices(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $currentYear = AcademicYear::current();

        $query = Invoice::visibleTo($user)
            ->where('academic_year_id', $currentYear?->id)
            ->with(['enrollment.student', 'enrollment.section.schoolClass', 'items.feeHead']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                    ->orWhereHas('enrollment.student', function ($sub) use ($s) {
                        $sub->where('first_name', 'like', "%{$s}%")
                            ->orWhere('last_name', 'like', "%{$s}%")
                            ->orWhere('admission_no', 'like', "%{$s}%");
                    });
            });
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        return view('fees.invoices', compact('invoices', 'currentYear'));
    }

    public function generateInvoices(Request $request, GenerateInvoices $generator): RedirectResponse
    {
        if (! Auth::user()->hasAnyRole(['Super Admin', 'School Admin', 'Accountant'])) {
            abort(403, 'Unauthorized. Generating school invoices requires accountant permissions.');
        }

        $data = $request->validate([
            'period_key' => 'required|string|max:50',
            'due_date' => 'required|date',
        ]);

        try {
            $result = $generator->execute($data['period_key'], $data['due_date']);

            return back()->with('success', "Invoices generated: {$result['generated']}, skipped (already existed): {$result['skipped']}.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function collectView(Invoice $invoice): View
    {
        if (! Auth::user()->hasAnyRole(['Super Admin', 'School Admin', 'Accountant'])) {
            abort(403, 'Unauthorized. Fee collection is restricted to accounting personnel.');
        }

        $invoice->load(['enrollment.student', 'enrollment.section.schoolClass', 'items.feeHead', 'payments.collector']);

        return view('fees.collect', compact('invoice'));
    }

    public function collectSubmit(Invoice $invoice, Request $request, CollectPayment $action): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->hasAnyRole(['Super Admin', 'School Admin', 'Accountant'])) {
            abort(403, 'Unauthorized. Fee collection is restricted to accounting personnel.');
        }

        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,upi,card,bank,cheque,online',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            $payment = $action->execute($invoice, $data['amount'], $data['payment_method'], $user, $data['reference'] ?? null, $data['notes'] ?? null);

            return redirect()->route('fees.receipt', $payment)->with('success', "Payment {$payment->payment_number} recorded successfully.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function reverse(Payment $payment, Request $request, ReversePayment $action): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->hasAnyRole(['Super Admin', 'School Admin', 'Accountant'])) {
            abort(403, 'Unauthorized. Reversing payments requires administrator or accountant authorization.');
        }

        $data = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        try {
            $action->execute($payment, $data['reason'], $user);

            return back()->with('success', "Payment {$payment->payment_number} reversed successfully.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function receipt(Payment $payment, Request $request): View|Response
    {
        /** @var User $user */
        $user = Auth::user();

        // Ensure non-accounts users (students, parents) can only access receipts of their own invoices
        if (! $user->hasAnyRole(['Super Admin', 'School Admin', 'Accountant'])) {
            $isOwnInvoice = Invoice::visibleTo($user)->where('id', $payment->invoice_id)->exists();
            if (! $isOwnInvoice) {
                abort(403, 'Unauthorized access to this payment receipt.');
            }
        }

        $payment->load(['invoice.enrollment.student', 'invoice.enrollment.section.schoolClass', 'collector']);
        $school = School::current() ?? School::first();

        $data = compact('payment', 'school');

        if ($request->boolean('download')) {
            $pdf = Pdf::loadView('fees.receipt-pdf', $data);

            return $pdf->download("Receipt-{$payment->payment_number}.pdf");
        }

        return view('fees.receipt', $data);
    }

    public function defaulters(): View
    {
        /** @var User $user */
        $user = Auth::user();

        // Overdue defaulters list of all students is strictly restricted to accountants and administrators
        if (! $user->hasAnyRole(['Super Admin', 'School Admin', 'Accountant'])) {
            abort(403, 'Unauthorized. The institutional overdue defaulters register can only be viewed by accountants and administrators.');
        }

        $currentYear = AcademicYear::current();
        $overdueInvoices = Invoice::where('academic_year_id', $currentYear?->id)
            ->overdue()
            ->with(['enrollment.student', 'enrollment.section.schoolClass'])
            ->get();

        return view('fees.defaulters', compact('overdueInvoices', 'currentYear'));
    }
}
