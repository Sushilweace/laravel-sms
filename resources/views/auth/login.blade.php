<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in - Greenwood International School SMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="h-full flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Logo and Brand -->
        <div class="text-center mb-8">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-indigo-600 text-white items-center justify-center font-extrabold text-2xl shadow-xl shadow-indigo-600/30 mb-3">
                G
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Greenwood International School</h1>
            <p class="text-sm text-slate-400 mt-1">End-to-End School Management Portal</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 border border-slate-100">
            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-3 rounded text-sm text-red-700 mb-6">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', !app()->isProduction() ? 'superadmin@school.test' : '') }}" required autofocus
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" value="{{ !app()->isProduction() ? 'password123' : '' }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-slate-600">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-lg shadow-indigo-600/30 transition text-sm mt-2">
                    Sign in to Portal
                </button>
            </form>

            <!-- Quick Demo Login Switcher (Local & Demo Environments Only) -->
            @if(!app()->isProduction() || config('app.demo_mode', false))
            <div class="mt-8 pt-6 border-t border-slate-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center mb-3">
                    Instant Demo Login (One-Click)
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <a href="{{ route('quick-login', 'superadmin') }}" class="p-2.5 text-center rounded-lg bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-200 font-medium text-slate-700 hover:text-indigo-600 transition">
                        🔑 Super Admin
                    </a>
                    <a href="{{ route('quick-login', 'principal') }}" class="p-2.5 text-center rounded-lg bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-200 font-medium text-slate-700 hover:text-indigo-600 transition">
                        🏫 Principal / Admin
                    </a>
                    <a href="{{ route('quick-login', 'teacher') }}" class="p-2.5 text-center rounded-lg bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-200 font-medium text-slate-700 hover:text-indigo-600 transition">
                        👩‍🏫 Teacher (Math/Sci)
                    </a>
                    <a href="{{ route('quick-login', 'accountant') }}" class="p-2.5 text-center rounded-lg bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-200 font-medium text-slate-700 hover:text-indigo-600 transition">
                        💳 Accountant
                    </a>
                    <a href="{{ route('quick-login', 'parent') }}" class="p-2.5 text-center rounded-lg bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-200 font-medium text-slate-700 hover:text-indigo-600 transition">
                        👨‍👦 Parent Portal
                    </a>
                    <a href="{{ route('quick-login', 'student') }}" class="p-2.5 text-center rounded-lg bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-200 font-medium text-slate-700 hover:text-indigo-600 transition">
                        🎒 Student Portal
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</body>
</html>
