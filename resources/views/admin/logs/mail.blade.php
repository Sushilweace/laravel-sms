@extends('layouts.app')

@section('title', 'Mail & Notification Sent Logs - Admin Panel')

@section('content')
<div class="space-y-6">

    <!-- Header Banner & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 uppercase tracking-wider">
                    Communications Hub
                </span>
                <span class="text-xs text-slate-500 font-mono">notification_logs table</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1">Mail, SMS & Notification Sent Logs</h1>
            <p class="text-xs text-slate-500">Track delivery status, view transmitted message payloads, and resend failed dispatches.</p>
        </div>

        <div class="flex items-center gap-2.5">
            <button onclick="document.getElementById('test-modal').classList.remove('hidden')" 
                    class="text-xs font-semibold px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition flex items-center gap-1.5">
                <span>✉</span> Send Test Dispatch
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
            <span>✓</span> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center gap-2">
            <span>⚠️</span> {{ session('error') }}
        </div>
    @endif

    <!-- Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Dispatched</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total'] }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">All channels combined</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[11px] font-semibold text-indigo-600 uppercase tracking-wider">Emails Sent</div>
            <div class="text-2xl font-black text-indigo-600 mt-1">{{ $stats['mail'] }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">SMTP / Log pipeline</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">SMS & WhatsApp</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['sms'] }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Mobile communications</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[11px] font-semibold text-blue-600 uppercase tracking-wider">Delivery Rate</div>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ $stats['delivery_rate'] }}%</div>
            <div class="text-[10px] text-slate-400 mt-0.5">{{ $stats['delivered'] }} delivered</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm col-span-2 sm:col-span-1">
            <div class="text-[11px] font-semibold text-rose-600 uppercase tracking-wider">Delivery Failures</div>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ $stats['failed'] }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Actionable issues</div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.logs.mail') }}" class="flex flex-wrap items-center gap-2 flex-1">
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-semibold text-slate-600">Channel:</span>
                <select name="channel" onchange="this.form.submit()" class="text-xs font-semibold rounded-xl border-slate-300 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="all" {{ $channel === 'all' ? 'selected' : '' }}>All Channels</option>
                    <option value="mail" {{ $channel === 'mail' ? 'selected' : '' }}>✉ Email</option>
                    <option value="sms" {{ $channel === 'sms' ? 'selected' : '' }}>📱 SMS</option>
                    <option value="whatsapp" {{ $channel === 'whatsapp' ? 'selected' : '' }}>💬 WhatsApp</option>
                    <option value="in_app" {{ $channel === 'in_app' ? 'selected' : '' }}>🔔 In-App</option>
                </select>
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-xs font-semibold text-slate-600">Status:</span>
                <select name="status" onchange="this.form.submit()" class="text-xs font-semibold rounded-xl border-slate-300 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="delivered" {{ $status === 'delivered' ? 'selected' : '' }}>✓ Delivered</option>
                    <option value="sent" {{ $status === 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>✕ Failed</option>
                    <option value="queued" {{ $status === 'queued' ? 'selected' : '' }}>⏳ Queued</option>
                </select>
            </div>

            <div class="flex-1 min-w-[200px] relative">
                <input type="text" name="q" value="{{ $search }}" placeholder="Search title, content, recipient name, email..."
                       class="w-full text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 py-1.5 pl-3 pr-8">
                @if($search)
                    <a href="{{ route('admin.logs.mail', ['channel' => $channel, 'status' => $status]) }}" class="absolute right-2.5 top-1.5 text-xs text-slate-400 hover:text-slate-600 font-bold">✕</a>
                @endif
            </div>

            <button type="submit" class="text-xs font-semibold px-3 py-1.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                Filter
            </button>
        </form>

        <span class="text-xs text-slate-500">
            Showing <strong>{{ $logs->count() }}</strong> of <strong>{{ $logs->total() }}</strong> records
        </span>
    </div>

    <!-- Sent Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 w-16">ID</th>
                        <th class="py-3 px-4 w-28">Channel</th>
                        <th class="py-3 px-4 min-w-[160px]">Recipient</th>
                        <th class="py-3 px-4 min-w-[200px]">Subject / Title</th>
                        <th class="py-3 px-4 w-24">Status</th>
                        <th class="py-3 px-4 w-36">Sent At</th>
                        <th class="py-3 px-4 text-right w-28">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono text-slate-400">#{{ $log->id }}</td>
                            <td class="py-3 px-4">
                                @if($log->channel === 'mail')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        ✉ Email
                                    </span>
                                @elseif($log->channel === 'sms')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        📱 SMS
                                    </span>
                                @elseif($log->channel === 'whatsapp')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                        💬 WhatsApp
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        🔔 In-App
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4">
                                @if($log->recipient)
                                    <div class="font-bold text-slate-900">{{ $log->recipient->name }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono truncate max-w-[180px]">
                                        {{ $log->channel === 'mail' ? $log->recipient->email : ($log->recipient->phone ?? $log->recipient->email) }}
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">User #{{ $log->recipient_id }}</span>
                                @endif
                            </td>

                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800 line-clamp-1">{{ $log->title ?: 'Notice Notification' }}</div>
                                <div class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{{ Str::limit($log->content, 60) }}</div>
                            </td>

                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                    {{ $log->status === 'delivered' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ $log->status === 'sent' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $log->status === 'failed' ? 'bg-rose-100 text-rose-800' : '' }}
                                    {{ $log->status === 'queued' ? 'bg-amber-100 text-amber-800' : '' }}">
                                    {{ $log->status }}
                                </span>
                            </td>

                            <td class="py-3 px-4 text-slate-500 font-mono text-[11px]">
                                {{ $log->sent_at ? $log->sent_at->format('d M Y, h:i A') : $log->created_at->format('d M Y, h:i A') }}
                            </td>

                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button onclick="viewMessageDetails({{ $log->id }})" 
                                            class="text-[11px] text-indigo-600 hover:text-indigo-800 font-semibold px-2 py-1 bg-indigo-50 hover:bg-indigo-100 rounded-md border border-indigo-200 transition">
                                        Preview
                                    </button>

                                    @if($log->recipient)
                                    <form action="{{ route('admin.logs.mail.resend', $log) }}" method="POST" onsubmit="return confirm('Resend this message to {{ $log->recipient->name }}?')">
                                        @csrf
                                        <button type="submit" class="text-[11px] text-slate-600 hover:text-slate-900 font-semibold px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-md transition" title="Resend Notification">
                                            ↻ Resend
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="text-3xl mb-2">✉</div>
                                <div class="font-bold text-slate-700">No Sent Notification Logs Found</div>
                                <div class="text-xs text-slate-400 mt-1">Sent emails, SMS, and WhatsApp dispatches will appear here.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal: Preview Notification Payload -->
<div id="details-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <div>
                <span id="modal-channel-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 uppercase">CHANNEL</span>
                <h3 class="text-base font-bold text-slate-900 mt-1" id="modal-title">Subject Title</h3>
            </div>
            <button onclick="document.getElementById('details-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
        </div>

        <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs">
            <div>
                <span class="text-slate-400 text-[10px] uppercase font-semibold">Recipient</span>
                <div class="font-bold text-slate-900 mt-0.5" id="modal-recipient-name">—</div>
                <div class="text-slate-500 font-mono text-[11px]" id="modal-recipient-contact">—</div>
            </div>
            <div>
                <span class="text-slate-400 text-[10px] uppercase font-semibold">Sent Timestamp</span>
                <div class="font-bold text-slate-900 mt-0.5" id="modal-sent-at">—</div>
                <div class="text-[11px] font-semibold text-emerald-600" id="modal-status">Delivered</div>
            </div>
        </div>

        <div id="modal-error-box" class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 hidden">
            <span class="font-bold block mb-0.5">Failure Reason:</span>
            <div id="modal-error-text" class="font-mono text-[11px]"></div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Transmitted Payload Body</label>
            <div class="p-4 bg-slate-900 text-slate-100 rounded-xl font-mono text-xs overflow-y-auto max-h-60 leading-relaxed border border-slate-800 whitespace-pre-wrap" id="modal-body">
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button onclick="document.getElementById('details-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Modal: Send Test Dispatch -->
<div id="test-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <h3 class="text-base font-bold text-slate-900">Send Test Notification / Email</h3>
            <button onclick="document.getElementById('test-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
        </div>

        <form action="{{ route('admin.logs.mail.test') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Target Recipient</label>
                <select name="recipient_id" required class="w-full rounded-xl border-slate-300 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach(\App\Models\User::take(20)->get() as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }} • {{ $u->getRoleNames()->first() ?? 'User' }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Communication Channel</label>
                <select name="channel" required class="w-full rounded-xl border-slate-300 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="mail">✉ Email (SMTP / Mail Log)</option>
                    <option value="sms">📱 SMS</option>
                    <option value="whatsapp">💬 WhatsApp</option>
                    <option value="in_app">🔔 In-App Notification</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Subject / Header</label>
                <input type="text" name="title" value="Test School Notification" required
                       class="w-full rounded-xl border-slate-300 py-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Message Body</label>
                <textarea name="content" rows="3" required
                          class="w-full rounded-xl border-slate-300 py-2 focus:ring-indigo-500 focus:border-indigo-500">This is an automated test dispatch from Greenwood International School Management System.</textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('test-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm transition">
                    Dispatch Message
                </button>
            </div>
        </form>
    </div>
</div>

<script>
async function viewMessageDetails(id) {
    try {
        const response = await fetch("{{ url('admin/logs/mail') }}/" + id, {
            headers: { 'Accept': 'application/json' }
        });
        if (!response.ok) return;

        const data = await response.json();
        document.getElementById('modal-channel-badge').textContent = data.channel;
        document.getElementById('modal-title').textContent = data.title || 'Untitled Notification';
        document.getElementById('modal-recipient-name').textContent = data.recipient_name;
        document.getElementById('modal-recipient-contact').textContent = (data.recipient_email || '') + (data.recipient_phone ? ' • ' + data.recipient_phone : '');
        document.getElementById('modal-sent-at').textContent = data.sent_at || data.created_at;
        document.getElementById('modal-status').textContent = data.status.toUpperCase();
        document.getElementById('modal-body').textContent = data.content;

        const errBox = document.getElementById('modal-error-box');
        if (data.error_message) {
            document.getElementById('modal-error-text').textContent = data.error_message;
            errBox.classList.remove('hidden');
        } else {
            errBox.classList.add('hidden');
        }

        document.getElementById('details-modal').classList.remove('hidden');
    } catch (e) {
        console.error(e);
    }
}
</script>
@endsection
