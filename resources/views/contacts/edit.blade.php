@extends('layouts.app')

@section('content')
<h3 class="mb-3">Edit Contact</h3>

<div class="card">
    <div class="card-body">
        <form action="{{ route('contacts.update', $contact) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $contact->name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $contact->email) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Groups</label>
                <select name="groups[]" class="form-select" multiple>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ in_array($group->id, old('groups', $selectedGroups)) ? 'selected' : '' }}>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('contacts.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
