@extends('layouts.app')

@section('title', 'Housekeeping Board')
@section('page-title', 'Housekeeping & Maintenance')
@section('breadcrumb', 'Operations › Housekeeping Board')

@section('content')

<div class="page-header">
    <div>
        <h1>Housekeeping Board</h1>
        <p>Real-time room status, cleaning task dispatching & maintenance tracking for {{ $property?->name ?: 'Selected Property' }}</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#logMaintenanceModal">
            <i class="bi bi-tools me-1"></i> Log Maintenance Request
        </button>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
            <div>
                <div class="stat-number">{{ $rooms->whereIn('status', ['clean', 'inspected'])->count() }}</div>
                <div class="stat-label">Clean / Inspected</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon amber"><i class="bi bi-exclamation-circle"></i></div>
            <div>
                <div class="stat-number">{{ $rooms->where('status', 'dirty')->count() }}</div>
                <div class="stat-label">Dirty Rooms</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-brush"></i></div>
            <div>
                <div class="stat-number">{{ $rooms->where('status', 'cleaning')->count() }}</div>
                <div class="stat-label">Cleaning In-Progress</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon red"><i class="bi bi-tools"></i></div>
            <div>
                <div class="stat-number">{{ $rooms->where('status', 'out_of_order')->count() }}</div>
                <div class="stat-label">Out of Order / Service</div>
            </div>
        </div>
    </div>
</div>

