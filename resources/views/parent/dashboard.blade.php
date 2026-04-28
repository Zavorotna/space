@extends('layouts.app')
@section('title', 'Батьківський кабінет')

@section('content')
<h1>Батьківський кабінет</h1>

@if(empty($childrenData))
    <p>У вас ще немає прив'язаних дітей. Зверніться до адміністрації для зв'язування акаунтів.</p>
@else
    @foreach($childrenData as $data)
    @php $child = $data['child']; @endphp
    <div class="parent-child-block">
        <h2>{{ $child->last_name }} {{ $child->first_name }}</h2>

        {{-- Active courses --}}
        <h3>Активні курси</h3>
        @if($data['courses']->count())
            @foreach($data['courses'] as $course)
                <div class="card">
                    <p><strong>{{ $course->title }}</strong></p>
                    <p>Викладач: {{ $course->teacher->last_name ?? '' }} {{ $course->teacher->first_name ?? '' }}</p>
                    <p>Успішність: {{ $course->pivot->success_rate ?? 0 }}%</p>
                    <p>Оплата: {{ $course->pivot->is_paid ? '✅ Оплачено' : '❌ Не оплачено' }}</p>
                </div>
            @endforeach
        @else
            <p>Немає активних курсів.</p>
        @endif

        {{-- Attendance --}}
        <h3>Відвідуваність (останні 10)</h3>
        @if($data['recentAttendances']->count())
            <table>
                <thead><tr><th>Дата</th><th>Курс</th><th>Статус</th></tr></thead>
                <tbody>
                @foreach($data['recentAttendances'] as $att)
                    <tr>
                        <td>{{ $att->lesson?->date?->format('d.m.Y') ?? '—' }} {{ $att->lesson?->start_time ?? '' }}</td>
                        <td>{{ $att->lesson?->course?->title ?? '—' }}</td>
                        <td>{{ $att->status === 'present' ? '✅ Присутній' : '❌ Відсутній' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p>Немає даних про відвідуваність.</p>
        @endif

        {{-- Notes/Remarks from teachers --}}
        <h3>Замітки від викладачів (останні 10)</h3>
        @if($data['notes']->count())
            @foreach($data['notes'] as $note)
                <div class="card">
                    <p><strong>{{ $note->author->last_name ?? '' }} {{ $note->author->first_name ?? '' }}</strong>
                        — {{ $note->created_at->format('d.m.Y H:i') }}</p>
                    <p>{{ $note->content }}</p>
                </div>
            @endforeach
        @else
            <p>Немає заміток.</p>
        @endif
    </div>
    @endforeach
@endif
@endsection
