<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Access Forbidden</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="h-full flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl p-8 text-center shadow-2xl border border-slate-100">
        <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-3xl font-extrabold mx-auto mb-4 border border-rose-200">
            🔒
        </div>
        <h1 class="text-2xl font-bold text-slate-900 mb-2">Access Restricted</h1>
        <p class="text-sm text-slate-500 mb-6">
            {{ $exception->getMessage() ?: 'You do not have administrative permission or security clearance to view this resource.' }}
        </p>
        <a href="{{ url('/') }}" class="inline-flex items-center justify-center w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition shadow-md">
            ← Return to Dashboard
        </a>
    </div>
</body>
</html>
