@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">Edit SMTP Server</h3>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('smtp.update', $server) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $server->name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Host</label>
                        <input type="text" name="host" class="form-control" value="{{ old('host', $server->host) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Port</label>
                        <input type="number" name="port" class="form-control" value="{{ old('port', $server->port) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Encryption</label>
                        <select name="encryption" class="form-select" required>
                            <option value="tls" @selected(old('encryption', $server->encryption) === 'tls')>TLS</option>
                            <option value="ssl" @selected(old('encryption', $server->encryption) === 'ssl')>SSL</option>
                            <option value="none" @selected(old('encryption', $server->encryption) === 'none')>None</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="{{ old('username', $server->username) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password (leave blank to keep unchanged)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">From Name</label>
                        <input type="text" name="from_name" class="form-control" value="{{ old('from_name', $server->from_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">From Email</label>
                        <input type="email" name="from_email" class="form-control" value="{{ old('from_email', $server->from_email) }}" required>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary">Update SMTP Server</button>
                    <a href="{{ route('smtp.index') }}" class="btn btn-outline-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
