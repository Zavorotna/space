@if($lessonsNeedingReport->count() > 0)
<div class="report-section report-section--required">
    <h2>⚠️ Потрібен звіт ({{ $lessonsNeedingReport->count() }})</h2>
    <p class="text-sm text-muted">Заповніть звіт по кожному заняттю — без цього звіт не закриється.</p>
    @foreach($lessonsNeedingReport as $lesson)
    @if($lesson->course)
    @php $isIndividual = $lesson->course->type === 'individual'; @endphp
    <div class="report-item">
        <strong>{{ $lesson->date->format('d.m.Y') }}</strong>
        · {{ $lesson->course->title }}
        {{ $lesson->title ? "· {$lesson->title}" : '' }}
        · {{ substr($lesson->start_time,0,5) }}–{{ substr($lesson->end_time,0,5) }}
        ({{ $lesson->plannedMinutes() }} хв)
        <span class="text-sm text-muted">{{ $isIndividual ? 'Індивідуальне' : 'Групове' }}</span>

        <form method="POST" action="{{ route('teacher.schedule.complete', $lesson) }}" class="mt-1">
            @csrf
            <div class="flex-row flex-start">
                <div>
                    <label>Статус</label><br>
                    @if($isIndividual)
                        <select name="completion_status" required id="status-{{ $lesson->id }}"
                                onchange="toggleReportFields({{ $lesson->id }}, this.value, true)">
                            <option value="full" selected>Повне заняття</option>
                            <option value="partial">Часткове</option>
                            <option value="cancelled">Скасовано</option>
                        </select>
                    @else
                        <select name="completion_status" required id="status-{{ $lesson->id }}"
                                onchange="toggleReportFields({{ $lesson->id }}, this.value, false)">
                            <option value="full" selected>Повне заняття</option>
                            <option value="cancelled">Скасовано</option>
                            <option value="rescheduled">Перенесено</option>
                        </select>
                    @endif
                </div>
                @if($isIndividual)
                <div id="rpt-minutes-{{ $lesson->id }}" style="display:none;">
                    <label>Фактично годин</label><br>
                    <input type="number" name="actual_hours" min="0.5" max="10" step="0.5"
                           placeholder="{{ round($lesson->plannedMinutes() / 60, 1) }}" class="input-sm">
                </div>
                @endif
                <div>
                    <label>Примітка</label><br>
                    <input type="text" name="completion_note" placeholder="необов'язково" class="input-md">
                </div>
            </div>
            <div id="rpt-makeup-{{ $lesson->id }}" class="makeup-panel" style="display:none;">
                <label><input type="checkbox" name="schedule_makeup" value="1"
                              id="rpt-makeup-cb-{{ $lesson->id }}"
                              onchange="toggleReportMakeup({{ $lesson->id }})">
                    Запланувати відпрацювання</label>
                <div id="rpt-makeup-date-{{ $lesson->id }}" class="makeup-date-row" style="display:none;">
                    <input type="date" name="makeup_date">
                    <input type="time" name="makeup_start">
                    <input type="time" name="makeup_end">
                </div>
            </div>
            <button type="submit" class="btn mt-1">Зберегти звіт</button>
        </form>
    </div>
    @endif
    @endforeach
</div>
<script>
function toggleReportFields(id, status, isIndividual) {
    const minutesEl = document.getElementById('rpt-minutes-' + id);
    const makeupEl  = document.getElementById('rpt-makeup-' + id);
    if (minutesEl) minutesEl.style.display = (status === 'partial') ? 'block' : 'none';
    if (makeupEl)  makeupEl.style.display  = (status === 'cancelled' || status === 'rescheduled') ? 'block' : 'none';
}
function toggleReportMakeup(id) {
    const cb = document.getElementById('rpt-makeup-cb-' + id);
    document.getElementById('rpt-makeup-date-' + id).style.display = cb.checked ? 'flex' : 'none';
}
</script>
@endif