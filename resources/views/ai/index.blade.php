@extends('layouts.app')

@section('title', 'AI Assistant')
@section('page-title', 'AI Features Hub & Suggestions')

@section('content')
<div class="space-y-8">

    <!-- AI Blueprint Introduction Banner -->
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 p-6 rounded-2xl text-white shadow-xl flex items-center justify-between flex-wrap gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-400/20 text-amber-300 text-xs font-semibold mb-2">
                ⚡ School AI Architecture Blueprint
            </div>
            <h2 class="text-xl font-bold tracking-tight">AI-Assisted Educational Workflow</h2>
            <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
                Adhering to strict safety guidelines: AI assists, humans decide. AI never mutates official marks or fees directly.
                All outputs produce reviewable suggestions subject to educator approval.
            </p>
        </div>
        <div class="text-right">
            <span class="text-[11px] text-slate-400 block font-mono">Gateway Driver:</span>
            <span class="text-xs font-bold text-emerald-400 font-mono bg-emerald-950/60 px-2.5 py-1 rounded-lg border border-emerald-800">
                Mock/Active LLM Driver
            </span>
        </div>
    </div>

    <!-- AI Tooling Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- 1. Notice Writer Assistant -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                    📝
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Notice & Circular Drafter</h3>
                    <p class="text-[11px] text-slate-500">Drafts official circulars and notices based on key topics.</p>
                </div>
            </div>

            <form id="noticeDraftForm" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Announcement Topic / Brief</label>
                    <input type="text" id="noticeTopic" required placeholder="e.g. Science Exhibition next Friday"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tone</label>
                    <select id="noticeTone" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        <option value="formal">Formal & Official</option>
                        <option value="urgent">Urgent Reminder</option>
                        <option value="friendly">Encouraging & Friendly</option>
                    </select>
                </div>
                <button type="button" onclick="generateNoticeDraft()" id="btnDraftNotice"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-xl transition">
                    ⚡ Generate Draft Announcement
                </button>
            </form>

            <div id="noticeResultBox" class="hidden mt-3 p-4 bg-slate-50 rounded-xl border border-slate-200">
                <span class="text-[10px] font-bold text-slate-400 uppercase">AI Generated Proposal:</span>
                <p id="noticeDraftText" class="text-xs text-slate-800 mt-1 whitespace-pre-line leading-relaxed"></p>
                <div class="mt-3 pt-2 border-t border-slate-200 flex justify-end">
                    <a href="{{ route('notices.create') }}" class="text-xs text-indigo-600 font-semibold hover:underline">
                        Open in Notice Composer →
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. Report Card Remark Assistant -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                    🎓
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Report Card Remark Helper</h3>
                    <p class="text-[11px] text-slate-500">Formulates constructive teacher feedback from exam performance.</p>
                </div>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Select Examination</label>
                    <select id="remarkExamId" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        @foreach($exams as $ex)
                        <option value="{{ $ex->id }}">{{ $ex->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Student Enrollment ID</label>
                    <input type="number" id="remarkEnrollmentId" value="1" placeholder="e.g. 1"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <button type="button" onclick="generateReportRemark()" id="btnDraftRemark"
                    class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-xl transition">
                    ⚡ Formulate Constructive Remark
                </button>
            </div>

            <div id="remarkResultBox" class="hidden mt-3 p-4 bg-slate-50 rounded-xl border border-slate-200">
                <span class="text-[10px] font-bold text-slate-400 uppercase">Suggested Teacher Feedback:</span>
                <p id="remarkDraftText" class="text-xs text-slate-800 mt-1 italic leading-relaxed"></p>
            </div>
        </div>

    </div>

    <!-- AI Suggestion Audit & Human Review Log -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h3 class="font-bold text-slate-900 text-sm mb-4">AI Audit Trail & Human Review Log</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-4">Feature</th>
                        <th class="py-2.5 px-4">Target Type</th>
                        <th class="py-2.5 px-4">Generated Suggestion</th>
                        <th class="py-2.5 px-4">Review Status</th>
                        <th class="py-2.5 px-4 text-right">Human Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($suggestions as $sug)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-bold text-slate-800 font-mono">{{ $sug->feature }}</td>
                        <td class="py-3 px-4 text-slate-500">{{ $sug->target_type }} #{{ $sug->target_id }}</td>
                        <td class="py-3 px-4 text-slate-700 max-w-md truncate">{{ $sug->suggested_content }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $sug->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                {{ $sug->status === 'rejected' ? 'bg-rose-100 text-rose-700' : '' }}
                                {{ $sug->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}">
                                {{ $sug->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right space-x-1">
                            @if($sug->status === 'pending')
                            <form action="{{ route('ai.review-suggestion', $sug) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold px-2 py-1 rounded text-xs border border-emerald-200">
                                    Approve
                                </button>
                            </form>
                            <form action="{{ route('ai.review-suggestion', $sug) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold px-2 py-1 rounded text-xs border border-rose-200">
                                    Reject
                                </button>
                            </form>
                            @else
                            <span class="text-slate-400 text-[11px]">Reviewed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-400">No AI requests logged yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    async function generateNoticeDraft() {
        const topic = document.getElementById('noticeTopic').value;
        const tone = document.getElementById('noticeTone').value;
        const btn = document.getElementById('btnDraftNotice');

        if (!topic) return alert('Please enter a brief topic.');

        btn.disabled = true;
        btn.innerText = 'Generating...';

        try {
            const res = await fetch('{{ route("ai.draft-notice") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ topic, tone })
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('noticeDraftText').innerText = data.draft;
                document.getElementById('noticeResultBox').classList.remove('hidden');
            }
        } catch (e) {
            alert('Failed to generate draft.');
        } finally {
            btn.disabled = false;
            btn.innerText = '⚡ Generate Draft Announcement';
        }
    }

    async function generateReportRemark() {
        const examId = document.getElementById('remarkExamId').value;
        const enrollmentId = document.getElementById('remarkEnrollmentId').value;
        const btn = document.getElementById('btnDraftRemark');

        btn.disabled = true;
        btn.innerText = 'Consulting AI...';

        try {
            const res = await fetch('{{ route("ai.generate-remarks") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ exam_id: examId, enrollment_id: enrollmentId })
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('remarkDraftText').innerText = '"' + data.remark + '"';
                document.getElementById('remarkResultBox').classList.remove('hidden');
            }
        } catch (e) {
            alert('Failed to formulate remark.');
        } finally {
            btn.disabled = false;
            btn.innerText = '⚡ Formulate Constructive Remark';
        }
    }
</script>
@endsection
