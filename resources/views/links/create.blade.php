@extends('layouts.app')

@section('title', 'Create New Link')

@section('content')
<div class="card">
    <div class="card-header">➕ Create Industrial Link</div>
    <div class="card-body">
        <form action="{{ route('links.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" required value="{{ old('title') }}">
            </div>
            
            <div class="form-group">
                <label class="form-label">URL *</label>
                <input type="url" name="url" class="form-control" required value="{{ old('url') }}">
                <small>Must be valid HTTPS/HTTP URL</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Type *</label>
                <select name="type" class="form-control" required>
                    <option value="">Select type</option>
                    <option value="whatsapp" {{ old('type') == 'whatsapp' ? 'selected' : '' }}>WhatsApp Link</option>
                    <option value="group" {{ old('type') == 'group' ? 'selected' : '' }}>Group Link</option>
                    <option value="jforce" {{ old('type') == 'jforce' ? 'selected' : '' }}>JForce Link</option>
                    <option value="study" {{ old('type') == 'study' ? 'selected' : '' }}>Study Link</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    Active
                </label>
            </div>
            
            <div class="form-group">
                <label class="form-label">Expiration Date (Optional)</label>
                <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
                <small>Leave empty for no expiration</small>
            </div>
            
            <!-- GDPR Consent Checkbox -->
            <div class="form-group" style="background: #fef3c7; padding: 1rem; border-radius: 0.375rem;">
                <label class="form-label">
                    <input type="checkbox" name="gdpr_consent" value="1" required>
                    I consent to the processing of my data under GDPR Article 6(1)(a) *
                </label>
                <small>You may withdraw consent at any time by requesting deletion.</small>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">Create Link</button>
                <a href="{{ route('links.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection