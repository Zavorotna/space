@extends('layouts.app')
@section('title', 'Редагування тесту: ' . $test->title)

@section('content')
<a href="{{ route('teacher.courses.edit', $test->course_id) }}">&larr; Назад до курсу</a>

<h1>Редагування тесту</h1>

<form method="POST" action="{{ route('tests.update', $test) }}">
    @csrf @method('PUT')
    <div>
        <label>Назва тесту</label>
        <input type="text" name="title" value="{{ old('title', $test->title) }}" required>
    </div>
    <div>
        <label>Опис</label>
        <textarea name="description">{{ old('description', $test->description) }}</textarea>
    </div>
    <div>
        <label>Прохідний бал (%)</label>
        <input type="number" name="passing_score" value="{{ old('passing_score', $test->passing_score) }}" min="1" max="100" required>
    </div>
    <button type="submit">Зберегти</button>
</form>

@if(auth()->user()->isAdmin())
<form method="POST" action="{{ route('tests.destroy', $test) }}" id="delete-test-form" class="mt-2">
    @csrf @method('DELETE')
    <button type="button" onclick="showTestDeleteConfirm()">Видалити тест</button>
</form>
<div id="test-delete-confirm" class="confirm-delete" style="display:none;">
    <p><strong>Видалити тест «{{ $test->title }}»?</strong></p>
    <p class="text-sm text-muted">Буде видалено {{ $test->questions->count() }} питань та всі результати студентів. Введіть назву тесту:</p>
    <input type="text" id="delete-test-input" placeholder="{{ $test->title }}">
    <div class="confirm-delete__row">
        <button type="button" id="confirm-delete-test-btn" disabled
                onclick="document.getElementById('delete-test-form').submit()">Так, видалити</button>
        <button type="button" onclick="hideTestDeleteConfirm()">Скасувати</button>
    </div>
</div>
<script>
function showTestDeleteConfirm() { document.getElementById('test-delete-confirm').style.display = 'block'; }
function hideTestDeleteConfirm() {
    document.getElementById('test-delete-confirm').style.display = 'none';
    document.getElementById('delete-test-input').value = '';
    document.getElementById('confirm-delete-test-btn').disabled = true;
}
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('delete-test-input').addEventListener('input', function () {
        document.getElementById('confirm-delete-test-btn').disabled = this.value !== '{{ addslashes($test->title) }}';
    });
});
</script>

@elseif(auth()->user()->isTeacher())
@php
    $hasPendingTestDeletion = \App\Models\DeletionRequest::where('deletable_type', \App\Models\Test::class)
        ->where('deletable_id', $test->id)->pending()->exists();
@endphp
@if($hasPendingTestDeletion)
<div class="dr-pending">
    <strong>Запит на видалення надіслано</strong>
    <p class="text-sm text-muted">Очікується рішення адміністратора.</p>
</div>
@else
<button type="button" onclick="document.getElementById('del-test-request-form').style.display='block';this.style.display='none'"
        class="btn btn-danger mt-2">
    Видалити тест
</button>
<div id="del-test-request-form" class="dr-box" style="display:none;">
    <p class="dr-box__title">Запит на видалення тесту</p>
    <form method="POST" action="{{ route('deletion.store') }}">
        @csrf
        <input type="hidden" name="deletable_type" value="App\Models\Test">
        <input type="hidden" name="deletable_id" value="{{ $test->id }}">
        <textarea name="reason" rows="3" placeholder="Причина видалення (необов'язково)..."></textarea>
        <div class="flex-row mt-1">
            <button type="submit" class="btn btn-sm btn-danger">Надіслати запит</button>
            <button type="button" onclick="document.getElementById('del-test-request-form').style.display='none'"
                    class="btn btn-sm btn-ghost">
                Скасувати
            </button>
        </div>
    </form>
</div>
@if(session('deletion_requested'))
<p class="text-success mt-1">{{ session('deletion_requested') }}</p>
@endif
@endif
@endif

<hr>

<h2>Питання ({{ $test->questions->count() }})</h2>

