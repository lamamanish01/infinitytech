<div class="table-responsive">
    <table class="table table-sm table-striped table-hover text-nowrap">
        <thead>
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Pass</th>
                <th>Reply</th>
                <th>Reply Message</th>
                <th>Nas IP Address</th>
                <th>Mac Address</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($authLogs as $log)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $log->username }}</td>
                    <td>{{ $log->pass }}</td>
                    <td>
                        @if($log->reply == 'Access-Accept')
                            <span class="badge bg-success">Access-Accept</span>
                        @elseif($log->reply == 'Access-Reject')
                            <span class="badge bg-danger">Access-Reject</span>
                        @else
                            <span class="badge bg-secondary">{{ $log->reply }}</span>
                        @endif
                    </td>
                    <td>{{ $log->reply_message }}</td>
                    <td>{{ $log->nasipaddress }}</td>
                    <td>{{ $log->mac }}</td>
                    <td>{{ optional($log->authdate)->toDateTimeString() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-3">{{ $authLogs->links() }}</div>
</div>
