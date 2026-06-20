<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Career Educate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Outfit', sans-serif; }</style>
    @include('partials.anti-copy')
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">
    @include('partials.results-header')

    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="space-y-8">
            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Student Account</p>
                    <h1 class="mt-2 text-2xl font-extrabold text-slate-950">Profile Settings</h1>
                    <p class="mt-1 text-sm text-slate-500">Update your account details and NEET counselling defaults.</p>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="p-6 space-y-6">
                    @csrf

                    <!-- Section 1: Personal Details -->
                    <div>
                        <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-2">Personal Information</h2>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="name" class="block text-xs font-bold uppercase tracking-wide text-slate-500">Full Name</label>
                                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                                    class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            </div>

                            <div>
                                <label for="phone" class="block text-xs font-bold uppercase tracking-wide text-slate-500">Mobile Number</label>
                                <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" required
                                    class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            </div>

                            <div class="md:col-span-2">
                                <label for="email" class="block text-xs font-bold uppercase tracking-wide text-slate-500">Email Address</label>
                                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                                    class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: NEET Academic / Predictor Details -->
                    <div class="pt-4 border-t border-slate-100">
                        <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-2">NEET Counselling Settings</h2>
                        <p class="mt-1 text-xs text-slate-500">These values will be saved as default reference criteria for the results.</p>
                        
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="neet_rank" class="block text-xs font-bold uppercase tracking-wide text-slate-500">NEET All India Rank (AIR)</label>
                                <input id="neet_rank" name="neet_rank" type="number" value="{{ old('neet_rank', $user->neet_rank) }}"
                                    placeholder="e.g. 15420"
                                    class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            </div>

                            <div>
                                <label for="neet_marks" class="block text-xs font-bold uppercase tracking-wide text-slate-500">NEET Marks / Score (Max 720)</label>
                                <input id="neet_marks" name="neet_marks" type="number" step="0.01" value="{{ old('neet_marks', $user->neet_marks) }}"
                                    placeholder="e.g. 620"
                                    class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            </div>

                            <div>
                                <label for="state" class="block text-xs font-bold uppercase tracking-wide text-slate-500">State of Residence</label>
                                <select id="state" name="state"
                                    class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                                    <option value="">Select State</option>
                                    @foreach ($states as $stateName)
                                        <option value="{{ $stateName }}" @selected(old('state', $user->state) === $stateName)>{{ $stateName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="quota" class="block text-xs font-bold uppercase tracking-wide text-slate-500">Default Quota</label>
                                <select id="quota" name="quota"
                                    class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                                    <option value="">Select Quota</option>
                                    @foreach ($quotas as $qVal)
                                        <option value="{{ $qVal }}" @selected(old('quota', $user->quota) === $qVal)>{{ $qVal }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <p class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-medium leading-5 text-slate-500">
                                Category values such as OPEN, OBC, SC, ST, EWS, and sheet-specific category labels are now available inside Default Quota.
                            </p>
                        </div>
                    </div>

                    <!-- Section 3: Change Password -->
                    <div class="pt-4 border-t border-slate-100">
                        <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-2">Change Password</h2>
                        <p class="mt-1 text-xs text-slate-500">Leave these fields blank to keep your current password.</p>
                        
                        <div class="mt-4 grid gap-4 md:grid-cols-3">
                            <div>
                                <label for="current_password" class="block text-xs font-bold uppercase tracking-wide text-slate-500">Current Password</label>
                                <input id="current_password" name="current_password" type="password"
                                    class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            </div>

                            <div>
                                <label for="new_password" class="block text-xs font-bold uppercase tracking-wide text-slate-500">New Password</label>
                                <input id="new_password" name="new_password" type="password"
                                    class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            </div>

                            <div>
                                <label for="new_password_confirmation" class="block text-xs font-bold uppercase tracking-wide text-slate-500">Confirm New Password</label>
                                <input id="new_password_confirmation" name="new_password_confirmation" type="password"
                                    class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="rounded-xl bg-rose-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-rose-500/10 transition hover:bg-rose-600 active:scale-95">
                            Save Changes
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>

</html>
