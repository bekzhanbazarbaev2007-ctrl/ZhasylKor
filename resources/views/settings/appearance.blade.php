@extends('layouts.settings')

@section('title', 'Appearance settings')
@section('heading', 'Appearance settings')
@section('description', 'Choose the theme used across your account.')

@section('content')
    <section class="rounded-xl border border-border bg-card p-6">
        <h2 class="text-lg font-medium">Theme</h2>
        <p class="mt-1 text-sm text-muted-foreground">Your selection is saved in this browser.</p>
        <div class="mt-5 flex flex-wrap gap-3" role="group" aria-label="Choose appearance">
            @foreach (['light' => 'Light', 'dark' => 'Dark', 'system' => 'System'] as $value => $label)
                <button type="button" class="rounded-lg border border-border px-4 py-2 hover:bg-muted" data-appearance="{{ $value }}" aria-pressed="{{ $appearance === $value ? 'true' : 'false' }}">{{ $label }}</button>
            @endforeach
        </div>
    </section>
@endsection
