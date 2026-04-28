@extends('layouts.app')
@section('title', 'Мої сертифікати')

@section('content')
<h1>Мої сертифікати</h1>

@if($certificates->isEmpty())
    <p>У вас ще немає сертифікатів. Завершіть курс, щоб отримати сертифікат.</p>
@else
    @foreach($certificates as $cert)
    <div class="card-panel">
        <h3>{{ $cert->course->title ?? '—' }}</h3>
        <p>Тип:
            @switch($cert->type)
                @case('bw') Чорно-білий (Прослухав) @break
                @case('color') Кольоровий (Старався) — знижка 10% @break
                @case('guaranteed') З гарантією (Відмінний результат) — знижка 20% @break
            @endswitch
        </p>
        <p>Успішність: {{ $cert->success_rate }}%</p>
        <p>Номер: {{ $cert->certificate_number }}</p>
        @if($cert->discount_next_course > 0)
            <p>Знижка на наступний курс: {{ $cert->discount_next_course }}%
                {{ $cert->discount_used ? '(використана)' : '' }}
            </p>
        @endif
        <a href="{{ route('certificates.show', $cert) }}">Переглянути</a>
    </div>
    @endforeach
@endif
@endsection
