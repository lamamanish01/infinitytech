<div class="card shadow-sm">
    <div class="card-header"><strong>Create Support Ticket</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('ticket.store') }}">@csrf
            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
            <div class="mb-3"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control" value="{{ old('subject') }}" required></div>
            <div class="mb-3"><label class="form-label">Priority</label><select name="priority" class="form-control"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
            <div class="mb-3"><label class="form-label">Message</label><textarea name="message" rows="6" class="form-control" required>{{ old('message') }}</textarea></div>
            <button type="submit" class="btn btn-success"><i class="fas fa-ticket-alt"></i> Create Ticket</button>
        </form>
    </div>
</div>
