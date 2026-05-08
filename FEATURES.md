# Novelio Technologies — Bulk Email Platform
## Features & Unique Selling Points

---

## 🚀 Core Features

### 📧 Email Campaigns
- **Full campaign lifecycle** — Create, edit, schedule, pause, resume, and delete campaigns
- **Rich text editor (Quill.js)** — WYSIWYG editor with font, colour, alignment, lists, links, images and code-block support
- **Raw HTML editor** — Switch between rich-text and raw HTML with live sync
- **File attachments** — Attach files up to 10 MB per campaign
- **Email warm-up** — Built-in warm-up schedule to gradually increase sending volume and protect deliverability
- **Emails-per-minute throttling** — Fine-grained rate control to stay within SMTP limits
- **Scheduled campaigns** — Set a future date/time for automatic delivery
- **Live stats dashboard** — Real-time counters (queued / sent / opened / failed) polled without page reload
- **Test email** — Send a test copy before launch

### 👥 Audience Management
- **Contacts** — Full CRUD (Create, Read, Update, Delete) on individual contacts
- **Groups / Lists** — Organise contacts into groups; campaigns target one or more groups
- **Bulk group assignment** — Assign hundreds of contacts to a group in one action
- **Bulk delete** — Remove contacts in bulk with safety confirmation
- **CSV import** — Import contacts from a CSV file with flexible column mapping:
  - Single "name" column *or* separate first/last name columns
  - Optional business name, website, and group assignment
  - **Per-row validation feedback** — every skipped row shows the exact reason (invalid email, duplicate, already exists)

### 📬 SMTP / Sending Infrastructure
- **Multiple SMTP servers** — Configure unlimited SMTP accounts (Gmail, Outlook, Zoho, or custom)
- **Provider presets** — One-click fill of host/port/encryption for popular providers
- **Encrypted passwords** — SMTP passwords stored with Laravel's `encrypt()` / `decrypt()`
- **Activate / Deactivate** — Toggle individual SMTP servers on/off without deleting them
- **Connection test** — Test SMTP connectivity directly from the UI
- **Send test email** — Fire a real test message through a specific SMTP server
- **Daily send limit** — Per-server daily limit to avoid quota overruns
- **Priority routing** — Assign a priority to control which server the queue worker prefers
- **CSV bulk upload** — Add dozens of SMTP servers in one CSV upload with per-row error reporting

### 📋 Email Templates Library
- **Template CRUD** — Create, edit, delete reusable email templates
- **Category tags** — Organise templates by category with tab-filter UI
- **Rich HTML editor** — Same Quill + raw-HTML editor as campaigns
- **Load template in campaigns** — One-click load of a saved template into the campaign editor (subject pre-fills if empty)
- **"Use in Campaign" shortcut** — Jump directly from a template to the campaign builder

### 📊 Dashboard & Reporting
- **Account-scoped dashboard** — Stat cards: total campaigns, total contacts, emails sent, emails opened, failed emails, open rate
- **Chart.js charts** — Bar chart (campaigns over time) and doughnut chart (sent / opened / failed breakdown)
- **Campaign report** — Per-campaign stats table with badge-coded status
- **Single email report** — Delivery history for individually sent emails
- **Failed email panel** — Quick view of recent failures with error messages

### 🔓 Unsubscribe & Legal Compliance
- **Branded unsubscribe footer** — Every campaign email includes a professional footer with unsubscribe link, app name, and copyright
- **List-Unsubscribe headers** — `List-Unsubscribe` and `List-Unsubscribe-Post` headers added automatically for Gmail / Outlook native unsubscribe button
- **Unsubscribe landing page** — Clean confirmation page on unsubscribe (inline CSS, no CDN dependency)
- **Suppression list** — Unsubscribed emails are stored and automatically excluded from future sends

### 👤 Multi-Account Architecture
- **Account isolation** — All data (campaigns, contacts, groups, SMTP servers, templates) is scoped to an `account_id`; users in different accounts never see each other's data
- **Centralized account resolution** — Base controller provides `getAccountId()` / `currentAccountId()` helpers with automatic 403 abort on missing context

