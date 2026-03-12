@extends('layouts.app')
@section('title', 'Результат тесту')
@section('content')
<h1>{{ $attempt->passed ? 'Вітаємо! Ви успішно склали тест' : 'Тест не складено' }}</h1>
<h2>{{ $test->title }}</h2>
<p>Ваш бал за тест: <strong>{{ $attempt->score }}%</strong></p>

<div>
    @foreach($test->questions as $i => $question)
        @php
            $answer = $attempt->answers->firstWhere('question_id', $question->id);
            $isCorrect = $answer && $answer->is_correct;
        @endphp
        <span style="display:inline-block;width:30px;height:30px;text-align:center;line-height:30px;border:1px solid {{ $isCorrect ? 'green' : 'red' }};color:{{ $isCorrect ? 'green' : 'red' }}">
            {{ $i + 1 }}
        </span>
    @endforeach
</div>

@if($attempt->passed)
    <p>Нараховано: {{ $attempt->coins_awarded }} монет</p>
@else
    <p>Ви можете пройти тестування ще раз для підвищення балу.</p>
    <form method="POST" action="{{ route('tests.start', $test) }}">
        @csrf
        <button type="submit">Перездати (-10 монет)</button>
    </form>
@endif
@endsection
