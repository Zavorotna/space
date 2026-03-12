@extends('layouts.app')
@section('title', 'Перевірка ДЗ: ' . $homework->title)

@section('content')
<a href="{{ route('teacher.courses.edit', $homework->course_id) }}">&larr; Назад до курсу</a>

<h1>Здачі: {{ $homework->title }}</h1>
<p>Складність: {{ ['easy' => 'Легка', 'medium' => 'Середня', 'hard' => 'Важка'][$homework->difficulty] }} |
   Дедлайн: {{ \Carbon\Carbon::parse($homework->deadline)->format('d.m.Y') }}</p>

@if($submissions->isEmpty())
    <p>Ще немає здач.</p>
@else
    @foreach($submissions as $sub)
    <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
        <h3>{{ $sub->user->last_name }} {{ $sub->user->first_name }}</h3>
        <p>Статус:
            @switch($sub->status)
                @case('submitted') 🟡 На перевірці @break
                @case('accepted') ✅ Прийнято @break
                @case('revision') ⚠️ На доопрацювання @break
            @endswitch
        </p>
        <p>Здано: {{ $sub->submitted_at ? $sub->submitted_at->format('d.m.Y H:i') : '—' }}</p>
        <p>Доопрацювань: {{ $sub->revision_count }}</p>
        @if($sub->early_submission) <p>🎯 Рання здача (+10 монет)</p> @endif

        {{-- Files --}}
        @if($sub->getMedia('files')->count())
            <h4>Файли:</h4>
            @foreach($sub->getMedia('files') as $media)
                <div style="display:inline-block; margin:5px;">
                    <a href="{{ $media->getUrl() }}" target="_blank">
                        <img src="{{ $media->getUrl() }}" alt="{{ $media->file_name }}" style="max-width:150px; max-height:150px;">
                    </a>
                </div>
            @endforeach
        @endif

        {{-- Links --}}
        @if($sub->links && count($sub->links))
            <h4>Посилання:</h4>
            <ul>
            @foreach($sub->links as $link)
                <li><a href="{{ $link }}" target="_blank">{{ $link }}</a></li>
            @endforeach
            </ul>
        @endif

        {{-- Review form --}}
        @if($sub->status === 'submitted')
        <form method="POST" action="{{ route('teacher.homework.review', $sub) }}">
            @csrf
            <div>
                <label>Коментар</label>
                <textarea name="teacher_comment" rows="2">{{ $sub->teacher_comment }}</textarea>
            </div>
            <div>
                <button type="submit" name="status" value="accepted">✅ Прийняти</button>
                <button type="submit" name="status" value="revision">⚠️ На доопрацювання (-1 монета)</button>
            </div>
        </form>
        @elseif($sub->teacher_comment)
            <p><strong>Коментар:</strong> {{ $sub->teacher_comment }}</p>
        @endif
    </div>
    @endforeach
@endif
@endsection
