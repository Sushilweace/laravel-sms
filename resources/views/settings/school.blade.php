@extends('layouts.app')

@section('title', 'School Settings & Package Management')

@section('content')
<div class="space-y-8">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 uppercase tracking-wider">
                        SaaS Configuration
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                        ● {{ $studentCount }} Active Students Enrolled
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    {{ $school->name }} - Plan & Feature Settings
                </h1>
                <p class="text-indigo-200 text-sm mt-1 max-w-2xl">
                    Configure institutional profile, manage active software modules, and calculate customized package billing tiers.
                </p>
            </div>
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 text-right min-w-[200px]">
                <div class="text-xs text-indigo-200 uppercase tracking-wider font-medium">Current Package Charge</div>
                <div class="text-2xl font-black text-white mt-0.5" id="banner-current-charge">
                    ₹ {{ number_format($currentQuote['final_charge'], 2) }}
                </div>
                <div class="text-xs text-emerald-300 mt-1 capitalize font-medium">
                    {{ $school->package_tier }} Tier • {{ $school->billing_cycle }}
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3">
            <span class="text-emerald-500 text-xl font-bold">✓</span>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('settings.school.update') }}" method="POST" id="package-form">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left 2 Cols: Tiers & Granular Feature Toggles -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Package Tiers Selector -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">1. Select Subscription Package Tier</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Pre-configured bundles or choose custom modular features</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($allTiers as $tKey => $tier)
                            <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition tier-card {{ $school->package_tier === $tKey ? 'border-indigo-600 bg-indigo-50/40 ring-2 ring-indigo-600/20' : 'border-slate-200 hover:border-slate-300 bg-white' }}"
                                data-tier="{{ $tKey }}"
                                data-base="{{ $tier['base_monthly'] }}"
                                data-student-rate="{{ $tier['per_student_monthly'] }}"
                                data-features="{{ json_encode($tier['features']) }}">
                                <input type="radio" name="package_tier" value="{{ $tKey }}" class="sr-only tier-radio" {{ $school->package_tier === $tKey ? 'checked' : '' }}>
                                
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-bold text-slate-900 text-base flex items-center gap-2">
                                        {{ $tier['name'] }}
                                        @if($tier['highlight'])
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-indigo-600 text-white uppercase tracking-wider">Popular</span>
                                        @endif
                                    </span>
                                    <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center tier-check {{ $school->package_tier === $tKey ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-300' }}">
                                        <svg class="w-3 h-3 fill-current {{ $school->package_tier === $tKey ? '' : 'hidden' }}" viewBox="0 0 20 20"><path d="M0 11l2-2 5 5L18 3l2 2L7 18z"/></svg>
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 mb-3">{{ $tier['tagline'] }}</div>
                                
                                <div class="mt-auto pt-2 border-t border-slate-100 flex items-baseline justify-between text-xs">
                                    <span class="text-slate-600">Base: <strong>₹{{ number_format($tier['base_monthly']) }}/mo</strong></span>
                                    <span class="text-indigo-600 font-semibold">
                                        @if($tKey === 'custom')
                                            Per-module rates
                                        @else
                                            +₹{{ number_format($tier['per_student_monthly']) }}/student
                                        @endif
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Granular Feature Matrix -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">2. Modular Feature Toggles</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Toggle individual capabilities to decide your exact package charge</p>
                        </div>
                        <span class="text-xs text-indigo-600 font-medium bg-indigo-50 px-3 py-1 rounded-full">
                            Enabled: <span id="enabled-count-badge">{{ count(array_filter($activeFeatures)) }}</span> of {{ count($allFeatures) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($allFeatures as $fKey => $meta)
                            <div class="p-4 rounded-xl border transition {{ !empty($activeFeatures[$fKey]) ? 'border-indigo-200 bg-indigo-50/20' : 'border-slate-200 bg-slate-50/50 opacity-80' }} flex items-start gap-3 feature-card" id="feature-card-{{ $fKey }}">
                                <div class="pt-0.5">
                                    <input type="checkbox" 
                                           name="features[{{ $fKey }}]" 
                                           value="1" 
                                           id="feat_{{ $fKey }}"
                                           data-feature="{{ $fKey }}"
                                           data-rate="{{ $meta['per_student_monthly'] }}"
                                           class="w-5 h-5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 feature-checkbox"
                                           {{ !empty($activeFeatures[$fKey]) ? 'checked' : '' }}>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <label for="feat_{{ $fKey }}" class="font-bold text-sm text-slate-900 cursor-pointer">
                                            @if($fKey === 'ai') 🤖 @elseif($fKey === 'reports') 📊 @elseif($fKey === 'fees') 💳 @elseif($fKey === 'attendance') 📅 @elseif($fKey === 'exams') 📝 @elseif($fKey === 'students') 👥 @elseif($fKey === 'staff') 👩‍🏫 @else 📢 @endif
                                            {{ $meta['name'] }}
                                        </label>
                                        <span class="text-[11px] font-semibold text-slate-500 bg-white border border-slate-200 px-2 py-0.5 rounded-md">
                                            +₹{{ number_format($meta['per_student_monthly']) }}/mo
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                        {{ $meta['description'] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- General School Institutional Profile -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">3. School Institutional Profile</h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">School Name</label>
                            <input type="text" name="name" value="{{ old('name', $school->name) }}" required
                                   class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">School Code</label>
                            <input type="text" name="code" value="{{ old('code', $school->code) }}" required
                                   class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone', $school->phone) }}"
                                   class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Official Email</label>
                            <input type="email" name="email" value="{{ old('email', $school->email) }}"
                                   class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Timezone</label>
                            <input type="text" name="timezone" value="{{ old('timezone', $school->timezone) }}" required
                                   class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Currency Code</label>
                            <input type="text" name="currency" value="{{ old('currency', $school->currency) }}" required maxlength="3"
                                   class="w-full rounded-xl border-slate-300 text-sm uppercase focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Campus Physical Address</label>
                            <textarea name="address" rows="2"
                                      class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">{{ old('address', $school->address) }}</textarea>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Col: Live Package Calculator Sidebar -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl border-2 border-indigo-200 shadow-lg p-6 sticky top-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Package Billing Summary</h3>
                            <p class="text-xs text-slate-500">Live dynamic pricing quote</p>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 uppercase tracking-wider" id="summary-tier-badge">
                            {{ $currentQuote['tier_name'] }}
                        </span>
                    </div>

                    <!-- Billing Cycle Selector -->
                    <div class="mt-4">
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Billing Cycle</label>
                        <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl">
                            <label class="cursor-pointer text-center py-2 text-xs font-bold rounded-lg transition cycle-tab {{ $school->billing_cycle === 'monthly' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                                <input type="radio" name="billing_cycle" value="monthly" class="sr-only cycle-radio" {{ $school->billing_cycle === 'monthly' ? 'checked' : '' }}>
                                Monthly
                            </label>
                            <label class="cursor-pointer text-center py-2 text-xs font-bold rounded-lg transition relative cycle-tab {{ $school->billing_cycle === 'annually' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                                <input type="radio" name="billing_cycle" value="annually" class="sr-only cycle-radio" {{ $school->billing_cycle === 'annually' ? 'checked' : '' }}>
                                Annually
                                <span class="absolute -top-2 -right-1 px-1.5 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-500 text-white shadow-sm">15% OFF</span>
                            </label>
                        </div>
                    </div>

                    <!-- Detailed Line Items -->
                    <div class="mt-6 space-y-3 text-xs">
                        <div class="flex justify-between items-center text-slate-600">
                            <span>Base Platform Charge:</span>
                            <span class="font-semibold text-slate-900" id="calc-base-monthly">₹ {{ number_format($currentQuote['base_monthly'], 2) }}</span>
                        </div>

                        <div class="flex justify-between items-center text-slate-600">
                            <span>Active Enrolled Students:</span>
                            <span class="font-semibold text-slate-900" id="calc-student-count">{{ $studentCount }}</span>
                        </div>

                        <div class="flex justify-between items-center text-slate-600">
                            <span>Per-Student Rate:</span>
                            <span class="font-semibold text-slate-900" id="calc-student-rate">₹ {{ number_format($currentQuote['per_student_rate'], 2) }}/student</span>
                        </div>

                        <div class="flex justify-between items-center text-slate-600">
                            <span>Student Volume Charge:</span>
                            <span class="font-semibold text-slate-900" id="calc-student-volume">₹ {{ number_format($currentQuote['student_charge_monthly'], 2) }}</span>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex justify-between items-center text-slate-600">
                            <span>Monthly Subtotal:</span>
                            <span class="font-bold text-slate-900" id="calc-monthly-subtotal">₹ {{ number_format($currentQuote['monthly_subtotal'], 2) }}</span>
                        </div>

                        <div class="flex justify-between items-center text-slate-600" id="calc-months-row">
                            <span>Billing Commitment:</span>
                            <span class="font-semibold text-slate-900" id="calc-months">{{ $currentQuote['months'] }} Months</span>
                        </div>

                        <div class="flex justify-between items-center text-emerald-600 font-medium" id="calc-discount-row">
                            <span>Annual Commitment Savings (15%):</span>
                            <span id="calc-discount">- ₹ {{ number_format($currentQuote['discount_amount'], 2) }}</span>
                        </div>
                    </div>

                    <!-- Final Total Display -->
                    <div class="mt-6 pt-4 border-t-2 border-indigo-100 bg-indigo-50/60 -mx-6 -mb-6 p-6 rounded-b-2xl">
                        <div class="text-xs font-bold text-indigo-900 uppercase tracking-wider mb-1">
                            Total Package Invoice Charge
                        </div>
                        <div class="text-3xl font-black text-indigo-700" id="calc-final-charge">
                            ₹ {{ number_format($currentQuote['final_charge'], 2) }}
                        </div>
                        <div class="text-xs text-indigo-500 mt-1" id="calc-effective-monthly">
                            Effective ₹ {{ number_format($currentQuote['effective_monthly'], 2) }} / month
                        </div>

                        <button type="submit" class="mt-6 w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                            <span>💾 Save & Apply Subscription</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tierRadios = document.querySelectorAll('.tier-radio');
    const tierCards = document.querySelectorAll('.tier-card');
    const featureCheckboxes = document.querySelectorAll('.feature-checkbox');
    const cycleRadios = document.querySelectorAll('.cycle-radio');
    const cycleTabs = document.querySelectorAll('.cycle-tab');
    const enabledCountBadge = document.getElementById('enabled-count-badge');

    function getSelectedTier() {
        const checked = document.querySelector('.tier-radio:checked');
        return checked ? checked.value : 'enterprise';
    }

    function getSelectedCycle() {
        const checked = document.querySelector('.cycle-radio:checked');
        return checked ? checked.value : 'monthly';
    }

    function getFeaturesMap() {
        const map = {};
        featureCheckboxes.forEach(cb => {
            map[cb.dataset.feature] = cb.checked ? 1 : 0;
        });
        return map;
    }

    // Refresh visual state of tier cards
    function updateTierCardsUI(selectedTier) {
        tierCards.forEach(card => {
            const isMatch = card.dataset.tier === selectedTier;
            const check = card.querySelector('.tier-check');
            const svg = card.querySelector('.tier-check svg');

            if (isMatch) {
                card.classList.add('border-indigo-600', 'bg-indigo-50/40', 'ring-2', 'ring-indigo-600/20');
                card.classList.remove('border-slate-200', 'bg-white');
                check.classList.add('border-indigo-600', 'bg-indigo-600', 'text-white');
                check.classList.remove('border-slate-300');
                if (svg) svg.classList.remove('hidden');
            } else {
                card.classList.remove('border-indigo-600', 'bg-indigo-50/40', 'ring-2', 'ring-indigo-600/20');
                card.classList.add('border-slate-200', 'bg-white');
                check.classList.remove('border-indigo-600', 'bg-indigo-600', 'text-white');
                check.classList.add('border-slate-300');
                if (svg) svg.classList.add('hidden');
            }
        });
    }

    // Cycle tab UI
    function updateCycleTabsUI(selectedCycle) {
        cycleTabs.forEach(tab => {
            const radio = tab.querySelector('.cycle-radio');
            if (radio.value === selectedCycle) {
                tab.classList.add('bg-white', 'text-indigo-600', 'shadow-sm');
                tab.classList.remove('text-slate-600');
            } else {
                tab.classList.remove('bg-white', 'text-indigo-600', 'shadow-sm');
                tab.classList.add('text-slate-600');
            }
        });
    }

    // Feature card visuals
    function updateFeatureCardsUI() {
        let count = 0;
        featureCheckboxes.forEach(cb => {
            const card = document.getElementById('feature-card-' + cb.dataset.feature);
            if (cb.checked) {
                count++;
                if (card) {
                    card.classList.add('border-indigo-200', 'bg-indigo-50/20');
                    card.classList.remove('border-slate-200', 'bg-slate-50/50', 'opacity-80');
                }
            } else {
                if (card) {
                    card.classList.remove('border-indigo-200', 'bg-indigo-50/20');
                    card.classList.add('border-slate-200', 'bg-slate-50/50', 'opacity-80');
                }
            }
        });
        if (enabledCountBadge) enabledCountBadge.textContent = count;
    }

    // Live calculation API caller
    async function recalculate() {
        const tier = getSelectedTier();
        const cycle = getSelectedCycle();
        const features = getFeaturesMap();

        try {
            const response = await fetch("{{ route('settings.school.calculate') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    package_tier: tier,
                    billing_cycle: cycle,
                    features: features
                })
            });

            if (!response.ok) return;

            const res = await response.json();
            if (res.success) {
                const q = res.quote;
                const f = res.formatted;

                document.getElementById('summary-tier-badge').textContent = q.tier_name;
                document.getElementById('calc-base-monthly').textContent = f.base_monthly;
                document.getElementById('calc-student-count').textContent = q.student_count;
                document.getElementById('calc-student-rate').textContent = '₹ ' + parseFloat(q.per_student_rate).toFixed(2) + '/student';
                document.getElementById('calc-student-volume').textContent = f.student_charge_monthly;
                document.getElementById('calc-monthly-subtotal').textContent = f.monthly_subtotal;
                document.getElementById('calc-months').textContent = q.months + ' Months';
                document.getElementById('calc-discount').textContent = '- ' + f.discount_amount;
                document.getElementById('calc-final-charge').textContent = f.final_charge;
                document.getElementById('calc-effective-monthly').textContent = 'Effective ' + f.effective_monthly + ' / month';

                const bannerCharge = document.getElementById('banner-current-charge');
                if (bannerCharge) bannerCharge.textContent = f.final_charge;
            }
        } catch (e) {
            console.error('Calculation error', e);
        }
    }

    // Event: Tier Selection
    tierCards.forEach(card => {
        card.addEventListener('click', function () {
            const tierKey = this.dataset.tier;
            const radio = this.querySelector('.tier-radio');
            if (radio) radio.checked = true;

            updateTierCardsUI(tierKey);

            // If not custom, auto-check tier features
            if (tierKey !== 'custom') {
                const tierFeatures = JSON.parse(this.dataset.features || '[]');
                featureCheckboxes.forEach(cb => {
                    cb.checked = tierFeatures.includes(cb.dataset.feature);
                });
            }

            updateFeatureCardsUI();
            recalculate();
        });
    });

    // Event: Feature checkbox toggle
    featureCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            // If user explicitly unchecks/checks features, mark tier as custom or recalculate
            updateFeatureCardsUI();
            recalculate();
        });
    });

    // Event: Billing Cycle change
    cycleTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const radio = this.querySelector('.cycle-radio');
            if (radio) {
                radio.checked = true;
                updateCycleTabsUI(radio.value);
                recalculate();
            }
        });
    });
});
</script>
@endpush
@endsection
