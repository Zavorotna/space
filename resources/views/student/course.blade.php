@extends('layouts.app')
@section('title', $course->title)
@section('content')
<h1>{{ $course->title }}</h1>
<p>{{ $course->description }}</p>
<p>Ціна: {{ $course->price }} грн. в місяць</p>
<a href="#">Детальніше</a>

<h2>Успішність та прогрес курсу</h2>
<div>
    <strong>{{ $enrollment->pivot->success_rate }}%</strong> успішність
    <progress value="{{ $enrollment->pivot->success_rate }}" max="100"></progress>
</div>

@if($showTelegram && $course->telegram_link)
    <p>Telegram група: <a href="{{ $course->telegram_link }}" target="_blank">Приєднатися</a></p>
@endif

<h2>Домашне завдання</h2>
@foreach($course->homeworkAssignments as $hw)
<div>
    <strong>{{ $hw->title }}</strong>
    <span>({{ ['easy'=>'легка','medium'=>'середня','hard'=>'важка'][$hw->difficulty] ?? $hw->difficulty }})</span>
    <span>Термін здачі: {{ $hw->deadline->format('d.m') }}</span>
    @php $sub = $homeworkSubmissions->get($hw->id); @endphp
    @if($sub && $sub->status === 'accepted')
        <span>✅ Прийнято</span>
    @elseif($sub && $sub->status === 'revision')
        <span>🔄 На доопрацювання</span>
        <a href="{{ route('homework.submitForm', $hw) }}">Здати</a>
    @else
        <a href="{{ route('homework.submitForm', $hw) }}">Здати</a>
    @endif
</div>
@endforeach

<h2>Тести</h2>
@foreach($course->tests as $test)
<div>
    <strong>{{ $test->title }}</strong>
    @php $attempts = $testAttempts->get($test->id); @endphp
    @if($attempts && $attempts->where('passed', true)->count() > 0)
        <span>✅ Складено ({{ $attempts->where('passed', true)->first()->score }}%)</span>
    @else
        <a href="{{ route('tests.show', $test) }}">Пройти</a>
    @endif
</div>
@endforeach

@if($course->additionalMaterials->count())
<h2>Додаткові матеріали</h2>
@foreach($course->additionalMaterials as $material)
<div>
    {{ $material->title }}
    @if($material->price_coins > 0)
        <span>{{ $material->price_coins }} монет</span>
        @if(!$material->purchases()->where('user_id', auth()->id())->exists())
            <form method="POST" action="{{ route('materials.purchase', $material) }}" style="display:inline">
                @csrf
                <button type="submit">Придбати</button>
            </form>
        @else
            <a href="{{ $material->url }}" target="_blank">Відкрити</a>
        @endif
    @else
        <a href="{{ $material->url }}" target="_blank">Відкрити</a>
    @endif
</div>
@endforeach
@endif

@if(!$enrollment->pivot->review_submitted)
<h2>Залишити відгук</h2>
<form method="POST" action="{{ route('courses.review', $course) }}">
    @csrf
    <select name="rating" required>
        <option value="5">5 - Відмінно</option>
        <option value="4">4 - Добре</option>
        <option value="3">3 - Нормально</option>
        <option value="2">2 - Погано</option>
        <option value="1">1 - Жахливо</option>
    </select>
    <textarea name="text" placeholder="Ваш відгук..."></textarea>
    <button type="submit">Надіслати (+100 монет)</button>
</form>
@endif
@endsection
