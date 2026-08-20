@extends('layouts.app')

@section('title', 'POS Terminal — ' . $property->name)

@push('styles')
<style>
    /* Native Dashboard POS Theme Styling */
    .pos-item-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 18px;
        cursor: pointer;
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        box-shadow: var(--shadow);
    }

    .pos-item-card:hover {
        transform: translateY(-2px);
        border-color: #151b16;
        box-shadow: var(--shadow-lg);
    }

    .pos-item-card:active {
        transform: scale(0.98);
    }

    .pos-icon-tile {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-sm);
        background: #eefb98;
        color: #151b16;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        font-weight: 800;
        margin-bottom: 12px;
    }

    .pos-badge-tag {
        position: absolute;
        top: 14px;
        right: 14px;
        font-size: 0.68rem;
        font-weight: 800;
        padding: 3px 8px;
        border-radius: var(--radius-pill);
        background: #d6f843;
        color: #151b16;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .pos-price-text {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--text-primary);
        letter-spacing: -0.3px;
    }

    .pos-add-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--sidebar-accent);
        color: #151b16;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        box-shadow: 0 4px 10px rgba(214, 248, 67, 0.4);
        transition: transform 0.2s;
    }

    .pos-item-card:hover .pos-add-circle {
        transform: scale(1.1);
        background: #ccf235;
    }

    /* Cart Panel & Controls */
    .pos-cart-card {
        background: #ffffff;
        border-radius: var(--radius);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-lg);
        padding: 24px;
    }

    .pos-segmented-type {
        display: flex;
        background: #f3f6f3;
        padding: 4px;
        border-radius: var(--radius-pill);
        gap: 4px;
    }

    .pos-seg-btn {
        flex: 1;
        border: none;
        background: transparent;
        color: var(--text-secondary);
        padding: 8px 12px;
        border-radius: var(--radius-pill);
        font-size: 0.82rem;
        font-weight: 700;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .pos-seg-btn.active {
        background: #ffffff;
        color: var(--text-primary);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .cart-line-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid var(--border-color);
    }

    .qty-stepper-pill {
        display: inline-flex;
        align-items: center;
        background: #f3f6f3;
        border-radius: var(--radius-pill);
        padding: 2px 6px;
        gap: 6px;
    }

    .stepper-btn {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: none;
        background: #ffffff;
        color: var(--text-primary);
        font-weight: 800;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .stepper-btn:hover {
        background: #151b16;
        color: #ffffff;
    }

    .cat-tab-btn {
        border-radius: var(--radius-pill);
        font-weight: 700;
        font-size: 0.82rem;
        padding: 7px 16px;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1>Point of Sale (POS) Terminal</h1>
        <p>Manage outlet sales, restaurant tickets, bar orders, and guest room charging</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#receiptPreviewModal">
            <i class="bi bi-printer me-1"></i>Thermal Receipt
        </button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="clearCart()">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Order
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <!-- Left Column: Menu Item Catalog & Category Filters -->
    <div class="col-lg-7">
        <!-- Search & Filter Controls -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div class="topbar-search m-0" style="max-width: 280px;">
                <i class="bi bi-search"></i>
                <input type="text" id="menuSearchInput" onkeyup="filterMenuItems()" placeholder="Search dish, drink, or item...">
            </div>

            <div class="d-flex align-items-center gap-2 overflow-auto py-1">
                <button type="button" class="btn btn-primary cat-tab-btn cat-filter-btn" onclick="filterCategory('all', this)"><i class="bi bi-grid-fill me-1"></i>All</button>
                <button type="button" class="btn btn-secondary cat-tab-btn cat-filter-btn" onclick="filterCategory('mains', this)"><i class="bi bi-pie-chart-fill me-1"></i>Mains</button>
                <button type="button" class="btn btn-secondary cat-tab-btn cat-filter-btn" onclick="filterCategory('beverages', this)"><i class="bi bi-cup-straw me-1"></i>Drinks</button>
                <button type="button" class="btn btn-secondary cat-tab-btn cat-filter-btn" onclick="filterCategory('desserts', this)"><i class="bi bi-egg-fried me-1"></i>Desserts</button>
                <button type="button" class="btn btn-secondary cat-tab-btn cat-filter-btn" onclick="filterCategory('services', this)"><i class="bi bi-heart-pulse me-1"></i>Services</button>
            </div>
        </div>

        <!-- Menu Item Cards Grid -->
        <div class="row g-3" id="menuGrid">
            @forelse($menuItems as $item)
                <div class="col-md-4 col-sm-6 menu-item-col" data-category="{{ strtolower($item->category) }}" data-name="{{ strtolower($item->name) }}">
                    <div class="pos-item-card" onclick="addToCart('{{ $item->name }}', {{ $item->price_minor }}, '{{ $item->category }}')">
                        @if(isset($item->badge))
                            <span class="pos-badge-tag">{{ $item->badge }}</span>
                        @endif
                        
                        <div>
                            <div class="pos-icon-tile">
                                <i class="bi {{ $item->icon ?? 'bi-shop' }}"></i>
                            </div>
                            <h6 class="fw-extrabold text-dark mb-1" style="font-size: 0.92rem;">{{ $item->name }}</h6>
                            <div class="text-muted small text-capitalize">{{ $item->category }}</div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top">
                            <span class="pos-price-text">{{ $property->currency }} {{ number_format($item->price_minor / 100, 2) }}</span>
                            <div class="pos-add-circle">
                                <i class="bi bi-plus-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 text-muted">
                    <i class="bi bi-basket fs-1 d-block mb-2"></i>
                    <p>No menu items registered for this outlet.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Right Column: Live Order Cart & Multi-Tender Checkout -->
    <div class="col-lg-5">
        <div class="pos-cart-card">
            <form action="{{ route('admin.pos.orders.store') }}" method="POST" id="posOrderForm">
                @csrf
                <input type="hidden" name="pos_outlet_id" value="{{ $outlets->first()->id ?? 1 }}">
                <input type="hidden" name="order_type" id="order_type_input" value="dine_in">
                <input type="hidden" name="amount" id="total_amount_input" value="0.00">
                <input type="hidden" name="item_name" id="item_name_input" value="POS Order Ticket">

                <!-- Order Type Segmented Switcher -->
                <div class="pos-segmented-type mb-3">
                    <button type="button" class="pos-seg-btn active" onclick="setOrderType('dine_in', this)">
                        <i class="bi bi-shop"></i>Dine-In
                    </button>
                    <button type="button" class="pos-seg-btn" onclick="setOrderType('takeaway', this)">
                        <i class="bi bi-bag"></i>Takeaway
                    </button>
                    <button type="button" class="pos-seg-btn" onclick="setOrderType('room_charge', this)">
                        <i class="bi bi-key"></i>Room Charge
                    </button>
                </div>

                <!-- Room Charge In-House Guest Dropdown -->
                <div id="roomChargeSelectWrapper" class="mb-3" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label text-primary small fw-bold mb-0"><i class="bi bi-house-door me-1"></i>Select In-House Guest / Room</label>
                        <span class="text-muted small" style="font-size: 0.72rem;">Required for Folio Posting</span>
                    </div>
                    <select name="reservation_id" id="reservation_id_select" class="form-select border-primary" style="transition: all 0.2s;">
                        <option value="">-- Choose Active In-House Guest & Room --</option>
                        @foreach($activeStays as $stay)
                            <option value="{{ $stay->id }}">
                                Room {{ $stay->rooms->first()->room_number ?? 'Suite' }} &bull; {{ $stay->primaryGuest->first_name }} {{ $stay->primaryGuest->last_name }} ({{ $stay->confirmation_number }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Table & Covers Inputs -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <input type="text" name="table_number" class="form-control form-control-sm" value="Table 04" placeholder="Table No.">
                    </div>
                    <div class="col-6">
                        <input type="number" name="covers" class="form-control form-control-sm" value="2" min="1" placeholder="Guests">
                    </div>
                </div>

                <!-- Cart Line Items -->
                <div class="flex-grow-1 overflow-auto pe-1 mb-3" style="max-height: 240px; min-height: 180px;">
                    <div id="cartEmptyState" class="text-center py-5 text-muted">
                        <i class="bi bi-cart3 fs-1 d-block mb-2 text-secondary"></i>
                        <div class="small fw-semibold">Your cart is empty. Tap menu items to build ticket.</div>
                    </div>

                    <div id="cartItemsList"></div>
                </div>

                <!-- Financial Calculation Summary -->
                <div class="p-3 rounded-4 mb-3" style="background: #fafcfb; border: 1px solid var(--border-color);">
                    <div class="d-flex justify-content-between text-muted small mb-1">
                        <span>Subtotal</span>
                        <span id="summarySubtotal">{{ $property->currency }} 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted small mb-1">
                        <span>Kenya VAT (16%)</span>
                        <span id="summaryVat">{{ $property->currency }} 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted small mb-2">
                        <span>Tourism Levy (2%)</span>
                        <span id="summaryLevy">{{ $property->currency }} 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <span class="fw-extrabold text-dark">Grand Total</span>
                        <span class="fs-3 fw-extrabold text-dark" id="summaryTotal">{{ $property->currency }} 0.00</span>
                    </div>
                </div>

                <!-- Multi-Tender Checkout Action Buttons -->
                <div class="row g-2">
                    <div class="col-6">
                        <button type="button" class="btn btn-success w-100 py-2 rounded-pill fw-bold small" data-bs-toggle="modal" data-bs-target="#mpesaStkModal">
                            <i class="bi bi-phone me-1"></i>M-Pesa STK Push
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-primary w-100 py-2 rounded-pill fw-bold small" onclick="submitRoomCharge()">
                            <i class="bi bi-key me-1"></i>Charge Room
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-outline-primary w-100 py-2 rounded-pill fw-bold small" onclick="submitTender('card')">
                            <i class="bi bi-credit-card me-1"></i>Card Payment
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-secondary w-100 py-2 rounded-pill fw-bold small" onclick="submitTender('cash')">
                            <i class="bi bi-cash-stack me-1"></i>Cash Tender
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- M-Pesa STK Push Modal -->
<div class="modal fade" id="mpesaStkModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-success"><i class="bi bi-phone me-2"></i>M-Pesa Express STK Push</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Enter customer phone number below. An instant M-Pesa PIN prompt will be sent to the customer's phone.</p>
                <div class="mb-3">
                    <label class="form-label">Customer Phone Number</label>
                    <input type="text" id="mpesaPhone" class="form-control" value="254712345678">
                </div>
                <div class="p-3 bg-light rounded-3 text-center mb-3">
                    <div class="text-muted small">Amount to Charge</div>
                    <div class="fs-2 fw-bold text-success" id="mpesaModalAmount">{{ $property->currency }} 0.00</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success rounded-pill px-4 fw-bold" onclick="triggerMpesaPush()">
                    <i class="bi bi-send me-1"></i>Send STK Push Prompt
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Thermal Receipt Preview Modal -->
<div class="modal fade" id="receiptPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-printer me-2"></i>Thermal Receipt Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="card p-4 border" id="receiptPrintArea" style="font-family: 'Courier New', Courier, monospace; background: #ffffff;">
                    <div class="text-center fw-bold fs-5 mb-1">{{ strtoupper($property->name) }}</div>
                    <div class="text-center small text-muted">Main Restaurant & Bar Outlet</div>
                    <div class="text-center small">Tel: +254 700 000 000 &bull; PIN: P051234567Z</div>
                    <div class="border-top border-dark border-dashed my-2"></div>
                    <div class="d-flex justify-content-between small">
                        <span>Date: {{ now()->format('d/m/Y H:i') }}</span>
                        <span>Server: {{ auth()->user()->name }}</span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span>Ticket #: POS-{{ rand(1000, 9999) }}</span>
                        <span>Table: T-04</span>
                    </div>
                    <div class="border-top border-dark border-dashed my-2"></div>
                    <div id="receiptItemsContainer"></div>
                    <div class="border-top border-dark border-dashed my-2"></div>
                    <div class="d-flex justify-content-between fw-bold fs-6">
                        <span>TOTAL PAYABLE</span>
                        <span id="receiptTotalVal">{{ $property->currency }} 0.00</span>
                    </div>
                    <div class="border-top border-dark border-dashed my-2"></div>
                    <div class="text-center small mt-2">KRA ETIMS QR SIGNATURE ATTACHED</div>
                    <div class="text-center mt-2">
                        <i class="bi bi-qr-code fs-1"></i>
                    </div>
                    <div class="text-center small text-muted mt-2">Asante Sana! Thank you for dining with us!</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary rounded-pill fw-bold" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let cart = [];
    const currency = "{{ $property->currency }}";

    function addToCart(name, priceMinor, category) {
        const existing = cart.find(item => item.name === name);
        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({ name, priceMinor, qty: 1, category });
        }
        renderCart();
    }

    function updateQty(name, delta) {
        const item = cart.find(i => i.name === name);
        if (item) {
            item.qty += delta;
            if (item.qty <= 0) {
                cart = cart.filter(i => i.name !== name);
            }
        }
        renderCart();
    }

    function removeFromCart(name) {
        cart = cart.filter(i => i.name !== name);
        renderCart();
    }

    function clearCart() {
        cart = [];
        renderCart();
    }

    function renderCart() {
        const emptyState = document.getElementById('cartEmptyState');
        const list = document.getElementById('cartItemsList');
        const receiptList = document.getElementById('receiptItemsContainer');

        if (cart.length === 0) {
            emptyState.style.display = 'block';
            list.innerHTML = '';
            receiptList.innerHTML = '<div class="text-center text-muted small">No items in order</div>';
            updateSummary(0);
            return;
        }

        emptyState.style.display = 'none';
        let html = '';
        let receiptHtml = '';
        let totalMinor = 0;

        cart.forEach(item => {
            const itemTotal = (item.priceMinor * item.qty) / 100;
            totalMinor += (item.priceMinor * item.qty);

            html += `
                <div class="cart-line-row">
                    <div>
                        <div class="fw-bold text-dark small">${item.name}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">${currency} ${(item.priceMinor/100).toFixed(2)} each</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="qty-stepper-pill">
                            <button type="button" class="stepper-btn" onclick="updateQty('${item.name}', -1)">-</button>
                            <span class="fw-bold text-dark small px-1">${item.qty}</span>
                            <button type="button" class="stepper-btn" onclick="updateQty('${item.name}', 1)">+</button>
                        </div>
                        <span class="fw-bold text-dark small ms-2">${currency} ${itemTotal.toFixed(2)}</span>
                        <i class="bi bi-x-circle text-muted hover-danger ms-2" style="cursor: pointer;" onclick="removeFromCart('${item.name}')"></i>
                    </div>
                </div>
            `;

            receiptHtml += `
                <div class="d-flex justify-content-between small">
                    <span>${item.qty}x ${item.name}</span>
                    <span>${currency} ${itemTotal.toFixed(2)}</span>
                </div>
            `;
        });

        list.innerHTML = html;
        receiptList.innerHTML = receiptHtml;
        updateSummary(totalMinor);
    }

    function updateSummary(totalMinor) {
        const total = totalMinor / 100;
        const subtotal = total / 1.18;
        const vat = subtotal * 0.16;
        const levy = subtotal * 0.02;

        document.getElementById('summarySubtotal').innerText = `${currency} ${subtotal.toFixed(2)}`;
        document.getElementById('summaryVat').innerText = `${currency} ${vat.toFixed(2)}`;
        document.getElementById('summaryLevy').innerText = `${currency} ${levy.toFixed(2)}`;
        document.getElementById('summaryTotal').innerText = `${currency} ${total.toFixed(2)}`;
        document.getElementById('receiptTotalVal').innerText = `${currency} ${total.toFixed(2)}`;
        document.getElementById('mpesaModalAmount').innerText = `${currency} ${total.toFixed(2)}`;

        document.getElementById('total_amount_input').value = total.toFixed(2);
        
        if (cart.length > 0) {
            document.getElementById('item_name_input').value = cart.map(i => `${i.qty}x ${i.name}`).join(', ');
        }
    }

    function setOrderType(type, btn) {
        document.getElementById('order_type_input').value = type;
        document.querySelectorAll('.pos-seg-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');

        const wrapper = document.getElementById('roomChargeSelectWrapper');
        wrapper.style.display = (type === 'room_charge') ? 'block' : 'none';
    }

    function filterCategory(cat, el) {
        document.querySelectorAll('.cat-filter-btn').forEach(p => {
            p.classList.remove('btn-primary');
            p.classList.add('btn-secondary');
        });
        el.classList.remove('btn-secondary');
        el.classList.add('btn-primary');

        document.querySelectorAll('.menu-item-col').forEach(col => {
            if (cat === 'all' || col.getAttribute('data-category') === cat) {
                col.style.display = 'block';
            } else {
                col.style.display = 'none';
            }
        });
    }

    function filterMenuItems() {
        const query = document.getElementById('menuSearchInput').value.toLowerCase();
        document.querySelectorAll('.menu-item-col').forEach(col => {
            const name = col.getAttribute('data-name');
            if (name.includes(query)) {
                col.style.display = 'block';
            } else {
                col.style.display = 'none';
            }
        });
    }

    function submitRoomCharge() {
        if (cart.length === 0) {
            alert('Please add items to cart first.');
            return;
        }

        // Switch to Room Charge mode and reveal dropdown
        setOrderType('room_charge', document.querySelectorAll('.pos-seg-btn')[2]);

        const select = document.getElementById('reservation_id_select');
        if (!select || !select.value) {
            select.focus();
            select.style.borderColor = '#151b16';
            select.style.boxShadow = '0 0 0 4px rgba(214, 248, 67, 0.5)';
            alert('Please select an active in-house guest room from the dropdown to post to folio.');
            return;
        }

        document.getElementById('posOrderForm').submit();
    }

    function submitTender(method) {
        if (cart.length === 0) {
            alert('Please add items to cart first.');
            return;
        }

        const totalVal = document.getElementById('total_amount_input').value;

        if (method === 'card') {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            fetch('{{ route("admin.pos.stripe-charge") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ amount: totalVal })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(`Stripe Card Payment Authorized!\nPaymentIntent ID: ${data.payment_intent_id}\nStatus: ${data.status}`);
                    document.getElementById('posOrderForm').submit();
                } else {
                    alert('Stripe authorization failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                alert('Stripe charge error: ' + err.message);
            });
            return;
        }

        document.getElementById('posOrderForm').submit();
    }

    function triggerMpesaPush() {
        const phone = document.getElementById('mpesaPhone').value;
        const totalVal = document.getElementById('total_amount_input').value;

        if (!phone) {
            alert('Please enter M-Pesa phone number.');
            return;
        }

        if (cart.length === 0) {
            alert('Please add items to cart first.');
            return;
        }

        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        fetch('{{ route("admin.pos.stk-push") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ phone: phone, amount: totalVal })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(`Safaricom M-Pesa Daraja Response:\n${data.customer_message}\nCheckout Request ID: ${data.checkout_request_id}`);
                const modal = bootstrap.Modal.getInstance(document.getElementById('mpesaStkModal'));
                if (modal) modal.hide();
                document.getElementById('posOrderForm').submit();
            } else {
                alert('M-Pesa STK Push error: ' + (data.message || 'Failed'));
            }
        })
        .catch(err => {
            alert('M-Pesa API error: ' + err.message);
        });
    }
</script>
@endpush
@endsection
