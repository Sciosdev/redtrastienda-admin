@extends('layouts.admin.app')
@section('title', translate('encuesta_respuestas'))

@section('content')
<div class="content container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h2 mb-1">{{ $survey->title }}</h1>
            <span class="badge badge-soft-{{ $survey->is_active ? 'success' : 'secondary' }}">{{ translate($survey->is_active ? 'encuesta_publicada' : 'encuesta_cerrada') }}</span>
            <span class="ml-2">{{ $responses->total() }} {{ translate('encuesta_respuestas') }}</span>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-outline-secondary" href="{{ route('admin.surveys.index') }}">{{ translate('encuesta_volver') }}</a>
            <a class="btn btn-primary" href="{{ route('admin.surveys.export', $survey->id) }}">{{ translate('encuesta_descargar_csv') }}</a>
        </div>
    </div>
    @if(session('survey_success'))<div class="alert alert-success" role="status">{{ session('survey_success') }}</div>@endif
    <div class="card mb-4"><div class="card-body">
        <h2 class="h5">{{ translate('encuesta_preguntas') }}</h2>
        @if($survey->description)<p class="text-muted">{{ $survey->description }}</p>@endif
        <ol class="mb-0">
        @foreach($survey->questions as $question)
            <li class="mb-2"><strong>{{ $question['text'] }}</strong>
                <div class="text-muted">{{ implode(' · ', $question['options']) }}@if($question['allow_other']) · {{ translate('encuesta_otros') }}@endif</div>
            </li>
        @endforeach
        </ol>
    </div></div>
    <div class="card"><div class="card-body">
        <h2 class="h5 mb-3">{{ translate('encuesta_respuestas') }}</h2>
        @forelse($responses as $response)
            @php($answers = collect($response->answers)->keyBy('question_id'))
            <div class="border rounded p-3 mb-3">
                <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                    <div><strong>{{ trim(($response->customer?->f_name ?? '') . ' ' . ($response->customer?->l_name ?? '')) ?: '#' . $response->customer_id }}</strong>
                        <div class="text-muted small">{{ $response->customer?->email }}</div>
                    </div>
                    <time class="text-muted">{{ $response->created_at->format('d/m/Y H:i') }}</time>
                </div>
                @foreach($survey->questions as $question)
                    @php($answer = $answers->get($question['id']))
                    <div class="mb-2">
                        <strong>{{ $loop->iteration }}. {{ $question['text'] }}</strong>
                        <div style="white-space: pre-wrap; overflow-wrap: anywhere">@if($answer && $answer['option'] === 'other'){{ translate('encuesta_otros') }}: {{ $answer['other_text'] }}@else{{ $question['options'][(int) ($answer['option'] ?? -1)] ?? '—' }}@endif</div>
                    </div>
                @endforeach
            </div>
        @empty
            <p class="text-muted text-center py-4">{{ translate('encuesta_sin_respuestas') }}</p>
        @endforelse
        {{ $responses->links() }}
    </div></div>
</div>
@endsection
