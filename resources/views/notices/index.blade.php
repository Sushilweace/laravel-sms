@extends('layouts.app')

@section('title', 'Notice Board')
@section('page-title', 'School Announcements & Notice Board')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between flex-wrap gap-4">
        <div>
            <h3 class="font-bold text-slate-900 text-sm">Official School Circulars & Bulletins</h3>
            <p class="text-xs text-slate-500 mt-0.5">Targeted communication for faculty, parents, and students.</p>
        </div>

        @can('notices.create')
        <a href="{{ route('notices.create') }}" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl shadow-md transition">
            + Compose Announcement
        </a>
        @endcan
    </div>

    <!-- Notices Cards List -->
    <div class="space-y-4">
        @forelse($notices as $notice)
        @php
            $isRead = $notice->reads->isNotEmpty();
            $isAck = (bool) $notice->reads->first()?->acknowledged_at;
        @endphp
        <div class="bg-white rounded-2xl border {{ $notice->is_pinned ? 'border-amber-300 ring-1 ring-amber-200' : 'border-slate-200' }} shadow-sm p-6 space-y-3">
            <div class="flex items-start justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    @if($notice->is_pinned)
                    <span class="text-amber-500 text-base" title="Pinned Announcement">📌</span>
                    @endif

                    <h3 class="font-bold text-base text-slate-900">{{ $notice->title }}</h3>

                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                        {{ $notice->priority === 'emergency' ? 'bg-rose-100 text-rose-700' : '' }}
                        {{ $notice->priority === 'important' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $notice->priority === 'normal' ? 'bg-slate-100 text-slate-600' : '' }}">
                        {{ $notice->priority }}
                    </span>
                </div>

                <div class="text-xs text-slate-400">
                    {{ $notice->published_at ? $notice->published_at->format('d M Y, h:i A') : $notice->created_at->format('d M Y') }}
                </div>
            </div>

            <!-- Content -->
            <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-line">{{ $notice->content }}</p>

            <!-- Footer: Author & Acknowledgement -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between flex-wrap gap-3 text-xs">
                <div class="text-slate-500">
                    Posted by: <strong class="text-slate-800">{{ $notice->creator->name }}</strong>
                </div>

                @if($notice->requires_ack)
                    @if($isAck)
                    <span class="bg-emerald-100 text-emerald-800 px-3 py-1 rounded-lg text-xs font-semibold flex items-center gap-1.5">
                        ✓ Acknowledged by You
                    </span>
                    @else
                    <form action="{{ route('notices.ack', $notice) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-1.5 rounded-lg text-xs transition">
                            ✓ Confirm & Acknowledge
                        </button>
                    </form>
                    @endif
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-sm text-slate-500">
            No notices published on the notice board yet.
        </div>
        @endforelse
    </div>

    @if($notices->hasPages())
    <div>{{ $notices->links() }}</div>
    @endif

</div>
@endsection