<!-- Main Tabs -->
<ul class="nav nav-tabs mb-4" id="housekeepingTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="kanban-tab" data-bs-toggle="tab" data-bs-target="#kanban" type="button" role="tab">
            <i class="bi bi-kanban me-1"></i> Room Kanban Board
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button" role="tab">
            <i class="bi bi-list-task me-1"></i> Cleaning Tasks ({{ $tasks->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance" type="button" role="tab">
            <i class="bi bi-wrench me-1"></i> Maintenance Logs ({{ $maintenanceRequests->count() }})
        </button>
    </li>
</ul>

<div class="tab-content" id="housekeepingTabsContent">
    <!-- 1. Room Kanban Board -->
    <div class="tab-pane fade show active" id="kanban" role="tabpanel">
        <div class="row g-3">
            <!-- Column 1: Dirty -->
            <div class="col-md-3">
                <div class="card h-100 border-top border-3 border-warning">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-warning"><i class="bi bi-exclamation-triangle me-1"></i> Dirty</span>
                        <span class="badge bg-warning text-dark">{{ $rooms->where('status', 'dirty')->count() }}</span>
                    </div>
                    <div class="card-body p-2" style="min-height: 400px; background: rgba(245,158,11,0.03);">
                        @forelse($rooms->where('status', 'dirty') as $rm)
                            <div class="card mb-2 shadow-sm border-0">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0">Room {{ $rm->room_number }}</h6>
                                        <span class="badge bg-secondary">{{ $rm->roomType?->name }}</span>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        {{ $rm->building?->name ?: 'Main Building' }} @if($rm->floor) • Floor {{ $rm->floor->floor_number }} @endif
                                    </p>
                                    <div class="d-flex gap-1">
                                        <form method="POST" action="{{ route('admin.housekeeping.room-status', $rm) }}" class="w-100">
                                            @csrf
                                            <input type="hidden" name="status" value="cleaning">
                                            <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                                                <i class="bi bi-play-fill"></i> Start Cleaning
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.housekeeping.room-status', $rm) }}" class="w-100">
                                            @csrf
                                            <input type="hidden" name="status" value="clean">
                                            <button type="submit" class="btn btn-sm btn-success w-100">
                                                <i class="bi bi-check2"></i> Quick Clean
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">No dirty rooms</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Column 2: Cleaning In Progress -->
            <div class="col-md-3">
                <div class="card h-100 border-top border-3 border-info">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-info"><i class="bi bi-arrow-repeat me-1"></i> Cleaning</span>
                        <span class="badge bg-info text-dark">{{ $rooms->where('status', 'cleaning')->count() }}</span>
                    </div>
                    <div class="card-body p-2" style="min-height: 400px; background: rgba(59,111,240,0.03);">
                        @forelse($rooms->where('status', 'cleaning') as $rm)
                            <div class="card mb-2 shadow-sm border-0">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0">Room {{ $rm->room_number }}</h6>
                                        <span class="badge bg-info">{{ $rm->roomType?->name }}</span>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        Housekeeper actively cleaning...
                                    </p>
                                    <form method="POST" action="{{ route('admin.housekeeping.room-status', $rm) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="clean">
                                        <button type="submit" class="btn btn-sm btn-success w-100">
                                            <i class="bi bi-check-circle me-1"></i> Mark as Clean
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">No active cleaning</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Column 3: Clean (Awaiting Inspection) -->
            <div class="col-md-3">
                <div class="card h-100 border-top border-3 border-primary">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-primary"><i class="bi bi-sparkles me-1"></i> Clean</span>
                        <span class="badge bg-primary">{{ $rooms->where('status', 'clean')->count() }}</span>
                    </div>
                    <div class="card-body p-2" style="min-height: 400px; background: rgba(16,185,129,0.03);">
                        @forelse($rooms->where('status', 'clean') as $rm)
                            <div class="card mb-2 shadow-sm border-0">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0">Room {{ $rm->room_number }}</h6>
                                        <span class="badge bg-primary">{{ $rm->roomType?->name }}</span>
                                    </div>
                                    <p class="small text-muted mb-2">Cleaned • Awaiting inspector</p>
                                    <form method="POST" action="{{ route('admin.housekeeping.sign-off', $rm) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success w-100">
                                            <i class="bi bi-patch-check me-1"></i> Inspector Sign-Off
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">No uninspected clean rooms</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Column 4: Inspected & Ready -->
            <div class="col-md-3">
                <div class="card h-100 border-top border-3 border-success">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-success"><i class="bi bi-check-all me-1"></i> Inspected</span>
                        <span class="badge bg-success">{{ $rooms->where('status', 'inspected')->count() }}</span>
                    </div>
                    <div class="card-body p-2" style="min-height: 400px; background: rgba(16,185,129,0.06);">
                        @forelse($rooms->where('status', 'inspected') as $rm)
                            <div class="card mb-2 shadow-sm border-0">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="fw-bold mb-0">Room {{ $rm->room_number }}</h6>
                                        <span class="badge bg-success"><i class="bi bi-shield-check"></i> Ready</span>
                                    </div>
                                    <p class="small text-muted mb-0">{{ $rm->roomType?->name }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">No inspected rooms</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Cleaning Tasks Tab -->
    <div class="tab-pane fade" id="tasks" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h6 class="fw-bold mb-0"><i class="bi bi-list-check me-2 text-primary"></i>Today's Cleaning Queue</h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Room #</th>
                            <th>Type</th>
                            <th>Priority</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $tsk)
                            <tr>
                                <td class="fw-bold"><code>Room {{ $tsk->room?->room_number }}</code></td>
                                <td><span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $tsk->type)) }}</span></td>
                                <td>
                                    <span class="badge bg-{{ $tsk->priority === 'high' || $tsk->priority === 'urgent' ? 'danger' : 'secondary' }}">
                                        {{ ucfirst($tsk->priority) }}
                                    </span>
                                </td>
                                <td>
                                    @if($tsk->assignedTo)
                                        <i class="bi bi-person-fill text-primary me-1"></i>{{ $tsk->assignedTo->name }}
                                    @else
                                        <form method="POST" action="{{ route('admin.housekeeping.task.assign', $tsk) }}" class="d-flex gap-1">
                                            @csrf
                                            <select name="assigned_to" class="form-select form-select-sm" required style="width: 140px;">
                                                <option value="">Assign Staff...</option>
                                                @foreach($staffMembers as $stf)
                                                    <option value="{{ $stf->id }}">{{ $stf->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Assign</button>
                                        </form>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge-status {{ $tsk->status }}">{{ ucfirst(str_replace('_', ' ', $tsk->status)) }}</span>
                                </td>
                                <td>
                                    @if($tsk->status !== 'completed')
                                        <form method="POST" action="{{ route('admin.housekeeping.task.complete', $tsk) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-lg me-1"></i> Complete
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-success small"><i class="bi bi-check-circle-fill me-1"></i> Done</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No cleaning tasks scheduled for today.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 3. Maintenance Requests Tab -->
    <div class="tab-pane fade" id="maintenance" role="tabpanel">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-wrench me-2 text-primary"></i>Maintenance Log</h6>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#logMaintenanceModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Request
                </button>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Room / Area</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Description</th>
                            <th>Reported By</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($maintenanceRequests as $req)
                            <tr>
                                <td class="fw-bold">
                                    {{ $req->room ? 'Room ' . $req->room->room_number : 'General Facility' }}
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ ucfirst($req->category) }}</span></td>
                                <td>
                                    <span class="badge bg-{{ $req->priority === 'urgent' ? 'danger' : ($req->priority === 'high' ? 'warning' : 'info') }}">
                                        {{ ucfirst($req->priority) }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($req->description, 40) }}</td>
                                <td>{{ $req->reportedBy?->name ?: 'Staff' }}</td>
                                <td><span class="badge-status {{ $req->status }}">{{ ucfirst($req->status) }}</span></td>
                                <td>
                                    @if($req->status !== 'completed')
                                        <form method="POST" action="{{ route('admin.housekeeping.maintenance.update', $req) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="completed">
                                            <input type="hidden" name="resolution_notes" value="Resolved by staff">
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-check2 me-1"></i> Resolve
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">Resolved</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No maintenance requests logged.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Log Maintenance Request -->
<div class="modal fade" id="logMaintenanceModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.housekeeping.maintenance.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-tools me-2 text-primary"></i>Log Maintenance Issue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Room (Optional)</label>
                    <select name="room_id" class="form-select">
                        <option value="">-- General Facility / Common Area --</option>
                        @foreach($rooms as $rm)
                            <option value="{{ $rm->id }}">Room {{ $rm->room_number }} ({{ $rm->roomType?->name }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="plumbing">Plumbing</option>
                            <option value="electrical">Electrical</option>
                            <option value="hvac">HVAC / AC</option>
                            <option value="furniture">Furniture & Fixtures</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select" required>
                            <option value="normal">Normal</option>
                            <option value="high">High (Auto OOO)</option>
                            <option value="urgent">Urgent (Auto OOO)</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Issue Description</label>
                    <textarea name="description" class="form-control" rows="3" required placeholder="Describe the maintenance issue..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Request</button>
            </div>
        </form>
    </div>
</div>

@endsection
