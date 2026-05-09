@extends('layouts.app')
@section('title', 'Підтвердження акаунту')
@section('content')
<h1>Вхід через Google</h1>

<p class="text-muted mb-2">
    Google акаунт <strong>{{ session('google_pending.email') }}</strong> не знайдено в системі.
</p>

@if(session('error'))
<div class="alert-box alert-box--error mb-2">{{ session('error') }}</div>
@endif

<div class="card-panel mb-2">
    <h2>У вас вже є акаунт на Hashtag Space?</h2>
    <p class="text-sm text-muted mb-2">Введіть номер телефону, щоб прив'язати Google до існуючого акаунту.</p>
    <form method="POST" action="{{ route('auth.google.claim.process') }}">
        @csrf
        <div class="form-group">
            <label>Номер телефону</label>
            <input type="text" name="phone" placeholder="+380501234567" required autofocus>
            @error('phone') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="btn btn-primary">Прив'язати Google до мого акаунту</button>
    </form>
</div>

<div class="card-panel">
    <h2>Ще не маєте акаунту?</h2>
    <p class="text-sm text-muted mb-2">Буде створено новий акаунт з даними Google.</p>
    <form method="POST" action="{{ route('auth.google.claim.process') }}">
        @csrf
        <input type="hidden" name="create_new" value="1">
        <button type="submit" class="btn btn-ghost">Створити новий акаунт</button>
    </form>
</div>
@endsection