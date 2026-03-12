@extends('layouts.app')
@section('title', $test->title)
@section('content')
<h1>Тест №{{ $test->sort_order + 1 }}</h1>
<h2>{{ $test->title }}</h2>

@if($test->description)
<div><p>{{ $test->description }}</p></div>
@endif

@isset($attempt)
<form method="POST" action="{{ route('tests.submit', $attempt) }}" id="test-form">
    @csrf
    @foreach($test->questions as $i => $question)
    <div>
        <p><strong>{{ $i + 1 }}. {{ $question->text }}</strong></p>
        @foreach($question->options as $option)
        <div>
            @if($question->type === 'single')
                <label>
                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}">
                    {{ $option->text }}
                </label>
            @else
                <label>
                    <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option->id }}">
                    {{ $option->text }}
                </label>
            @endif
        </div>
        @endforeach

        @if($question->hint)
        <div>
            <button type="button" class="hint-btn" data-question-id="{{ $question->id }}">Підказка (15 монет)</button>
            <span class="hint-text" id="hint-{{ $question->id }}" style="display:none"></span>
            <input type="hidden" name="hints_used[]" class="hint-input" disabled>
        </div>
        @endif
    </div>
    @endforeach

    <button type="submit">Завершити</button>
</form>
@else
    @if($attemptCount > 0)
        <p>Спроба #{{ $attemptCount + 1 }}. Вартість: 10 монет.</p>
    @endif
    <form method="POST" action="{{ route('tests.start', $test) }}">
        @csrf
        <button type="submit">Почати тест</button>
    </form>
@endisset
@endsection

@push('scripts')
<script>
document.querySelectorAll('.hint-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const qId = this.dataset.questionId;
        fetch(`/test-questions/${qId}/hint`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.hint) {
                document.getElementById('hint-' + qId).textContent = data.hint;
                document.getElementById('hint-' + qId).style.display = 'block';
                this.disabled = true;
                // Enable hidden input to track hint usage
                const input = this.parentElement.querySelector('.hint-input');
                input.value = qId;
                input.disabled = false;
            } else if (data.error) {
                alert(data.error);
            }
        });
    });
});
</script>
@endpush
