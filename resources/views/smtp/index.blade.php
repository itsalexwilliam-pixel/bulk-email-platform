@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">SMTP Servers</h3>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">Add SMTP Server</div>
        <div class="card-body">
            <form method="POST" action="{{ route('smtp.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Host</label>
                        <input type="text" name="host" class="form-control" value="{{ old('host') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Port</label>
                        <input type="number" name="port" class="form-control" value="{{ old('port', 587) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Encryption</label>
                        <select name="encryption" class="form-select" required>
                            <option value="tls" @selected(old('encryption') === 'tls')>TLS</option>
                            <option value="ssl" @selected(old('encryption') === 'ssl')>SSL</option>
                            <option value="none" @selected(old('encryption') === 'none')>None</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">From Name</label>
                        <input type="text" name="from_name" class="form-control" value="{{ old('from_name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">From Email</label>
                        <input type="email" name="from_email" class="form-control" value="{{ old('from_email') }}" required>
                    </div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-primary">Save SMTP Server</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Configured Servers</div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Host</th>
                        <th>Port</th>
                        <th>Username</th>
                        <th>From</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($servers as $server)
                        <tr>
                            <td>{{ $server->name }}</td>
                            <td>{{ $server->host }}</td>
                            <td>{{ $server->port }}</td>
                            <td>{{ $server->username }}</td>
                            <td>{{ $server->from_name }} <{{ $server->from_email }}></td>
                            <td>
                                <span class="badge {{ $server->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $server->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('smtp.edit', $server) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('smtp.toggle', $server) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-warning">
                                        {{ $server->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form action="{{ route('smtp.destroy', $server) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this SMTP server?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">No SMTP servers configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">
            {{ $servers->links() }}
        </div>
    </div>
</div>
@endsection
