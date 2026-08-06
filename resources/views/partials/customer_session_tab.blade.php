{{-- Active Session --}}
<h6 class="mb-2">Active Session</h6>
@if($customer->activeSession)
    <div class="table-responsive">
        <table class="table table-sm table-striped table-hover text-nowrap">
            <thead class="table-light">
                <tr>
                    <th>IP</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Time</th>
                    <th>Mac Address</th>
                    <th>NAS IP</th>
                    <th>Upload</th>
                    <th>Download</th>
                    <th>Server</th>
                </tr>
            </thead>
            <tbody>
                @php $session = $customer->activeSession; @endphp
                <tr>
                    <td>
                        @if($session->ip_address)
                            <a href="http://{{ $session->ip_address }}" target="_blank" rel="noopener noreferrer">
                                {{ $session->ip_address }}
                            </a>
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($session->acctstarttime)->format('Y-m-d H:i:s A') }}</td>
                    <td>
                        @if($lastSession && $lastSession->acctstoptime)
                            {{ \Carbon\Carbon::parse($lastSession->acctstoptime)->format('Y-m-d h:i:s A') }}
                        @else
                            <span class="badge badge-success">Never Disconnected</span>
                        @endif
                    </td>
                    <td>{{ $session->session_time_human ?? '-' }}</td>
                    <td>{{ $session->mac_address ?? '-' }}</td>
                    <td>{{ $session->nasipaddress ?? '-' }}</td>
                    <td>{{ $session->upload_mb ?? '-' }}</td>
                    <td>{{ $session->download_mb ?? '-' }}</td>
                    <td>{{ $session->ppp_server ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@else
    <div class="alert alert-secondary">No active session</div>
@endif

{{-- Live Traffic Chart --}}
<div class="card mt-3 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>📊 Live PPP User Traffic</strong>
        <div class="text-muted small text-end">
            <span id="traffic-download" class="me-3" style="color: #20c997;">0 bps</span>/
            <span id="traffic-upload" class="me-3" style="color: #0d6efd;">0 bps</span>
            <span id="traffic-update-time">Updating...</span>
        </div>
    </div>
    <div class="card-body">
        <div style="position: relative; height: 280px; min-height: 280px; width: 100%;">
            <canvas id="trafficChart"></canvas>
        </div>
    </div>
</div>

{{-- Daily Traffic Chart --}}
<div class="card mt-3 shadow-sm">
    <div class="card-header bg-white">
        <strong>📊 Daily Traffic Volume (Last 30 Days)</strong>
    </div>
    <div class="card-body">
        <div style="position: relative; height: 300px; min-height: 300px; width: 100%;">
            <canvas id="dailyTrafficChart"></canvas>
        </div>
    </div>
</div>

{{-- Previous Sessions --}}
<h6 class="mt-4 mb-2">Previous Sessions</h6>
@if($previousSessions && $previousSessions->count() > 0)
    <div class="table-responsive">
        <table class="table table-sm table-striped table-hover text-nowrap">
            <thead class="table-light">
                <tr>
                    <th>IP</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Time</th>
                    <th>Mac Address</th>
                    <th>NAS IP</th>
                    <th>Upload</th>
                    <th>Download</th>
                    <th>Server</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($previousSessions as $session)
                    <tr>
                        <td>{{ $session->ip_address ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($session->acctstarttime)->format('Y-m-d H:i:s A') }}</td>
                        <td>
                            @if($session->acctstoptime)
                                {{ \Carbon\Carbon::parse($session->acctstoptime)->format('Y-m-d H:i:s A') }}
                            @else
                                <span class="badge badge-success">Active</span>
                            @endif
                        </td>
                        <td>{{ $session->session_time_human ?? '-' }}</td>
                        <td>{{ $session->mac_address ?? '-' }}</td>
                        <td>{{ $session->nasipaddress ?? '-' }}</td>
                        <td>{{ $session->upload_mb ?? '-' }}</td>
                        <td>{{ $session->download_mb ?? '-' }}</td>
                        <td>{{ $session->ppp_server ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center">No previous sessions found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">{{ $previousSessions->links() }}</div>
    </div>
@else
    <div class="alert alert-light">No previous session found</div>
@endif
