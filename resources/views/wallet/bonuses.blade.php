@extends('layouts.app')
@section('title', 'Бонуси')

@section('content')
<a href="{{ route('wallet.index') }}">&larr; Гаманець</a>

<h1>Бонуси</h1>

{{-- Purchase bonuses --}}
<h2>Придбати бонуси</h2>
<form method="POST" action="{{ route('bonuses.purchase') }}">
    @csrf
    <div>
        <label>Тип бонусу</label>
        <select name="type" id="bonus-type">
            <option value="test_hint">Підказка на тесті (-15 монет)</option>
            <option value="homework_freeze">Заморозка дедлайну ДЗ (-15 монет/день)</option>
            <option value="graduation_freeze">Заморозка дедлайну випускного (-50 монет/день)</option>
        </select>
    </div>
    <div>
        <label>Курс</label>
        <select name="course_id">
            @foreach(auth()->user()->activeEnrollments as $course)
                <option value="{{ $course->id }}">{{ $course->title }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Кількість</label>
        <input type="number" name="quantity" value="1" min="1" max="20">
    </div>
    <button type="submit">Придбати</button>
</form>

<hr>

{{-- Inventory --}}
<h2>Інвентар</h2>

@if($inventory->isEmpty())
    <p>У вас ще немає бонусів.</p>
@else
    @foreach($inventory as $courseId => $items)
        @php $course = $items->first()->course; @endphp
        <h3>{{ $course?->title ?? 'Курс #' . $courseId }}</h3>
        <table>
            <thead>
                <tr>
                    <th>Тип</th>
                    <th>Залишок</th>
                    <th>Використано</th>
                    <th>Дія</th>
                </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td>
                        @switch($item->type)
                            @case('test_hint') Підказка на тесті @break
                            @case('homework_freeze') Заморозка ДЗ @break
                            @case('graduation_freeze') Заморозка випускного @break
                        @endswitch
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->used_count }}</td>
                    <td>
                        @if($item->quantity > 0)
                        <form method="POST" action="{{ route('bonuses.sell', $item) }}" style="display:inline;"
                              onsubmit="return confirm('Продати за -10%?')">
                            @csrf
                            <button type="submit">Продати (-10%)</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endforeach
@endif

<p><em>VIP-знижка на бонуси: 10%</em></p>
@endsection
