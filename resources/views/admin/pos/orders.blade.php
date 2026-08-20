@extends('layouts.app')

@section('title', 'POS Orders History — ' . $property->name)

@section('content')
<div class="page-header">
    <div>
        <h1>POS Sales Orders Roster</h1>
        <p>Audit and inspect all F&B, bar, and spa outlet orders, payments, and room folio postings</p>
    </div>
    <div>
        <a href="{{ route('admin.pos.terminal') }}" class="btn btn-primary rounded-pill fw-bold">
            <i class="bi bi-display me-1"></i>Touch POS Terminal
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('admin.pos.orders.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Payment Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">-- All Payment Statuses --</option>
                    <option value="posted_to_room" {{ request('status') === 'posted_to_room' ? 'selected' : '' }}>Posted to Room Folio</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid (Cash / Card / Mobile Money)</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid / Pending</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Order Type</label>
                <select name="order_type" class="form-select" onchange="this.form.submit()">
                    <option value="">-- All Order Types --</option>
                    <option value="dine_in" {{ request('order_type') === 'dine_in' ? 'selected' : '' }}>Dine-In</option>
                    <option value="takeaway" {{ request('order_type') === 'takeaway' ? 'selected' : '' }}>Takeaway</option>
                    <option value="room_charge" {{ request('order_type') === 'room_charge' ? 'selected' : '' }}>Room Charge</option>
                </select>
            </div>
            <div class="col-md-4 pt-4">
                <a href="{{ route('admin.pos.orders.index') }}" class="btn btn-secondary rounded-pill w-100 fw-bold">Reset Filters</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="m-0 font-weight-bold"><i class="bi bi-receipt-cutoff me-2 text-primary"></i>Placed POS Order Tickets</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr>
                        <th>Order ULID</th>
                        <th>Outlet</th>
                        <th>Table / Location</th>
                        <th>Order Type</th>
                        <th>Payment Tender / Link</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Timestamp</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><code>{{ substr($order->ulid, -8) }}</code></td>
                            <td class="fw-bold">{{ $order->outlet->name ?? 'POS Outlet' }}</td>
                            <td><span class="badge bg-light text-dark">{{ $order->table_number ?? 'Counter' }}</span></td>
                            <td><span class="badge bg-light text-dark text-capitalize">{{ str_replace('_', ' ', $order->order_type) }}</span></td>
                            <td>
                                @if($order->payment_status === 'posted_to_room' && $order->reservation)
                                    <span class="badge bg-primary">
                                        <i class="bi bi-key me-1"></i>Billed to Room {{ $order->reservation->rooms->first()->room_number ?? 'Suite' }} ({{ $order->reservation->primaryGuest->last_name ?? 'Guest' }})
                                    </span>
                                @elseif($order->payment_status === 'posted_to_room')
                                    <span class="badge bg-primary"><i class="bi bi-key me-1"></i>Posted to Folio</span>
                                @else
                                    <span class="badge bg-success">Paid Direct</span>
                                @endif
                            </td>
                            <td class="text-end fw-extrabold text-dark">{{ $property->currency }} {{ number_format($order->total_minor / 100, 2) }}</td>
                            <td class="text-end text-muted small">{{ $order->created_at->format('d M Y, H:i') }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" 
                                        onclick="showOrderReceipt('{{ substr($order->ulid, -8) }}', '{{ e($order->outlet->name ?? 'Main Restaurant') }}', '{{ e($order->table_number ?? 'Counter') }}', '{{ $property->currency }}', '{{ number_format($order->total_minor / 100, 2) }}', '{{ $order->created_at->format('d/m/Y H:i') }}', '{{ e(str_replace('_', ' ', $order->order_type)) }}')">
                                    <i class="bi bi-printer me-1"></i>Print Receipt
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No POS orders match the filter criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $orders->links() }}
        </div>
    </div>
</div>

<!-- Modal: Thermal Receipt Modal -->
<div class="modal fade" id="orderReceiptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-printer me-2 text-primary"></i>80mm Thermal Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="card p-4 border" id="receiptPrintArea" style="font-family: 'Courier New', Courier, monospace; background: #ffffff;">
                    <div class="text-center fw-bold fs-5 mb-1">{{ strtoupper($property->name) }}</div>
                    <div class="text-center small text-muted" id="rcptOutlet">Main Outlet</div>
                    <div class="text-center small">Tel: +254 700 000 000 &bull; PIN: P051234567Z</div>
                    <div class="border-top border-dark border-dashed my-2"></div>
                    <div class="d-flex justify-content-between small">
                        <span>Date: <span id="rcptDate">19/08/2026</span></span>
                        <span>Server: {{ auth()->user()->name }}</span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span>Ticket #: POS-<span id="rcptUlid">1234</span></span>
                        <span>Table: <span id="rcptTable">T-01</span></span>
                    </div>
                    <div class="border-top border-dark border-dashed my-2"></div>
                    <div class="d-flex justify-content-between small fw-bold">
                        <span>1x F&B Order Items</span>
                        <span id="rcptSubtotal">0.00</span>
                    </div>
                    <div class="border-top border-dark border-dashed my-2"></div>
                    <div class="d-flex justify-content-between fw-bold fs-6">
                        <span>TOTAL PAID</span>
                        <span id="rcptTotal">USD 0.00</span>
                    </div>
                    <div class="border-top border-dark border-dashed my-2"></div>
                    <div class="text-center small mt-2">KRA ETIMS FISCAL QR SIGNATURE</div>
                    <div class="text-center mt-2">
                        <i class="bi bi-qr-code fs-1"></i>
                    </div>
                    <div class="text-center small text-muted mt-2">Asante Sana! Thank you for dining with us!</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>Print Receipt Now
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showOrderReceipt(ulid, outlet, table, currency, amount, date, type) {
        document.getElementById('rcptUlid').innerText = ulid;
        document.getElementById('rcptOutlet').innerText = outlet + ' (' + type + ')';
        document.getElementById('rcptTable').innerText = table;
        document.getElementById('rcptDate').innerText = date;
        document.getElementById('rcptSubtotal').innerText = currency + ' ' + amount;
        document.getElementById('rcptTotal').innerText = currency + ' ' + amount;

        const modal = new bootstrap.Modal(document.getElementById('orderReceiptModal'));
        modal.show();
    }
</script>
@endpush
@endsection
