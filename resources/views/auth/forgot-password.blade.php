@extends('layouts.auth')

@section('title', 'Forgot password')
@section('heading', 'Forgot password')
@section('description', 'Enter your email to receive a password reset link.')

@section('content')
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <label class="block space-y-2 text-sm font-medium" for="email">Email address
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal" placeholder="email@example.com">
            @error('email')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
        </label>
        <button class="w-full rounded-lg bg-emerald-700 px-4 py-2.5 font-medium text-white hover:bg-emerald-800" type="submit">Email password reset link</button>
    </form>
    <p class="mt-6 text-center text-sm text-muted-foreground">Or, return to <a class="underline underline-offset-4" href="{{ route('login') }}">log in</a>.</p>
@endsection
