# Multi-Property Hotel PMS & Booking Platform
## Operational Production Runbook & Pilot Deployment Blueprint

**Document Version:** 1.0  
**Target Environment:** Laravel Modular Monolith (PHP 8.1+, MySQL 8.0+, Bootstrap 5, Spatie Permissions)  
**Last Updated:** August 12, 2026  

---

## 1. Production Deployment Checklist

Before deploying updates or launching pilot properties:

### 1.1 Pre-Deployment Commands
Run the following Artisan commands during deployment:
```bash
# 1. Run database migrations
php artisan migrate --force

# 2. Clear & optimize configuration, route & view caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Perform production health check & DB latency audit
php artisan pms:health-check
```

### 1.2 Storage Permissions
Ensure the web server user (`www-data`) has read/write permissions for storage & bootstrap cache:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 2. System Health & Diagnostics

### 2.1 Web Diagnostics Dashboard
Staff administrators can inspect real-time system metrics by navigating to:  
`https://your-domain.com/admin/system/health`

**Monitored Parameters:**
- Database PDO Connection Latency (target < 50ms)
- Storage Write Permissions (`storage/app`, `storage/framework`, `storage/logs`)
- Disk Capacity & Memory Utilization
- Table Row Counts (`reservations`, `stays`, `folio_transactions`, `inventory_days`)
- HTTP Security Headers (`SecureHeadersMiddleware`)

### 2.2 CLI Diagnostics
Run terminal health check anytime:
```bash
php artisan pms:health-check
```

---

## 3. Night Audit Troubleshooting & Procedures

### 3.1 Overview
The Night Audit runbook (`/admin/night-audit`) advances the hotel's `business_date` and posts nightly room charges to in-house folios.

### 3.2 Pre-Audit Blocking Issues & Resolution
- **Pending Arrivals > 0**: Ensure Front Desk staff checks in expected guests or marks them as `no_show`.
- **Pending Departures > 0**: Ensure departures are checked out or extended.
- **Open Cashier Shifts > 0**: Ensure cashiers close their active shifts under `/admin/cashier-shifts`.

### 3.3 Audit Failure Recovery
If a step fails during audit execution:
1. Review `night_audits.failure_notes` in the database or UI.
2. Resolve underlying issue (e.g. database lock timeout or missing charge code).
3. Click **Reset Failed Steps** in `/admin/night-audit` to resume audit from the failed step.

---

## 4. Channel Manager & Webhook Dead-Letter Queue Triage

### 4.1 Inbound Webhook Endpoint
OTAs send real-time reservation notifications to:  
`POST /api/v1/webhooks/{provider}` (e.g., `booking_com`, `airbnb`, `expedia`).

### 4.2 Handling Webhook Failures
If an inbound payload fails parsing or processing:
1. It is automatically routed to the **Dead-Letter Queue** (`dead_letter_items`).
2. Navigate to `/admin/dead-letter-queue`.
3. Click **Payload** to inspect incoming JSON.
4. Click **Replay** to re-attempt processing after resolving data issues.
5. Click **Discard** if the event is invalid or orphaned.

---

## 5. Pilot Property Onboarding Guide

To onboard a new hotel property:
1. Navigate to `/admin/properties` and click **Add New Property**.
2. Configure Room Types (`/admin/room-types`) and Physical Rooms (`/admin/rooms`).
3. Set up Rate Plans (`/admin/rate-plans`) and 14-day daily rate matrix.
4. Enable Public Booking Engine under `/booking/{property-slug}`.
