@extends('layouts.app')
@section('title', 'Сертифікат: ' . $certificate->course->title)

@section('content')
<a href="{{ route('certificates.index') }}">&larr; Всі сертифікати</a>

<h1>Сертифікат</h1>

<div>
    <p><strong>Курс:</strong> {{ $certificate->course->title }}</p>
    <p><strong>Студент:</strong> {{ $certificate->user->last_name }} {{ $certificate->user->first_name }}</p>
    <p><strong>Тип:</strong>
        @switch($certificate->type)
            @case('bw') Чорно-білий (Прослухав) @break
            @case('color') Кольоровий (Старався) @break
            @case('guaranteed') З гарантією (Відмінний результат) @break
        @endswitch
    </p>
    <p><strong>Успішність:</strong> {{ $certificate->success_rate }}%</p>
    <p><strong>Номер:</strong> {{ $certificate->certificate_number }}</p>
    <p><strong>Дата видачі:</strong> {{ $certificate->created_at->format('d.m.Y') }}</p>

    @if($certificate->discount_next_course > 0)
        <p><strong>Знижка на наступний курс:</strong> {{ $certificate->discount_next_course }}%</p>
    @endif
</div>

{{-- Certificate image --}}
@if($certificate->getFirstMedia('certificate_image'))
    <div>
        <h3>Зображення сертифіката</h3>
        <img src="{{ $certificate->getFirstMediaUrl('certificate_image') }}" alt="Сертифікат" style="max-width:100%;">
    </div>
@endif
@endsection
