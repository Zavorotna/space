@extends('layouts.app')
@section('title', 'Випускний проєкт: ' . $project->title)

@section('content')
<a href="{{ route('teacher.courses.edit', $project->course_id) }}">&larr; Назад до курсу</a>

<h1>Випускний проєкт: {{ $project->title }}</h1>
<p>Дедлайн: {{ \Carbon\Carbon::parse($project->deadline)->format('d.m.Y') }}</p>
@if($project->description)
    <p>{{ $project->description }}</p>
@endif

@if($submissions->isEmpty())
    <p>Ще немає здач.</p>
@else
    @foreach($submissions as $sub)
    <div class="card-panel">
        <h3>{{ $sub->user->last_name }} {{ $sub->user->first_name }}</h3>
        <p>Статус:
            @switch($sub->status)
                @case('submitted') 🟡 На перевірці @break
                @case('accepted') ✅ Захищено @break
                @case('revision') ⚠️ На доопрацювання @break
                @case('commission') 🔶 На комісію @break
            @endswitch
        </p>
        <p>Здано: {{ $sub->submitted_at ? $sub->submitted_at->format('d.m.Y H:i') : '—' }}</p>
        <p>Доопрацювань: {{ $sub->revision_count }} | Нагорода: {{ $sub->calculateReward() }} монет</p>

        {{-- Files --}}
        @if($sub->getMedia('files')->count())
            <h4>Файли:</h4>
            @foreach($sub->getMedia('files') as $media)
                <a href="{{ $media->getUrl() }}" target="_blank">{{ $media->file_name }}</a><br>
            @endforeach
        @endif

        {{-- Links --}}
        @if($sub->links && count($sub->links))
            <h4>Посилання:</h4>
            @foreach($sub->links as $link)
                <a href="{{ $link }}" target="_blank">{{ $link }}</a><br>
            @endforeach
        @endif

        {{-- Review form --}}
        @if($sub->status === 'submitted' || $sub->status === 'commission')
        <form method="POST" action="{{ route('teacher.graduation.review', $sub) }}">
            @csrf
            <div>
                <label>Коментар</label>
                <textarea name="teacher_comment" rows="2">{{ $sub->teacher_comment }}</textarea>
            </div>
            <div>
                <button type="submit" name="status" value="accepted">✅ Захищено</button>
                <button type="submit" name="status" value="revision">⚠️ На доопрацювання (-5 монет)</button>
                <button type="submit" name="status" value="commission">🔶 На комісію</button>
            </div>
        </form>
        @elseif($sub->teacher_comment)
            <p><strong>Коментар:</strong> {{ $sub->teacher_comment }}</p>
        @endif
    </div>
    @endforeach
@endif
@endsection