@foreach($test->questions as $index => $question)
<div class="question-card">
    <form method="POST" action="{{ route('tests.updateQuestion', $question) }}">
        @csrf @method('PUT')
        <strong>Питання {{ $index + 1 }}</strong>
        <div>
            <label>Текст питання</label>
            <textarea name="text" required>{{ $question->text }}</textarea>
        </div>
        <div>
            <label>Тип</label>
            <select name="type">
                <option value="single" @selected($question->type === 'single')>Одна правильна</option>
                <option value="multiple" @selected($question->type === 'multiple')>Декілька правильних</option>
            </select>
        </div>
        <div>
            <label>Підказка (необов'язково)</label>
            <textarea name="hint">{{ $question->hint }}</textarea>
        </div>

        <h4>Варіанти відповідей</h4>
        <div id="options-{{ $question->id }}">
            @foreach($question->options as $oi => $option)
            <div>
                <input type="hidden" name="options[{{ $oi }}][id]" value="{{ $option->id }}">
                <input type="text" name="options[{{ $oi }}][text]" value="{{ $option->text }}" required>
                <label>
                    <input type="checkbox" name="options[{{ $oi }}][is_correct]" value="1"
                        @checked($option->is_correct)>
                    Правильна
                </label>
                <input type="hidden" name="options[{{ $oi }}][is_correct]" value="0" class="fallback-correct">
            </div>
            @endforeach
        </div>

        <button type="submit">Оновити питання</button>
    </form>

    <form method="POST" action="{{ route('tests.deleteQuestion', $question) }}" class="form-inline"
          onsubmit="return confirm('Видалити це питання?')">
        @csrf @method('DELETE')
        <button type="submit">Видалити питання</button>
    </form>
</div>
@endforeach

<hr>

<h2>Додати нове питання</h2>
<form method="POST" action="{{ route('tests.addQuestion', $test) }}" id="new-question-form">
    @csrf
    <div>
        <label>Текст питання</label>
        <textarea name="text" required></textarea>
    </div>
    <div>
        <label>Тип</label>
        <select name="type">
            <option value="single">Одна правильна</option>
            <option value="multiple">Декілька правильних</option>
        </select>
    </div>
    <div>
        <label>Підказка</label>
        <textarea name="hint"></textarea>
    </div>

    <h4>Варіанти відповідей</h4>
    <div id="new-options">
        <div>
            <input type="text" name="options[0][text]" placeholder="Варіант 1" required>
            <label><input type="checkbox" name="options[0][is_correct]" value="1"> Правильна</label>
            <input type="hidden" name="options[0][is_correct]" value="0" class="fallback-correct">
        </div>
        <div>
            <input type="text" name="options[1][text]" placeholder="Варіант 2" required>
            <label><input type="checkbox" name="options[1][is_correct]" value="1"> Правильна</label>
            <input type="hidden" name="options[1][is_correct]" value="0" class="fallback-correct">
        </div>
    </div>
    <button type="button" onclick="addOption()">+ Додати варіант</button>
    <br><br>
    <button type="submit">Додати питання</button>
</form>

<script>
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        this.querySelectorAll('.fallback-correct').forEach(hidden => {
            const checkbox = hidden.previousElementSibling?.querySelector('input[type="checkbox"]')
                || hidden.parentElement.querySelector('input[type="checkbox"]');
            if (checkbox && checkbox.checked) {
                hidden.disabled = true;
            }
        });
    });
});

let optionCount = 2;
function addOption() {
    const container = document.getElementById('new-options');
    const div = document.createElement('div');
    div.innerHTML = `
        <input type="text" name="options[${optionCount}][text]" placeholder="Варіант ${optionCount + 1}" required>
        <label><input type="checkbox" name="options[${optionCount}][is_correct]" value="1"> Правильна</label>
        <input type="hidden" name="options[${optionCount}][is_correct]" value="0" class="fallback-correct">
    `;
    container.appendChild(div);
    optionCount++;
}
</script>
@endsection