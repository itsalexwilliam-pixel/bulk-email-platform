@extends('layouts.app')

@section('content')
<h3 class="mb-3">Import Summary</h3>

<div class="card">
    <div class="card-body">
        <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between">
                <span>Total Rows</span>
                <strong>{{ $total }}</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span>Imported</span>
                <strong class="text-success">{{ $imported }}</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span>Skipped</span>
                <strong class="text-danger">{{ $skipped }}</strong>
            </li>
        </ul>

        <div class="mt-3">
            <a href="{{ route('contacts.index') }}" class="btn btn-primary">Go to Contacts</a>
            <a href="{{ route('import.index') }}" class="btn btn-secondary">Import Another File</a>
        </div>
    </div>
</div>
@endsection
