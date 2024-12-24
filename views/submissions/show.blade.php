@extends('formbuilder::layout')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card rounded-0">
                <div class="card-header">
                    <h5 class="card-title">
                        Viewing Submission #{{ $submission['id'] }} for form '{{ $form['name'] ?? 'Unnamed Form' }}'

                        <div class="btn-toolbar float-right" role="toolbar">
                            <div class="btn-group" role="group" aria-label="First group">
                                <a href="{{ route('formbuilder::forms.submissions.index', $form['id'] ?? 0) }}" class="btn btn-primary float-md-right btn-sm" title="Back To Submissions">
                                    <i class="fa fa-arrow-left"></i> 
                                </a>
                                <form action="{{ route('formbuilder::forms.submissions.destroy', [$form['id'] ?? 0, $submission['id']]) }}" method="POST" id="deleteSubmissionForm_{{ $submission['id'] }}" class="d-inline-block">
                                    @csrf 
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-danger btn-sm rounded-0 confirm-form" data-form="deleteSubmissionForm_{{ $submission['id'] }}" data-message="Delete submission" title="Delete this submission?">
                                        <i class="fa fa-trash-o"></i> 
                                    </button>
                                </form>
                            </div>
                        </div>
                    </h5>
                </div>

                <ul class="list-group list-group-flush">
                    @foreach($form_headers as $header)
                        <li class="list-group-item">
                            <strong>{{ $header['label'] ?? title_case($header['name']) }}: </strong> 
                            <span class="float-right">
                                {{ $submission[$header['name']] ?? 'n/a' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card rounded-0">
                <div class="card-header">
                    <h5 class="card-title">Details</h5>
                </div>

                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>Form: </strong> 
                        <span class="float-right">{{ $form['name'] ?? 'Unnamed Form' }}</span>
                    </li>
                    <li class="list-group-item">
                        <strong>Last Updated On: </strong> 
                        <span class="float-right">{{ \Carbon\Carbon::parse($submission['updated_at'])->toDayDateTimeString() }}</span>
                    </li>
                    <li class="list-group-item">
                        <strong>Submitted On: </strong> 
                        <span class="float-right">{{ \Carbon\Carbon::parse($submission['created_at'])->toDayDateTimeString() }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
