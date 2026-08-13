@extends('layouts.app')

@section('title', 'Tape Chart')
@section('page-title', 'Interactive Tape Chart')
@section('breadcrumb', 'Reservations › Tape Chart')

@section('content')

<div class="page-header">
    <div>
        <h1>Tape Chart</h1>
        <p>Visual room occupancy timeline for {{ $property?->name ?: 'All Properties' }}</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary"><i class="bi bi-chevron-left"></i> Previous Week</button>
        <button class="btn btn-outline-primary">Today</button>
        <button class="btn btn-outline-secondary">Next Week <i class="bi bi-chevron-right"></i></button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-calendar3 me-2 text-primary"></i>
        <h6>Grid Timeline View</h6>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th class="text-start" style="width: 140px;">Room</th>
                        @for($i = 0; $i < 10; $i++)
                        @php $d = now()->addDays($i); @endphp
                        <th style="min-width: 75px; font-size: 0.75rem;">
                            <div>{{ $d->format('D') }}</div>
                            <div class="fw-bold">{{ $d->format('M d') }}</div>
                        </th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @php
                        $rooms = $property ? \App\Infrastructure\Persistence\Room::where('property_id', $property->id)->with('roomType')->get() : collect();
                    @endphp
                    @forelse($rooms as $rm)
                    <tr>
                        <td class="text-start">
                            <span class="fw-bold"><code>{{ $rm->room_number }}</code></span>
                            <div class="text-secondary" style="font-size: 0.68rem;">{{ $rm->roomType?->code }}</div>
                        </td>
                        @for($i = 0; $i < 10; $i++)
                        <td style="background: rgba(16,185,129,0.05); font-size: 0.7rem;" class="text-success">
                            Vacant
                        </td>
                        @endfor
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-4 text-secondary">
                            No physical rooms configured yet. <a href="{{ route('admin.rooms.create') }}">Add rooms</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
