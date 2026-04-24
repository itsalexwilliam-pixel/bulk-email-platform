@extends('layouts.app')

@section('content')
<h3 class="mb-3">Import Contacts from CSV</h3>

<div class="card">
    <div class="card-body">
        <form action="{{ route('import.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label">CSV File</label>
                <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Name Column Header</label>
                <input type="text" name="name_column" class="form-control" value="{{ old('name_column', 'name') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email Column Header</label>
                <input type="text" name="email_column" class="form-control" value="{{ old('email_column', 'email') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Assign to Groups (optional)</label>
                <select name="groups[]" class="form-select" multiple>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Import</button>
        </form>
    </div>
</div>
@endsection
