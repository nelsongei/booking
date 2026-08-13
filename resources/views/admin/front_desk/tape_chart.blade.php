@extends('layouts.app')

@section('title', 'Tape Chart')
@section('page-title', 'Interactive 14-Day Tape Chart')
@section('breadcrumb', 'Front Desk › Tape Chart')

@section('content')

<div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1"><i class="bi bi-calendar3 text-primary me-2"></i>Tape Chart</h1>
        <p class="text-secondary small mb-0">Visual room occupancy matrix for <strong>{{ $property?->name ?: 'All Properties' }}</strong></p>
    </div>
    <div class="d-flex align-items-center gap-2 mt-3 mt-sm-0">
        <a href="{{ route('admin.tape-chart.index', ['start_date' => $startDate->copy()->subDays(14)->toDateString()]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-chevron-left me-1"></i> Prev 14 Days
        </a>
        <a href="{{ route('admin.tape-chart.index') }}" class="btn btn-primary btn-sm rounded-pill px-3">
            Today
        </a>
        <a href="{{ route('admin.tape-chart.index', ['start_date' => $startDate->copy()->addDays(14)->toDateString()]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            Next 14 Days <i class="bi bi-chevron-right ms-1"></i>
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-grid-3x3-gap text-primary me-2"></i>14-Day Timeline View</h6>
            <span class="badge bg-light text-dark border fw-normal">{{ $startDate->format('M d, Y') }} &rarr; {{ $startDate->copy()->addDays(13)->format('M d, Y') }}</span>
        </div>

        <!-- Legend -->
        <div class="d-flex align-items-center gap-3 small">
            <span class="d-flex align-items-center gap-1"><span style="width:12px;height:12px;border-radius:3px;" class="bg-warning"></span> Confirmed</span>
            <span class="d-flex align-items-center gap-1"><span style="width:12px;height:12px;border-radius:3px;" class="bg-success"></span> Checked-In</span>
            <span class="d-flex align-items-center gap-1"><span style="width:12px;height:12px;border-radius:3px;" class="bg-secondary"></span> Checked-Out</span>
            <span class="d-flex align-items-center gap-1"><span style="width:12px;height:12px;border-radius:3px;" class="bg-danger"></span> Out of Order</span>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 align-middle text-center" style="font-size: 0.82rem;">
                <thead class="table-light">
                    <tr>
                        <th class="text-start ps-3" style="width: 150px; min-width: 150px;">Physical Room</th>
                        @foreach($dates as $d)
                            <th style="min-width: 70px;" class="{{ $d->isToday() ? 'bg-primary text-white' : '' }}">
                                <div class="small fw-normal">{{ $d->format('D') }}</div>
                                <div class="fw-bold">{{ $d->format('M d') }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($rooms as $rm)
                        <tr>
                            <td class="text-start ps-3 bg-white">
                                <div class="fw-bold text-dark">Room <code>{{ $rm->room_number }}</code></div>
                                <div class="text-secondary small">{{ $rm->roomType?->code }} &bull; <span class="badge badge-sm {{ $rm->status === 'clean' ? 'bg-success' : ($rm->status === 'dirty' ? 'bg-warning text-dark' : 'bg-secondary') }}">{{ ucfirst($rm->status) }}</span></div>
                            </td>

                            @foreach($dates as $d)
                                @php
                                    $dateStr = $d->toDateString();
                                    $cell = $matrix[$rm->id][$dateStr] ?? null;
                                @endphp

                                @if($cell && !empty($cell['reservation']))
                                    @php
                                        $res = $cell['reservation'];
                                        $guest = $cell['guest'];
                                        $barColor = match($cell['status']) {
                                            'checked_in'  => 'bg-success text-white',
                                            'checked_out' => 'bg-secondary text-white',
                                            'confirmed'   => 'bg-warning text-dark',
                                            'held'        => 'bg-info text-white',
                                            default       => 'bg-warning text-dark'
                                        };
                                        $guestName = $guest ? ($guest->first_name . ' ' . $guest->last_name) : $cell['confirmation'];
                                    @endphp
                                    <td class="{{ $barColor }} fw-semibold text-truncate p-2" style="max-width: 70px; cursor: pointer;" title="{{ $cell['confirmation'] }} — {{ $guestName }} ({{ ucfirst($cell['status']) }})">
                                        <a href="{{ route('admin.reservations.show', $res) }}" class="text-reset text-decoration-none d-block fw-bold">
                                            {{ Str::limit($guest?->last_name ?: $cell['confirmation'], 8) }}
                                        </a>
                                    </td>
                                @else
                                    <td class="bg-light opacity-50 text-muted">
                                        &middot;
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="py-5 text-center text-muted">
                                <i class="bi bi-building fs-1 d-block mb-2 text-secondary"></i>
                                No physical rooms configured yet for this property. <a href="{{ route('admin.rooms.create') }}">Add Rooms</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
