@extends('layouts.auth')

@section('title', 'Create an account')
@section('heading', 'Create an account')
@section('description', 'Enter your details below to create your account.')

@section('content')
    @if ($teamInvitation)
        <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900">
            You have been invited to join <strong>{{ $teamInvitation['teamName'] }}</strong>.
        </div>
    @endif
    <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
        @csrf
        @if ($teamInvitation)<input type="hidden" name="invitation" value="{{ $teamInvitation['code'] }}">@endif
        <label class="block space-y-2 text-sm font-medium" for="name">Name
            <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal" placeholder="Full name">
            @error('name')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
        </label>
        <label class="block space-y-2 text-sm font-medium" for="email">Email address
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal" placeholder="email@example.com">
            @error('email')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
        </label>
        <label class="block space-y-2 text-sm font-medium" for="password">Password
            <input id="password" name="password" type="password" autocomplete="new-password" required class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal">
            @error('password')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
        </label>
        <label class="block space-y-2 text-sm font-medium" for="password_confirmation">Confirm password
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal">
        </label>
        <button class="w-full rounded-lg bg-emerald-700 px-4 py-2.5 font-medium text-white hover:bg-emerald-800" type="submit">Create account</button>
    </form>
    <p class="mt-6 text-center text-sm text-muted-foreground">Already have an account?
        <a class="font-medium text-foreground underline underline-offset-4" href="{{ route('login', $teamInvitation ? ['invitation' => $teamInvitation['code']] : []) }}">Log in</a>
    </p>
@endsection
