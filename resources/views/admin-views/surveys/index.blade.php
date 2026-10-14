@extends('layouts.admin.app')
@section('title', translate('encuestas'))

@section('content')
<div class="content container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h1 class="h2 mb-0">{{ translate('encuestas') }}</h1>
        <a class="btn btn-primary" href="{{ route('admin.surveys.create') }}">{{ translate('encuesta_crear') }}</a>
    </div>
    @if(session('survey_success'))
        <div class="alert alert-success" role="status">{{ session('survey_success') }}</div>
    @endif
    <div class="card">
        <div class="card-body">
            <p class="text-muted">{{ translate('encuesta_publicacion_ayuda') }}</p>
            <div class="table-responsive">
                <table class="table table-hover table-align-middle">
                    <thead><tr>
                        <th>{{ translate('encuesta_titulo') }}</th>
                        <th>{{ translate('status') }}</th>
                        <th>{{ translate('encuesta_respuestas') }}</th>
                        <th>{{ translate('action') }}</th>
                    </tr></thead>
                    <tbody>
                    @forelse($surveys as $survey)
                        <tr>
                            <td>
                                <strong>{{ $survey->title }}</strong>
                                <div class="text-muted small">{{ count($survey->questions) }} {{ translate('encuesta_preguntas') }}</div>
                            </td>
                            <td><span class="badge badge-soft-{{ $survey->is_active ? 'success' : 'secondary' }}">{{ translate($survey->is_active ? 'encuesta_publicada' : 'encuesta_cerrada') }}</span></td>
                            <td>{{ $survey->responses_count }}</td>
                            <td><div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.surveys.responses', $survey->id) }}">{{ translate('encuesta_ver_respuestas') }}</a>
                                <form method="POST" action="{{ route('admin.surveys.status', $survey->id) }}">
                                    @csrf
                                    <input type="hidden" name="is_active" value="{{ $survey->is_active ? 0 : 1 }}">
                                    <button class="btn btn-outline-secondary btn-sm" type="submit">{{ translate($survey->is_active ? 'encuesta_cerrar' : 'encuesta_publicar') }}</button>
                                </form>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-5">{{ translate('encuesta_sin_encuestas') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $surveys->links() }}
        </div>
    </div>
</div>
@endsection
