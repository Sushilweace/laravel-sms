@extends('layouts.app')

@section('title', ($featureMeta['name'] ?? 'Feature') . ' - Plan Upgrade Required')

@section('content')
<div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden text-center p-8 sm:p-12">
        <div class="mx-auto w-20 h-20 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-4xl mb-6 shadow-inner">
            🔒
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 uppercase tracking-wider mb-3">
            Module Not Active In Current Package
        </span>

        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight sm:text-4xl mb-4">
            {{ $featureMeta['name'] ?? 'Feature Locked' }}
        </h1>

        <p class="text-base text-slate-600 max-w-xl mx-auto mb-8">
            {{ $featureMeta['description'] ?? 'This functionality is not included in your school\'s current subscription package.' }}
        </p>

        @if(!empty($suggestedTiers))
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 mb-8 text-left max-w-lg mx-auto">
                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Available in Packages:</div>
                <div class="flex flex-wrap gap-2">
                    @foreach($suggestedTiers as $tName)
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            ✨ {{ $tName }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('School Admin'))
                <a href="{{ route('settings.school') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                    ⚙️ Manage School Package & Features
                </a>
            @endif
            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-slate-300 text-base font-medium rounded-xl text-slate-700 bg-white hover:bg-slate-50 transition">
                ← Return to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
