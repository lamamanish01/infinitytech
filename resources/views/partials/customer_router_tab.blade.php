@php
    $router = $router ?? null; // from controller
    $server = $server ?? null;
@endphp

@if($router)
    <form method="POST" action="{{ route('tr069.device.router.update', $router->id) }}">
        @csrf
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-dark text-white"><strong>📡 ACS Server Info</strong></div>
                    <div class="card-body">
                        <p><strong>ACS URL:</strong><br><span class="text-primary">{{ $server->acs_url ?? '-' }}</span></p>
                        <p><strong>Username:</strong> {{ $server->acs_username ?? '-' }}</p>
                        <p><strong>Status:</strong> <span class="badge {{ $router->status == 'online' ? 'bg-success' : 'bg-danger' }}">{{ strtoupper($router->status) }}</span></p>
                        <p><strong>Last Sync:</strong><br>{{ $router->updated_at?->diffForHumans() }}</p>
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-header bg-white"><strong>📦 Router Info</strong></div>
                    <div class="card-body">
                        <p><strong>Serial:</strong> {{ $router->serial }}</p>
                        <p><strong>Product Class:</strong> {{ $router->product_class ?? '-' }}</p>
                        <p><strong>Manufacturer:</strong> {{ $router->manufacturer ?? '-' }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card shadow-sm border-primary mb-3">
                    <div class="card-header bg-primary text-white"><strong>📶 WiFi Settings (2.4G + 5G)</strong></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="{{ $router->wifi_5_ssid ? 'col-md-6' : 'col-md-12' }}">
                                <h6 class="text-primary">2.4 GHz</h6>
                                <div class="mb-2">
                                    <label>SSID</label>
                                    <input type="text" name="ssid_24" class="form-control" value="{{ old('ssid_24', $router->wifi_24_ssid ?? '') }}">
                                </div>
                                <div class="mb-2">
                                    <label>Password</label>
                                    <input type="text" name="password_24" class="form-control" value="{{ old('password_24', $router->wifi_24_password ?? '') }}">
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="hide_ssid_24" value="1" {{ $router->hide_ssid_24 ? 'checked' : '' }}>
                                    <label class="form-check-label">Hide SSID</label>
                                </div>
                            </div>
                            @if($router->wifi_5_ssid)
                                <div class="col-md-6">
                                    <h6 class="text-success">5 GHz</h6>
                                    <div class="mb-2">
                                        <label>SSID</label>
                                        <input type="text" name="ssid_5" class="form-control" value="{{ old('ssid_5', $router->wifi_5_ssid) }}">
                                    </div>
                                    <div class="mb-2">
                                        <label>Password</label>
                                        <input type="text" name="password_5" class="form-control" value="{{ old('password_5', $router->wifi_5_password) }}">
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="hide_ssid_5" value="1" {{ $router->hide_ssid_5 ? 'checked' : '' }}>
                                        <label class="form-check-label">Hide SSID</label>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" name="action" value="update_wifi" class="btn btn-sm btn-primary">🚀 Update WiFi</button>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm border-success">
                    <div class="card-header bg-success text-white"><strong>🌐 PPPoE Settings</strong></div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label>Username</label>
                            <input type="text" name="pppoe_username" class="form-control" value="{{ $customer->username }}">
                        </div>
                        <div class="mb-2">
                            <label>Password</label>
                            <input type="password" name="pppoe_password" class="form-control" value="{{ $customer->password }}">
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" name="action" value="update_pppoe" class="btn btn-sm btn-success">🚀 Update PPPoE</button>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm border-0 mt-3">
                    <div class="card-header bg-light"><strong>⚙️ Router Actions</strong></div>
                    <div class="card-body d-flex gap-2 flex-wrap">
                        <form method="POST" action="{{ route('tr069.device.reboot', $router->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">🔄 Reboot</button>
                        </form>
                        <form method="POST" action="{{ route('tr069.device.factory-reset', $router->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Factory reset router?')">⚠️ Factory Reset</button>
                        </form>
                        <form method="POST" action="{{ route('tr069.device.push-acs', $router->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-dark">🚀 Push ACS</button>
                        </form>
                        <a href="{{ route('tr069.device.logs', $router->id) }}" class="btn btn-sm btn-outline-secondary">📜 Logs</a>
                        <form method="POST" action="{{ route('tr069.device.destroy', $router->id) }}" onsubmit="return confirm('Are you sure you want to delete this router? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">🗑️ Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </form>
@else
    <div class="alert alert-warning">No router device linked with this customer.</div>
@endif
