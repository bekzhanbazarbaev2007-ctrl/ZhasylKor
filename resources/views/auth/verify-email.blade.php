@extends('layouts.auth')

@section('title', 'Email verification')
@section('heading', 'Verify your email')
@section('description', 'Please verify your email address by clicking the link we just emailed to you.')

@section('content')
    <form method="POST" action="{{ route('verification.send') }}" class="space-y-5 text-center">
        @csrf
        <button class="w-full rounded-lg border border-border px-4 py-2.5 font-medium hover:bg-muted" type="submit">Resend verification email</button>
    </form>
    <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
        @csrf
        <button class="text-sm underline underline-offset-4" type="submit">Log out</button>
    </form>
@endsection
