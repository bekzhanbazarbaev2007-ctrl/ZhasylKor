@extends('layouts.auth')

@section('title', 'Confirm password')
@section('heading', 'Confirm your password')
@section('description', 'This is a secure area. Please confirm your password before continuing.')

@section('content')
    <div class="space-y-4">
        <button type="button" class="w-full rounded-lg border border-border px-4 py-2.5 text-sm font-medium hover:bg-muted" data-passkey-confirm>Confirm with passkey</button>
        <p class="text-center text-sm text-red-700" data-passkey-error role="alert"></p>
    </div>
    <form method="POST" action="{{ route('password.confirm.store') }}" class="mt-5 space-y-5">
        @csrf
        <label class="block space-y-2 text-sm font-medium" for="password">Password
            <input id="password" name="password" type="password" autocomplete="current-password" required autofocus class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal" placeholder="Password">
            @error('password')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
        </label>
        <button class="w-full rounded-lg bg-emerald-700 px-4 py-2.5 font-medium text-white hover:bg-emerald-800" type="submit">Confirm password</button>
    </form>
@endsection
