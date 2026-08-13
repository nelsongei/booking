@extends('layouts.app')

@section('title', 'Loyalty Program')
@section('page-title', 'Guest Loyalty & Tier Membership Engine')
@section('breadcrumb', 'Scale › Guest Loyalty')

@section('content')

<div class="page-header flex-wrap gap-3">
    <div>
        <h1>Guest Loyalty Program</h1>
        <p>Tier membership (Bronze, Silver, Gold, Platinum), stay points accumulation & redemption ledger</p>
    </div>

    <div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#enrollGuestModal">
            <i class="bi bi-person-plus me-1"></i> Enroll Guest Member
        </button>
    </div>
</div>

<!-- Tier Overview Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="bi bi-gem"></i></div>
            <div>
                <div class="stat-number">{{ $loyaltyAccounts->where('tier', 'platinum')->count() }}</div>
                <div class="stat-label">Platinum Members (15k+ pts)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon amber"><i class="bi bi-star-fill"></i></div>
            <div>
                <div class="stat-number">{{ $loyaltyAccounts->where('tier', 'gold')->count() }}</div>
                <div class="stat-label">Gold Members (5k+ pts)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-award-fill"></i></div>
            <div>
                <div class="stat-number">{{ $loyaltyAccounts->where('tier', 'silver')->count() }}</div>
                <div class="stat-label">Silver Members (1k+ pts)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="stat-number">{{ $loyaltyAccounts->count() }}</div>
                <div class="stat-label">Total Enrolled Members</div>
            </div>
        </div>
    </div>
</div>

<!-- Loyalty Members Roster -->
<div class="card">
    <div class="card-header bg-light">
        <h6 class="fw-bold mb-0"><i class="bi bi-award me-2 text-primary"></i>Loyalty Membership Roster</h6>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Account #</th>
                    <th>Member Name</th>
                    <th>Email</th>
                    <th>Tier Status</th>
                    <th>Points Balance</th>
                    <th>Lifetime Points</th>
                    <th>Joined Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loyaltyAccounts as $acc)
                    <tr>
                        <td class="fw-bold"><code>{{ $acc->account_number }}</code></td>
                        <td class="fw-bold text-primary">{{ $acc->guestProfile?->fullName }}</td>
                        <td>{{ $acc->guestProfile?->email }}</td>
                        <td>
                            @if($acc->tier === 'platinum')
                                <span class="badge bg-purple text-white px-3 py-1"><i class="bi bi-gem me-1"></i> Platinum</span>
                            @elseif($acc->tier === 'gold')
                                <span class="badge bg-warning text-dark px-3 py-1"><i class="bi bi-star-fill me-1"></i> Gold</span>
                            @elseif($acc->tier === 'silver')
                                <span class="badge bg-info text-dark px-3 py-1"><i class="bi bi-award me-1"></i> Silver</span>
                            @else
                                <span class="badge bg-secondary px-3 py-1">Bronze</span>
                            @endif
                        </td>
                        <td class="fw-bold text-success fs-6">{{ number_format($acc->points_balance) }} pts</td>
                        <td class="text-muted">{{ number_format($acc->lifetime_points) }} pts</td>
                        <td class="small text-muted">{{ $acc->joined_at ? $acc->joined_at->format('M d, Y') : '—' }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#adjustModal_{{ $acc->id }}">
                                <i class="bi bi-plus-minus me-1"></i> Adjust Points
                            </button>
                        </td>
                    </tr>

                    <!-- Adjust Points Modal -->
                    <div class="modal fade" id="adjustModal_{{ $acc->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" action="{{ route('admin.loyalty.adjust', $acc) }}" class="modal-content">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-minus me-2 text-primary"></i>Adjust Points — {{ $acc->guestProfile?->fullName }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Transaction Type</label>
                                            <select name="type" class="form-select" required>
                                                <option value="earn">Earn Points (+)</option>
                                                <option value="redeem">Redeem Points (-)</option>
                                                <option value="adjustment">Manual Adjustment</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Points Quantity</label>
                                            <input type="number" name="points" class="form-control" required placeholder="500">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Reason / Description</label>
                                        <input type="text" name="description" class="form-control" required placeholder="VIP bonus / stay reward">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Submit Adjustment</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No guests enrolled in loyalty program.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Enroll Guest -->
<div class="modal fade" id="enrollGuestModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.loyalty.enroll') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-2 text-primary"></i>Enroll Guest in Loyalty</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Select Guest Profile</label>
                    <select name="guest_profile_id" class="form-select" required>
                        <option value="">-- Choose Guest Profile --</option>
                        @foreach($guestsWithoutAccount as $gst)
                            <option value="{{ $gst->id }}">{{ $gst->fullName }} ({{ $gst->email }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Enroll Member</button>
            </div>
        </form>
    </div>
</div>

@endsection
