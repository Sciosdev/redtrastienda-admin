@extends('layouts.admin.app')
@section('title', translate('encuesta_crear'))

@section('content')
<div class="content container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h1 class="h2 mb-0">{{ translate('encuesta_crear') }}</h1>
        <a class="btn btn-outline-secondary" href="{{ route('admin.surveys.index') }}">{{ translate('encuesta_volver') }}</a>
    </div>
    @if($errors->any())
        <div class="alert alert-danger" role="alert"><ul class="mb-0">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul></div>
    @endif
    <form method="POST" action="{{ route('admin.surveys.store') }}" id="survey-form">
        @csrf
        <div class="card mb-3"><div class="card-body">
            <div class="form-group">
                <label for="survey-title">{{ translate('encuesta_titulo') }}</label>
                <input class="form-control" id="survey-title" name="title" required maxlength="160" value="{{ old('title') }}">
            </div>
            <div class="form-group mb-0">
                <label for="survey-description">{{ translate('encuesta_descripcion') }}</label>
                <textarea class="form-control" id="survey-description" name="description" rows="2" maxlength="2000">{{ old('description') }}</textarea>
            </div>
        </div></div>
        <p class="text-muted">{{ translate('encuesta_editor_ayuda') }}</p>
        <div id="survey-questions">
            @foreach(old('questions', [['text' => '', 'options_text' => '', 'allow_other' => 1]]) as $index => $question)
                <div class="card mb-3 survey-question">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h5 mb-0">{{ translate('encuesta_pregunta') }} <span class="question-number">{{ $loop->iteration }}</span></h2>
                            <button class="btn btn-outline-danger btn-sm remove-question" type="button">{{ translate('encuesta_quitar') }}</button>
                        </div>
                        <div class="form-group">
                            <label for="question-text-{{ $index }}">{{ translate('encuesta_pregunta') }}</label>
                            <textarea id="question-text-{{ $index }}" class="form-control" data-field="text" name="questions[{{ $index }}][text]" required maxlength="500" rows="2">{{ $question['text'] ?? '' }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="question-options-{{ $index }}">{{ translate('encuesta_opciones_ayuda') }}</label>
                            <textarea id="question-options-{{ $index }}" class="form-control" data-field="options_text" name="questions[{{ $index }}][options_text]" required maxlength="2100" rows="3">{{ $question['options_text'] ?? '' }}</textarea>
                        </div>
                        <input type="hidden" data-field="allow_other" name="questions[{{ $index }}][allow_other]" value="0">
                        <label class="d-flex align-items-center gap-2 mb-0">
                            <input type="checkbox" data-field="allow_other" name="questions[{{ $index }}][allow_other]" value="1" {{ !empty($question['allow_other']) ? 'checked' : '' }}>
                            {{ translate('encuesta_permitir_otros') }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
        <button class="btn btn-outline-primary mb-4" id="survey-add-question" type="button">{{ translate('encuesta_agregar_pregunta') }}</button>
        <div class="card"><div class="card-body">
            <input type="hidden" name="is_active" value="0">
            <label class="d-flex align-items-center gap-2 mb-3">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                {{ translate('encuesta_publicar_ahora') }}
            </label>
            <p class="text-muted small">{{ translate('encuesta_guardado_ayuda') }}</p>
            <button class="btn btn-primary" type="submit">{{ translate('encuesta_guardar') }}</button>
        </div></div>
    </form>
</div>
@endsection

@push('script')
<script>
(() => {
    const form = document.getElementById('survey-form');
    const questions = document.getElementById('survey-questions');
    const addButton = document.getElementById('survey-add-question');
    if (!form || form.dataset.initialized) return;
    form.dataset.initialized = '1';
    const template = questions.firstElementChild.cloneNode(true);
    function renumber() {
        const cards = questions.querySelectorAll('.survey-question');
        cards.forEach((card, index) => {
            card.querySelector('.question-number').textContent = index + 1;
            card.querySelector('.remove-question').disabled = cards.length === 1;
            card.querySelectorAll('[data-field]').forEach(input => {
                input.name = `questions[${index}][${input.dataset.field}]`;
                if (input.id) {
                    const label = card.querySelector(`label[for="${input.id}"]`);
                    input.id = `question-${input.dataset.field}-${index}`;
                    if (label) label.htmlFor = input.id;
                }
            });
        });
        addButton.disabled = cards.length >= 20;
    }
    addButton.addEventListener('click', () => {
        if (questions.children.length >= 20) return;
        const card = template.cloneNode(true);
        card.querySelectorAll('textarea').forEach(input => input.value = '');
        card.querySelector('input[type="checkbox"]').checked = true;
        questions.appendChild(card);
        renumber();
        card.querySelector('textarea').focus();
    });
    questions.addEventListener('click', event => {
        const button = event.target.closest('.remove-question');
        if (button && questions.children.length > 1) {
            button.closest('.survey-question').remove();
            renumber();
        }
    });
    form.addEventListener('submit', () => {
        form.querySelector('button[type="submit"]').disabled = true;
    });
    renumber();
})();
</script>
@endpush
