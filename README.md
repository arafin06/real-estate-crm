# Real Estate Agent CRM — Laravel API

A production-ready SaaS backend for a real estate agent CRM platform. Built with Laravel 11, MySQL, and Stripe. Powers a Vue 3 SPA frontend with role-based access, deal pipeline management, subscription billing, and real-time analytics.

> **Portfolio project** demonstrating full-stack SaaS architecture with real-world U.S. real estate domain knowledge.

---

## Live Demo

The frontend is paired at: **[github.com/arafin06/real-estate-crm-frontend](https://github.com/arafin06/real-estate-crm-frontend)**

| Role | Email | Password |
|---|---|---|
| Super Admin | admin@recrm.demo | Demo1234! |
| Agent (Pro Plan) | sarah@recrm.demo | Demo1234! |
| Agent (Free Plan) | marcus@recrm.demo | Demo1234! |
| Client | client@recrm.demo | Demo1234! |

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 11 |
| Language | PHP 8.3 |
| Database | MySQL 8 |
| Authentication | Laravel Sanctum (token-based) |
| Payments | Stripe (Checkout, Billing Portal, Webhooks) |
| Queue | Laravel Queue (database driver) |
| File Storage | Laravel local storage with public symlink |
| Email | Laravel Mail (Mailtrap for dev) |

---

## Features

### Authentication & Authorization
- Token-based API auth via Laravel Sanctum
- Three roles: `super_admin`, `agent`, `client`
- Role middleware gates all protected routes
- Soft deletes on users

### Property Management
- Full CRUD for property listings
- Multiple image upload with primary image designation
- Filter by type, listing type, status, city, price range
- Paginated results (12 per page)

### Client / Contact Management
- Full CRUD for client profiles
- Buyer qualification fields: budget range, pre-approval amount, purchase timeline
- Polymorphic notes system — shared with deals and tasks
- Contact history timeline

### Deal Pipeline
- 7-stage pipeline: Lead → Prospect → Showing → Offer Made → Under Contract → Closed → Lost
- Commission auto-calculation from deal value and rate
- `closed_at` timestamp set on stage transition (accurate commission reporting)
- Activity log: every stage change recorded with timestamp and user
- Lost reason tracking
- Kanban board data endpoint (grouped by stage)

### Task & Follow-up System
- Four statuses: Incomplete / In Progress / Complete / Closed
- Priority levels: Urgent / High / Medium / Low
- Recurring tasks: daily, weekly (with day picker), biweekly, monthly, yearly
- Occurrence timeline: each completion logged with optional note, next due date auto-advances
- Subtasks with self-referential parent relationship
- Polymorphic notes on tasks
- Grouped endpoint: overdue / today / tomorrow / this week / later / complete / closed

### Dashboard & Analytics
- Summary: active listings, open deals, pipeline value, commission YTD, conversion rate, overdue tasks
- Pipeline breakdown by stage (count + value)
- Monthly closed deals chart (last 6 months, zero-filled)
- Tasks due today
- Recent deal activity feed (last 10)
- Top clients by deal volume
- Agent-scoped: agents see own data, super_admin sees all

### Subscription Billing (Stripe)
- Free plan: up to 5 property listings
- Pro plan: $29/month, unlimited
- Stripe Checkout hosted payment flow
- Customer Portal for subscription management
- Webhook handling: `checkout.session.completed`, `customer.subscription.deleted`, `invoice.payment_failed`, `invoice.payment_succeeded`
- Signature verification on all webhook events
- Free-tier middleware gate on property creation (402 response)

### Profile Management
- Update name, email, phone
- Password change with current password verification
- Avatar upload (stored in public disk)

---

## API Structure

```
POST   /api/register
POST   /api/login
POST   /api/logout
GET    /api/me

GET    /api/dashboard

GET    /api/properties
POST   /api/properties
GET    /api/properties/{id}
PATCH  /api/properties/{id}
DELETE /api/properties/{id}
POST   /api/properties/{id}/images
DELETE /api/properties/{id}/images/{imageId}
PATCH  /api/properties/{id}/images/{imageId}/primary

GET    /api/clients
POST   /api/clients
GET    /api/clients/{id}
PATCH  /api/clients/{id}
DELETE /api/clients/{id}
POST   /api/clients/{id}/notes
DELETE /api/clients/{id}/notes/{noteId}

GET    /api/deals/kanban
GET    /api/deals
POST   /api/deals
GET    /api/deals/{id}
PATCH  /api/deals/{id}
DELETE /api/deals/{id}
PATCH  /api/deals/{id}/stage
POST   /api/deals/{id}/notes
DELETE /api/deals/{id}/notes/{noteId}

GET    /api/tasks/summary
GET    /api/tasks/grouped
GET    /api/tasks
POST   /api/tasks
GET    /api/tasks/{id}
PATCH  /api/tasks/{id}
DELETE /api/tasks/{id}
POST   /api/tasks/{id}/subtasks
POST   /api/tasks/{id}/notes
DELETE /api/tasks/{id}/notes/{noteId}
POST   /api/tasks/{id}/complete-occurrence

GET    /api/profile
PATCH  /api/profile
POST   /api/profile/password
POST   /api/profile/avatar

POST   /api/subscription/checkout
POST   /api/subscription/portal
POST   /api/webhooks/stripe
```

---

## Database Schema

| Table | Purpose |
|---|---|
| `users` | Agents, admins, clients with role + subscription status |
| `properties` | Listings with full address and spec fields |
| `property_images` | Multiple images per property, primary flag |
| `clients` | Contact profiles with buyer qualification fields |
| `notes` | Polymorphic — attached to clients, deals, or tasks |
| `deals` | Pipeline deals with stage, financials, closed_at |
| `deal_activities` | Immutable log of every stage change |
| `tasks` | Tasks with recurrence, subtasks (self-referential), priority |
| `task_occurrences` | Recurring task completion history |

---

## Local Setup

### Requirements
- PHP 8.3
- Composer
- MySQL 8
- Node.js 18+
- XAMPP or Laravel Herd

### Installation

```bash
git clone https://github.com/arafin06/real-estate-crm.git
cd real-estate-crm

composer install

cp .env.example .env
php artisan key:generate
```

Configure `.env`:
```env
DB_DATABASE=real_estate_crm
DB_USERNAME=root
DB_PASSWORD=

STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_PRICE_ID=price_...
STRIPE_SUCCESS_URL=http://localhost:5173/settings?subscription=success
STRIPE_CANCEL_URL=http://localhost:5173/settings?subscription=cancelled
```

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

API runs at `http://localhost:8000`

### Stripe Webhooks (local)
```bash
stripe listen --events checkout.session.completed,customer.subscription.deleted,invoice.payment_failed,invoice.payment_succeeded --forward-to localhost:8000/api/webhooks/stripe
```

---

## Demo Data

The seeder creates two agents with different subscription tiers, realistic U.S. property listings across Austin TX and Nashville TN, clients with buyer qualification data and contact history, deals spread across all 7 pipeline stages, and tasks including recurring and subtask examples.

---

## Architecture Notes

- **Subscription middleware** gates `POST /api/properties` at 5 listings for free users, returns `402` with `{ upgrade: true }` payload that the frontend intercepts globally
- **`closed_at` timestamp** on deals prevents commission figures from shifting when closed deals are later edited
- **Polymorphic notes** (`notable_type` / `notable_id`) allow a single `notes` table to serve clients, deals, and tasks without schema duplication
- **Recurring task occurrences** are logged to a separate `task_occurrences` table rather than creating new task records, keeping the task list clean while preserving full completion history
- **Role scoping** is applied at the query level in every controller — agents see only their own data, super_admin bypasses all ownership checks

---

## Author

**Niaz Md. Arafin Haque**
Director of Operations — Global Softel Inc.
5+ years in U.S. real estate and commercial lending operations

[github.com/arafin06](https://github.com/arafin06)
