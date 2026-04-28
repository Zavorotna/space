@extends('layouts.app')
@section('title', 'Статистика занять')

@section('content')
<h1>Статистика занять</h1>

<form method="GET" action="{{ route('superadmin.lesson.stats') }}" class="flex-row mb-3">
    <select name="month">
        @foreach(range(1, 12) as $m)
            <option value="{{ $m }}" @selected($m == $month)>
                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
            </option>
        @endforeach
    </select>
    <select name="year">
        @foreach(range(now()->year - 1, now()->year + 1) as $y)
            <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
        @endforeach
    </select>
    <button type="submit">Показати</button>
</form>

@if($unreported->count())
<div class="unreported-section">
    <strong>⚠️ Без звіту: {{ $unreported->count() }} занять</strong>
    <ul>
        @foreach($unreported as $l)
        <li>{{ $l->date->format('d.m.Y') }} · {{ $l->teacher->full_name ?? '—' }} · {{ $l->course->title }}</li>
        @endforeach
    </ul>
</div>
@endif

@forelse($byTeacher as $teacherId => $data)
<div class="card-panel mb-2">
    <h2>{{ $data['teacher']->full_name ?? '—' }}</h2>

    <table class="stats-table mb-1">
        <tr>
            <td><strong>Всього занять:</strong></td>
            <td>{{ $data['total'] }}</td>
        </tr>
        <tr>
            <td>Повні:</td>
            <td>{{ $data['full'] }}</td>
        </tr>
        <tr>
            <td>Часткові (інд.):</td>
            <td>{{ $data['partial'] }}</td>
        </tr>
        <tr>
            <td>Скасовані:</td>
            <td>{{ $data['cancelled'] }}</td>
        </tr>
        <tr>
            <td>Перенесені (груп.):</td>
            <td>{{ $data['rescheduled'] }}</td>
        </tr>
        @if($data['individual_minutes_planned'] > 0)
        <tr>
            <td><strong>Інд. годин (план / факт):</strong></td>
            <td><strong>{{ round($data['individual_minutes_planned'] / 60, 1) }} / {{ round($data['individual_minutes_actual'] / 60, 1) }} год</strong></td>
        </tr>
        @endif
    </table>

    <details>
        <summary class="text-blue">Деталі занять</summary>
        <table class="data-table data-table--bordered text-sm mt-1">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Курс</th>
                    <th>Тип</th>
                    <th>Статус</th>
                    <th>Год план</th>
                    <th>Год факт</th>
                    <th>Примітка</th>
                </tr>
            </thead>
            <tbody>
            @foreach($data['lessons'] as $lesson)
            <tr>
                <td>{{ $lesson->date->format('d.m') }}</td>
                <td>{{ $lesson->course->title }}</td>
                <td>{{ $lesson->course->type === 'individual' ? 'Інд.' : 'Груп.' }}</td>
                <td>
                    @switch($lesson->completion_status)
                        @case('full') ✅ Повне @break
                        @case('partial') ⚡ Часткове @break
                        @case('cancelled') ❌ Скасовано @break
                        @case('rescheduled') 🔄 Перенесено @break
                    @endswitch
                </td>
                <td>{{ round($lesson->plannedMinutes() / 60, 1) }}</td>
                <td>
                    @php $actMin = $lesson->actual_minutes ?? ($lesson->completion_status === 'full' ? $lesson->plannedMinutes() : null); @endphp
                    {{ $actMin !== null ? round($actMin / 60, 1) : '—' }}
                </td>
                <td>{{ $lesson->completion_note ?? '—' }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </details>
</div>
@empty
<p>Немає даних за обраний місяць.</p>
@endforelse
@endsection