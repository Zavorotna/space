@extends('layouts.app')
@section('title', 'Резюме: ' . $resume->user->last_name . ' ' . $resume->user->first_name)

@section('content')
<a href="{{ route('resumes.index') }}">&larr; Всі резюме</a>

<div>
    @if($resume->user->getFirstMediaUrl('avatar'))
        <img src="{{ $resume->user->getFirstMediaUrl('avatar') }}" alt="Аватар" style="width:100px; height:100px; border-radius:50%;">
    @endif

    {{-- VIP slider --}}
    @if($resume->user->isVip() && $resume->user->getMedia('extra_avatars')->count())
        <div id="avatar-slider">
            @foreach($resume->user->getMedia('extra_avatars') as $i => $avatar)
                <img src="{{ $avatar->getUrl() }}" alt="Аватар {{ $i+1 }}"
                     style="width:80px; height:80px; border-radius:50%; display:{{ $i === 0 ? 'inline-block' : 'none' }};"
                     class="extra-avatar">
            @endforeach
            @if($resume->user->getMedia('extra_avatars')->count() > 1)
                <button onclick="slideAvatar()">→</button>
            @endif
        </div>
    @endif

    <h1>{{ $resume->user->last_name }} {{ $resume->user->first_name }}
        @if($resume->user->isVip()) ⭐ VIP @endif
    </h1>
</div>

@if($resume->about)
    <h2>Про себе</h2>
    <p>{!! nl2br(e($resume->about)) !!}</p>
@endif

{{-- Courses & Certificates --}}
<h2>Завершені курси</h2>
@php $visibleCerts = $resume->user->certificates->filter(fn($c) => !in_array($c->course_id, $resume->hidden_courses ?? [])); @endphp
@if($visibleCerts->count())
    <table>
        <thead><tr><th>Курс</th><th>Успішність</th><th>Сертифікат</th></tr></thead>
        <tbody>
        @foreach($visibleCerts as $cert)
            <tr>
                <td>{{ $cert->course->title ?? '—' }}</td>
                <td>{{ $cert->success_rate }}%</td>
                <td>
                    @switch($cert->type)
                        @case('bw') Чорно-білий @break
                        @case('color') Кольоровий @break
                        @case('guaranteed') З гарантією @break
                    @endswitch
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p>Немає завершених курсів.</p>
@endif

@if($resume->work_experience)
    <h2>Досвід роботи</h2>
    <p>{!! nl2br(e($resume->work_experience)) !!}</p>
@endif

@if($resume->project_links && count($resume->project_links))
    <h2>Проєкти</h2>
    <ul>
    @foreach($resume->project_links as $link)
        <li><a href="{{ $link }}" target="_blank">{{ $link }}</a></li>
    @endforeach
    </ul>
@endif

<h2>Контакти</h2>
@if($resume->contact_email) <p>Email: {{ $resume->contact_email }}</p> @endif
@if($resume->contact_phone) <p>Телефон: {{ $resume->contact_phone }}</p> @endif

<script>
let currentAvatar = 0;
function slideAvatar() {
    const avatars = document.querySelectorAll('.extra-avatar');
    if (!avatars.length) return;
    avatars[currentAvatar].style.display = 'none';
    currentAvatar = (currentAvatar + 1) % avatars.length;
    avatars[currentAvatar].style.display = 'inline-block';
}
</script>
@endsection