### 🔒 Security & Stability
- **Rate limiting** — Campaign send/pause/resume throttled at 10 req/min; single email at 20 req/min; SMTP test endpoints at 10 req/min
- **CSRF protection** — All mutating forms include `@csrf`
- **Input validation** — Server-side validation on all form submissions with user-friendly error messages
- **Queue-based sending** — Emails are dispatched via Laravel's queue system; the UI stays responsive during large campaigns
- **Supervisor configuration** — `supervisor.conf` included for production queue worker management with auto-restart

### 🎨 UI / UX
- **Professional SaaS layout** — Sticky top navbar, collapsible sidebar, responsive for mobile
- **7 built-in themes** — Light, Dark, Pro Teal, Midnight Navy, Deep Emerald, Royal Purple, Charcoal — all persisted in `localStorage`
- **Dark mode** — Full Tailwind `dark:` class support across all views
- **Novelio Technologies branding** — Custom logo in sidebar, top bar, login page, and browser tab (favicon)
- **Toast / flash messages** — Success and error feedback after every action
- **Inline form validation** — Real-time body-empty guard on the campaign editor

---

## 💡 Unique Selling Points (USP)

| # | USP | Detail |
|---|-----|--------|
| 1 | **Zero third-party email service dependency** | Send through any SMTP server you own — no Mailchimp, SendGrid, or SES required. You control the infrastructure entirely. |
| 2 | **Per-row CSV import errors** | Most importers silently skip bad rows. Novelio shows you a table of every skipped contact with the exact reason (invalid email, duplicate, already exists), so you fix your data confidently. |
| 3 | **Multi-SMTP priority routing** | Configure multiple SMTP accounts and assign priorities. The queue worker automatically picks the highest-priority active server, giving you built-in failover and load distribution. |
| 4 | **Built-in email warm-up** | Gradually increase sending volume on a per-campaign basis using the warm-up schedule, protecting your domain reputation without any external tool. |
| 5 | **Templates with one-click campaign load** | A modal in the campaign editor lets you search and load any saved template in seconds — subject line pre-fills automatically. |
| 6 | **Full account isolation** | Every resource is scoped to an account. Run a SaaS version with multiple tenants without any data leakage between accounts. |
| 7 | **Professional compliance out of the box** | RFC-compliant `List-Unsubscribe` headers, branded unsubscribe footer, and suppression list — everything needed to avoid spam filters and honour user preferences. |
| 8 | **Live campaign stats without page reload** | The campaign detail page polls the server every few seconds to show queued / sent / opened / failed counts and a scrollable log of recent deliveries. |
| 9 | **7 professional colour themes** | Light, Dark, Pro Teal, Midnight Navy, Deep Emerald, Royal Purple, and Charcoal — theme persists per-browser, so every user can personalise the UI. |
| 10 | **Production-ready queue setup** | Ships with a `supervisor.conf` for two queue worker processes with auto-restart, max execution time, and correct log paths — ready to copy to your server. |

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend framework | Laravel 12 (PHP 8.2+) |
| Frontend CSS | Tailwind CSS (CDN — JIT) |
| Rich text editor | Quill.js 2.0 |
| Charts | Chart.js |
| Database | MySQL / SQLite (via Eloquent ORM) |
| Queue | Laravel Queue (database driver) |
| Process manager | Supervisor |
| Authentication | Laravel Breeze |
| Email sending | Laravel Mailer (Symfony Mailer) |

---

## 📦 Versioning

| Version | Highlights |
|---------|-----------|
| v1.0.0 | Initial release — core campaign & contact management |
| v1.1.0 | Novelio Technologies rebrand; SMTP encryption; Quill editor sync fixes |
| v1.2.0 | Phase 1 — Security hardening, rate limiting, Supervisor config |
| v1.3.0 | Phase 2 — Rich account-scoped dashboard with Chart.js |
| v1.4.0 | Phase 3 — Branded unsubscribe footer & List-Unsubscribe headers |
| v1.5.0 | Phase 4 — Email Templates Library with campaign integration |
| v1.6.0 | Phase 5 — Code refactoring; centralised account ID; real error messages |
| v1.7.0 | Phase 6 — Per-row CSV import validation feedback |
| **v1.8.0** | **UI polish — Load Template fix, table layouts (Templates & SMTP), 7 themes, Novelio logo & favicon** |

---

*Built with ❤️ by Novelio Technologies*
