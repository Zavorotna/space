@extends('layouts.app')
@section('title', 'Студенти: ' . $course->title)

@section('content')
<a href="{{ route('teacher.courses.edit', $course) }}">&larr; Назад до курсу</a>

<h1>Студенти курсу: {{ $course->title }}</h1>

@if($students->isEmpty())
    <p>Немає студентів на курсі.</p>
@else
<table>
    <thead>
        <tr>
            <th>Ім'я</th>
            <th>Прізвище</th>
            <th>Телефон</th>
            <th>Оплата</th>
            <th>Успішність</th>
            <th>Баланс</th>
            <th>Дата завершення</th>
            <th>Дії</th>
        </tr>
    </thead>
    <tbody>
    @foreach($students as $student)
        <tr>
            <td>{{ $student->first_name }}</td>
            <td>{{ $student->last_name }}</td>
            <td>{{ $student->phone }}</td>
            <td>{{ $student->pivot->is_paid ? '✅' : '❌' }}</td>
            <td>{{ $student->pivot->success_rate ?? 0 }}%</td>
            <td>{{ $student->wallet?->balance ?? 0 }}</td>
            <td>{{ $student->active_until ? \Carbon\Carbon::parse($student->active_until)->format('d.m.Y') : '—' }}</td>
            <td>
                <a href="{{ route('profile.show', $student) }}">Профіль</a>
                {{-- Issue certificate --}}
                <form method="POST" action="{{ route('teacher.certificates.issue', [$course, $student->id]) }}" style="display:inline;" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="certificate_image" accept="image/*">
                    <button type="submit">Видати сертифікат</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif

<hr>

{{-- Change end date --}}
<h2>Змінити дату завершення курсу</h2>
<p>Поточна: {{ $course->end_date ? \Carbon\Carbon::parse($course->end_date)->format('d.m.Y') : 'не встановлена' }}</p>
<form method="POST" action="{{ route('teacher.courses.endDate', $course) }}">
    @csrf
    <input type="date" name="end_date" value="{{ $course->end_date?->format('Y-m-d') ?? '' }}" required>
    <button type="submit">Змінити</button>
</form>

{{-- Course LiqPay settings (superadmin only) --}}
@if(auth()->user()->role === 'superadmin')
<hr>
<h2>LiqPay налаштування (ФОП)</h2>
<form method="POST" action="{{ route('superadmin.courses.liqpay', $course) }}">
    @csrf
    <div>
        <label>Merchant ID</label>
        <input type="text" name="liqpay_merchant_id" value="{{ $course->liqpay_merchant_id }}">
    </div>
    <div>
        <label>Private Key</label>
        <input type="text" name="liqpay_private_key" value="{{ $course->liqpay_private_key }}">
    </div>
    <button type="submit">Зберегти</button>
</form>
@endif
@endsection
