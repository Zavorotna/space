@extends('layouts.app')
@section('title', 'Статистика занять')

@section('content')
<h1>Статистика занять</h1>

{{-- Month selector --}}
<form method="GET" action="{{ route('superadmin.lesson.stats') }}" style="display:flex; gap:8px; margin-bottom:20px;">
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
<div style="border:2px solid #e74c3c; padding:12px; margin-bottom:20px; border-radius:6px;">
    <strong style="color:#e74c3c;">⚠️ Без звіту: {{ $unreported->count() }} занять</strong>
    <ul style="margin:8px 0 0;">
        @foreach($unreported as $l)
        <li>{{ $l->date->format('d.m.Y') }} · {{ $l->teacher->full_name ?? '—' }} · {{ $l->course->title }}</li>
        @endforeach
    </ul>
</div>
@endif

@forelse($byTeacher as $teacherId => $data)
<div style="border:1px solid #ddd; padding:15px; margin-bottom:20px; border-radius:6px;">
    <h2 style="margin-top:0;">{{ $data['teacher']->full_name ?? '—' }}</h2>

    <table style="border-collapse:collapse; margin-bottom:10px;">
        <tr>
            <td style="padding:4px 12px 4px 0;"><strong>Всього занять:</strong></td>
            <td>{{ $data['total'] }}</td>
        </tr>
        <tr>
            <td style="padding:4px 12px 4px 0;">Повні:</td>
            <td>{{ $data['full'] }}</td>
        </tr>
        <tr>
            <td style="padding:4px 12px 4px 0;">Часткові (інд.):</td>
            <td>{{ $data['partial'] }}</td>
        </tr>
        <tr>
            <td style="padding:4px 12px 4px 0;">Скасовані:</td>
            <td>{{ $data['cancelled'] }}</td>
        </tr>
        <tr>
            <td style="padding:4px 12px 4px 0;">Перенесені (груп.):</td>
            <td>{{ $data['rescheduled'] }}</td>
        </tr>
        @if($data['individual_minutes_planned'] > 0)
        <tr>
            <td style="padding:4px 12px 4px 0;"><strong>Інд. годин (план / факт):</strong></td>
            <td><strong>{{ round($data['individual_minutes_planned'] / 60, 1) }} / {{ round($data['individual_minutes_actual'] / 60, 1) }} год</strong></td>
        </tr>
        @endif
    </table>

    <details>
        <summary style="cursor:pointer; color:#4a90d9;">Деталі занять</summary>
        <table style="width:100%; border-collapse:collapse; margin-top:8px; font-size:0.9em;">
            <thead>
                <tr style="background:#f5f5f5;">
                    <th style="padding:5px 8px; text-align:left; border:1px solid #ddd;">Дата</th>
                    <th style="padding:5px 8px; text-align:left; border:1px solid #ddd;">Курс</th>
                    <th style="padding:5px 8px; text-align:left; border:1px solid #ddd;">Тип</th>
                    <th style="padding:5px 8px; text-align:left; border:1px solid #ddd;">Статус</th>
                    <th style="padding:5px 8px; text-align:left; border:1px solid #ddd;">Год план</th>
                    <th style="padding:5px 8px; text-align:left; border:1px solid #ddd;">Год факт</th>
                    <th style="padding:5px 8px; text-align:left; border:1px solid #ddd;">Примітка</th>
                </tr>
            </thead>
            <tbody>
            @foreach($data['lessons'] as $lesson)
            <tr>
                <td style="padding:5px 8px; border:1px solid #ddd;">{{ $lesson->date->format('d.m') }}</td>
                <td style="padding:5px 8px; border:1px solid #ddd;">{{ $lesson->course->title }}</td>
                <td style="padding:5px 8px; border:1px solid #ddd;">{{ $lesson->course->type === 'individual' ? 'Інд.' : 'Груп.' }}</td>
                <td style="padding:5px 8px; border:1px solid #ddd;">
                    @switch($lesson->completion_status)
                        @case('full') ✅ Повне @break
                        @case('partial') ⚡ Часткове @break
                        @case('cancelled') ❌ Скасовано @break
                        @case('rescheduled') 🔄 Перенесено @break
                    @endswitch
                </td>
                <td style="padding:5px 8px; border:1px solid #ddd;">{{ round($lesson->plannedMinutes() / 60, 1) }}</td>
                <td style="padding:5px 8px; border:1px solid #ddd;">
                    @php $actMin = $lesson->actual_minutes ?? ($lesson->completion_status === 'full' ? $lesson->plannedMinutes() : null); @endphp
                    {{ $actMin !== null ? round($actMin / 60, 1) : '—' }}
                </td>
                <td style="padding:5px 8px; border:1px solid #ddd;">{{ $lesson->completion_note ?? '—' }}</td>
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
