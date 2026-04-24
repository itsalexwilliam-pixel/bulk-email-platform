@extends('layouts.app')

@section('content')
<h3 class="mb-3">Groups</h3>

<div class="row">
    <div class="col-md-5">
        <div class="card mb-3">
            <div class="card-header">Create Group</div>
            <div class="card-body">
                <form method="POST" action="{{ route('groups.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Group Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Create</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header">All Groups</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contacts</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $group)
                            <tr>
                                <td>{{ $group->name }}</td>
                                <td>{{ $group->contacts_count }}</td>
                                <td>
                                    <form method="POST" action="{{ route('groups.destroy', $group) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this group?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted text-center">No groups found.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $groups->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
