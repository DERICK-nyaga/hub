@extends('layouts.app')

@section('title', 'Manage Links - Industrial CRUD')

@section('content')
<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span>📋 Link Inventory</span>
            <a href="{{ route('links.create') }}" class="btn btn-primary">+ New Link</a>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter Bar -->
        <form method="GET" action="{{ route('links.index') }}" style="margin-bottom: 1.5rem;">
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <select name="type" class="form-control" style="width: auto;">
                    <option value="">All Types</option>
                    <option value="whatsapp" {{ request('type') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="group" {{ request('type') == 'group' ? 'selected' : '' }}>Group</option>
                    <option value="jforce" {{ request('type') == 'jforce' ? 'selected' : '' }}>JForce</option>
                    <option value="study" {{ request('type') == 'study' ? 'selected' : '' }}>Study</option>
                </select>
                
                <select name="status" class="form-control" style="width: auto;">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                
                <input type="text" name="search" placeholder="Search by title/description..." 
                       value="{{ request('search') }}" class="form-control" style="width: 300px;">
                
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="{{ route('links.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
        
        <!-- Links Table -->
        <table class="industrial-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>URL</th>
                    <th>Status</th>
                    <th>Clicks</th>
                    <th>Expires</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($links as $link)
                <tr>
                    <td><strong>{{ $link->title }}</strong><br>
                        <small>{{ Str::limit($link->description, 50) }}</small>
                    </td>
                    <td>
                        <span style="padding: 0.25rem 0.5rem; background: #e0e7ff; border-radius: 0.25rem; font-size: 0.75rem;">
                            {{ ucfirst($link->type) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" 
                           style="color: var(--industrial-blue); text-decoration: none;">
                            {{ Str::limit($link->url, 40) }}
                        </a>
                    </td>
                    <td>
                        @if($link->is_active && !$link->isExpired())
                            <span style="color: var(--industrial-success);">✅ Active</span>
                        @else
                            <span style="color: var(--industrial-warning);">⛔ Inactive/Expired</span>
                        @endif
                    </td>
                    <td>{{ $link->click_count }}</td>
                    <td>{{ $link->expires_at ? $link->expires_at->format('Y-m-d') : 'Never' }}</td>
                    <td>
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="{{ route('links.show', $link) }}" class="btn" style="background: #6b7280; color: white; padding: 0.25rem 0.5rem;">View</a>
                            <a href="{{ route('links.edit', $link) }}" class="btn" style="background: #059669; color: white; padding: 0.25rem 0.5rem;">Edit</a>
                            <form action="{{ route('links.destroy', $link) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn" style="background: #dc2626; color: white; padding: 0.25rem 0.5rem;" 
                                        onclick="return confirm('GDPR Notice: This action will soft-delete the link. To permanently remove (Right to Erasure), please specify.')">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem;">No links found. Create your first link!</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div style="margin-top: 1.5rem;">
            {{ $links->appends(request()->query())->links() }}
        </div>
        
        <!-- GDPR Compliance Note -->
        <div class="gdpr-notice" style="margin-top: 1rem;">
            <small>🔒 GDPR Compliant: Your data is processed under Art. 6(1)(a) consent. You have the right to access, rectify, and erase your data.</small>
        </div>
    </div>
</div>
@endsection