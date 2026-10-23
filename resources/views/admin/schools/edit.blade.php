@extends('layouts.app')

@section('title', 'Edit School - ' . $school->name)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.schools.show', $school) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1">
                    ← Back to {{ $school->name }} Details
                </a>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1">Edit School / Campus Settings</h1>
            <p class="text-xs text-slate-500">Update identity, campus hierarchy, contact info, and subscription pricing.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 font-mono">
                Code: {{ $school->code }}
            </span>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1">
            <div class="font-bold flex items-center gap-2">
                <span>⚠️</span> Please fix the following errors:
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.schools.update', $school) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. School Information & Hierarchy -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-base font-bold text-slate-900">1. School Information & Campus Hierarchy</h2>
                <p class="text-xs text-slate-500">Update institution profile and parent campus linkage.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Name -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        School / Institution Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $school->name) }}" required
                           class="w-full text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3">
                </div>

                <!-- Code -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Unique Institutional Code <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="code" value="{{ old('code', $school->code) }}" required
                           class="w-full text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3 uppercase font-mono">
                    <p class="text-[10px] text-slate-400 mt-1">Short alphanumeric abbreviation used for identification.</p>
                </div>

                <!-- Parent School (Hierarchy) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Parent Institution / Main Campus (parent_id)
                    </label>
                    <select name="parent_id" class="w-full text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3">
                        <option value="">-- Independent Primary / Root School --</option>
                        @foreach($parentSchools as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id', $school->parent_id) == $parent->id ? 'selected' : '' }}>
                                ↳ Branch of: {{ $parent->name }} ({{ $parent->code }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1">Assigning a parent converts this into a child campus branch.</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Contact Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $school->email) }}"
                           class="w-full text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3">
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Contact Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $school->phone) }}"
                           class="w-full text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3">
                </div>

                <!-- Timezone -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Timezone <span class="text-rose-500">*</span></label>
                    <input type="text" name="timezone" value="{{ old('timezone', $school->timezone) }}" required
                           class="w-full text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3">
                </div>

                <!-- Currency -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Currency Code <span class="text-rose-500">*</span></label>
                    <input type="text" name="currency" value="{{ old('currency', $school->currency) }}" required maxlength="3"
                           class="w-full text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3 uppercase font-mono">
                </div>

                <!-- Address -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Physical Address</label>
                    <textarea name="address" rows="2"
                              class="w-full text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3">{{ old('address', $school->address) }}</textarea>
                </div>
            </div>
        </div>

        <!-- 2. Subscription Package & Features -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-base font-bold text-slate-900">2. SaaS Subscription Plan & Tier</h2>
                <p class="text-xs text-slate-500">Configure tier and billing cycle. Granular module toggles can be managed in School Settings.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Subscription Plan Tier <span class="text-rose-500">*</span>
                    </label>
                    <select name="package_tier" class="w-full text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3">
                        @foreach($allTiers as $tierKey => $tierData)
                            <option value="{{ $tierKey }}" {{ old('package_tier', $school->package_tier) === $tierKey ? 'selected' : '' }}>
                                {{ $tierData['name'] }} — Base: ₹ {{ number_format((float) ($tierData['base_monthly'] ?? 0)) }} /mo
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Billing Frequency <span class="text-rose-500">*</span>
                    </label>
                    <select name="billing_cycle" class="w-full text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3">
                        <option value="monthly" {{ old('billing_cycle', $school->billing_cycle) === 'monthly' ? 'selected' : '' }}>Monthly Billing</option>
                        <option value="annually" {{ old('billing_cycle', $school->billing_cycle) === 'annually' ? 'selected' : '' }}>Annually Billing (Includes 15% discount)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between gap-3 pt-2">
            <a href="{{ route('admin.schools.show', $school) }}" class="text-xs font-semibold px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 transition">
                Cancel
            </a>
            <button type="submit" class="text-xs font-semibold px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                Save School Changes
            </button>
        </div>
    </form>

    <!-- Danger Zone: Delete Campus -->
    <div class="bg-rose-50/50 rounded-2xl border border-rose-200 p-6 space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-rose-900">Delete School / Campus</h3>
                <p class="text-xs text-rose-700">
                    Deleting a school will unbind any child branch campuses. Note: The primary root school cannot be deleted.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.schools.destroy', $school) }}" onsubmit="return confirm('Are you sure you want to permanently delete \'{{ $school->name }}\'? Child branches will be detached.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs font-semibold px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition">
                    Delete School
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
