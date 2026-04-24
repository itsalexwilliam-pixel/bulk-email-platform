@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Unsubscribed Emails</h1>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Unsubscribed At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($unsubscribes as $item)
                            <tr>
                                <td>{{ $item->email }}</td>
                                <td>
                                    @if($item->contact)
                                        {{ $item->contact->name }} ({{ $item->contact->email }})
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ optional($item->unsubscribed_at)->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No unsubscribed emails found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($unsubscribes, 'links'))
            <div class="card-footer">
                {{ $unsubscribes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
