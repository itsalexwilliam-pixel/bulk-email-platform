@extends('layouts.app')

@section('content')
<h3 class="mb-3">Edit Campaign</h3>

<form action="{{ route('campaigns.update', $campaign) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card mb-3">
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Campaign Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $campaign->name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-control" value="{{ old('subject', $campaign->subject) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email Body</label>
                <textarea name="body" id="body-editor" class="form-control" rows="12" required>{{ old('body', $campaign->body) }}</textarea>
            </div>

            <button type="button" class="btn btn-outline-secondary mb-3" data-bs-toggle="modal" data-bs-target="#previewModal" onclick="previewEmail()">
                Preview
            </button>

            <div class="mb-3">
                <label class="form-label">Schedule (optional)</label>
                <input type="datetime-local" name="scheduled_at" class="form-control"
                       value="{{ old('scheduled_at', $campaign->scheduled_at ? \Illuminate\Support\Carbon::parse($campaign->scheduled_at)->format('Y-m-d\TH:i') : '') }}">
                <small class="text-muted">If set, campaign status becomes scheduled. Otherwise it is saved as draft.</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Select Contacts</label>
                <select name="contact_ids[]" class="form-select" multiple size="8">
                    @foreach($contacts as $contact)
                        <option value="{{ $contact->id }}" @selected(collect(old('contact_ids', $selectedContactIds))->contains($contact->id))>
                            {{ $contact->name }} ({{ $contact->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Select Groups</label>
                <select name="group_ids[]" class="form-select" multiple size="6">
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" @selected(collect(old('group_ids'))->contains($group->id))>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Contacts from selected groups are merged with manually selected contacts.</small>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Update Campaign</button>
    <a href="{{ route('campaigns.index') }}" class="btn btn-secondary">Cancel</a>
</form>

<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="previewBody" class="border rounded p-3 bg-light"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#body-editor',
        height: 320,
        menubar: true,
        plugins: 'lists link image table code preview',
        toolbar: 'undo redo | formatselect | bold italic underline | bullist numlist | link table | code preview'
    });

    function previewEmail() {
        const html = tinymce.get('body-editor') ? tinymce.get('body-editor').getContent() : '';
        document.getElementById('previewBody').innerHTML = html;
    }
</script>
@endsection
