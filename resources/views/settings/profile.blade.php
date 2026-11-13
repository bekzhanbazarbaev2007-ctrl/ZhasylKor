@extends('layouts.settings')

@section('title', 'Profile settings')
@section('heading', 'Profile settings')
@section('description', 'Update your name, email address, and phone number.')

@section('content')
    <section class="rounded-xl border border-border bg-card p-6">
        <h2 class="text-lg font-medium">Personal information</h2>
        <form method="POST" action="{{ route('profile.update') }}" class="mt-5 max-w-xl space-y-5">
            @csrf
            @method('PATCH')
            <label class="block space-y-2 text-sm font-medium" for="name">Name
                <input id="name" name="name" value="{{ old('name', auth()->user()->name) }}" autocomplete="name" required class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal">
                @error('name')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
            </label>
            <label class="block space-y-2 text-sm font-medium" for="email">Email address
                <input id="email" name="email" type="email" value="{{ old('email', auth()->user()->email) }}" autocomplete="username" required class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal">
                @error('email')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
            </label>
            <label class="block space-y-2 text-sm font-medium" for="phone">Phone number <span class="font-normal text-muted-foreground">(optional)</span>
                <input id="phone" name="phone" type="tel" value="{{ old('phone', auth()->user()->phone) }}" autocomplete="tel" class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal">
                @error('phone')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
            </label>
            @if ($mustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div class="text-sm text-amber-800">
                    <p>Your email address is unverified.</p>
                    <form method="POST" action="{{ route('verification.send') }}" class="mt-1">@csrf<button class="underline underline-offset-4">Resend the verification email</button></form>
                </div>
            @endif
            <button class="rounded-lg bg-emerald-700 px-4 py-2.5 font-medium text-white hover:bg-emerald-800" type="submit">Save changes</button>
        </form>
    </section>

    <section class="mt-6 rounded-xl border border-red-200 bg-card p-6">
        <h2 class="text-lg font-medium text-red-700">Delete account</h2>
        <p class="mt-1 text-sm text-muted-foreground">Permanently remove your account and its personal data.</p>
        <form method="POST" action="{{ route('profile.destroy') }}" class="mt-4 flex max-w-xl flex-wrap items-end gap-3">
            @csrf
            @method('DELETE')
            <label class="min-w-64 flex-1 space-y-2 text-sm font-medium" for="delete-password">Confirm with your password
                <input id="delete-password" name="password" type="password" autocomplete="current-password" required class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal">
                @error('password')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
            </label>
            <button class="rounded-lg bg-red-700 px-4 py-2.5 font-medium text-white" type="submit" onclick="return confirm('Delete your account permanently?')">Delete account</button>
        </form>
    </section>
@endsection
