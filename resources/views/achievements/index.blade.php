@extends('layouts.app')
@section('title', 'Досягнення')

@section('content')
<h1>Досягнення</h1>

{{-- Monthly leaderboard --}}
<h2>Топ місяця — {{ now()->translatedFormat('F Y') }}</h2>
@if($leaderboard->count())
    <table>
        <thead>
            <tr><th>#</th><th>Студент</th><th>Бали</th><th>Монети</th></tr>
        </thead>
        <tbody>
        @foreach($leaderboard as $entry)
            <tr>
                <td>{{ $entry->rank }}</td>
                <td>{{ $entry->user->last_name ?? '' }} {{ $entry->user->first_name ?? '' }}</td>
                <td>{{ $entry->total_score }}</td>
                <td>
                    @if($entry->rank === 1) +50
                    @elseif($entry->rank === 2) +30
                    @elseif($entry->rank === 3) +20
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p>Дані за поточний місяць ще не розраховані.</p>
@endif

<hr>

{{-- All achievements --}}
<h2>Всі досягнення</h2>

@foreach($allAchievements as $achievement)
<div class="card {{ in_array($achievement->id, $earned) ? 'card--earned' : '' }}">
    <h3>{{ $achievement->title }} {{ in_array($achievement->id, $earned) ? '✅' : '🔒' }}</h3>
    <p>{{ $achievement->description }}</p>
    <p>Нагорода: +{{ $achievement->reward_coins }} монет</p>
</div>
@endforeach
@endsection
