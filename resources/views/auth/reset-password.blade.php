@extends('layouts.auth')

@section('title', 'Reset password')
@section('heading', 'Reset password')
@section('description', 'Please enter your new password below.')

@section('content')
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label class="block space-y-2 text-sm font-medium" for="email">Email
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required readonly class="w-full rounded-lg border border-input bg-muted px-3 py-2.5 font-normal">
            @error('email')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
        </label>
        <label class="block space-y-2 text-sm font-medium" for="password">New password
            <input id="password" name="password" type="password" autocomplete="new-password" required autofocus class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal">
            @error('password')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
        </label>
        <label class="block space-y-2 text-sm font-medium" for="password_confirmation">Confirm password
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal">
        </label>
        <button class="w-full rounded-lg bg-emerald-700 px-4 py-2.5 font-medium text-white hover:bg-emerald-800" type="submit">Reset password</button>
    </form>
@endsection
