<div class="table-responsive">
    <table class="table table-sm table-striped table-hover text-nowrap">
        <thead><tr><th>#</th><th>Title</th><th>Message</th><th>User</th><th>Date</th><th>Status</th></tr></thead>
        <tbody>
            @forelse($activityLogs as $activity)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><i class="{{ $activity->icon ?? 'fas fa-bell' }}"></i> {{ $activity->title }}</td>
                    <td>{{ $activity->message ?? '-' }}</td>
                    <td>{{ $activity->user->name ?? 'System' }}</td>
                    <td>{{ $activity->created_at->format('Y-m-d H:i') }}<br><small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small></td>
                    <td>@if($activity->is_read)<span class="badge bg-success">Read</span>@else<span class="badge bg-warning">Unread</span>@endif</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">No activities found for this customer.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $activityLogs->links() }}</div>
