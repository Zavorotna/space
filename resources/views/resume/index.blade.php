@extends('layouts.app')
@section('title', 'Резюме студентів')

@section('content')
<h1>Резюме студентів</h1>

@if($resumes->isEmpty())
    <p>Наразі немає опублікованих резюме.</p>
@else
    @foreach($resumes as $resume)
    <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
        <div>
            @if($resume->user->getFirstMediaUrl('avatar'))
                <img src="{{ $resume->user->getFirstMediaUrl('avatar') }}" alt="Аватар" style="width:60px; height:60px; border-radius:50%;">
            @endif
            <h3>{{ $resume->user->last_name }} {{ $resume->user->first_name }}
                @if($resume->user->isVip()) ⭐ @endif
            </h3>
        </div>

        {{-- Certificates --}}
        @if($resume->user->certificates->count())
            <p>Курси:
            @foreach($resume->user->certificates as $cert)
                <span>{{ $cert->course->title ?? '—' }} ({{ $cert->success_rate }}%)</span>@if(!$loop->last), @endif
            @endforeach
            </p>
        @endif

        @if($resume->about)
            <p>{{ Str::limit($resume->about, 150) }}</p>
        @endif

        <a href="{{ route('resumes.show', $resume) }}">Детальніше</a>
    </div>
    @endforeach

    {{ $resumes->links() }}
@endif
@endsection
