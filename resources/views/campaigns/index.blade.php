@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Campaigns</h3>
    <a href="{{ route('campaigns.create') }}" class="btn btn-primary">Create Campaign</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Scheduled At</th>
                        <th>Contacts</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($campaigns as $campaign)
                        <tr>
                            <td>{{ $campaign->name }}</td>
                            <td>{{ $campaign->subject }}</td>
                            <td><span class="badge bg-secondary text-uppercase">{{ $campaign->status }}</span></td>
                            <td>{{ $campaign->scheduled_at ? \Illuminate\Support\Carbon::parse($campaign->scheduled_at)->format('Y-m-d H:i') : '-' }}</td>
                            <td>{{ $campaign->contacts_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('campaigns.edit', $campaign) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('campaigns.destroy', $campaign) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this campaign?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No campaigns found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $campaigns->links() }}
</div>
@endsection
