<!doctype html>
<html lang="en" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full bg-slate-50">
  <div class="min-h-full flex items-center justify-center px-4">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow">
      <div class="text-center">
        <div class="mx-auto h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600"></div>
        <h1 class="mt-3 text-xl font-extrabold text-slate-900">Sign in</h1>
        <p class="mt-1 text-sm text-slate-500">Login to continue</p>
      </div>

      @if($errors->any())
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('login.post') }}" class="mt-5 space-y-4">
        @csrf

        <div>
          <label class="text-sm font-semibold">Email</label>
          <input name="email" value="{{ old('email') }}" type="email" required
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500/30">
        </div>

        <div>
          <label class="text-sm font-semibold">Password</label>
          <input name="password" type="password" required
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500/30">
        </div>

        <div class="flex items-center justify-between">
          <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300">
            Remember me
          </label>
        </div>

        <button class="w-full rounded-2xl bg-indigo-600 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
          Login
        </button>
      </form>
    </div>
  </div>
</body>
</html>
