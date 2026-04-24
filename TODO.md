# UI Upgrade TODO (Mailchimp-style SaaS)

- [ ] Create reusable UI components
  - [ ] `resources/views/components/saas-sidebar.blade.php`
  - [ ] `resources/views/components/saas-navbar.blade.php`
  - [ ] `resources/views/components/saas-stat-card.blade.php`

- [ ] Replace unified app layout
  - [ ] Update `resources/views/layouts/app.blade.php`
  - [ ] Add dark mode toggle with localStorage persistence
  - [ ] Add toast notifications area
  - [ ] Add sidebar collapse behavior + active route highlighting

- [ ] Rebuild dashboard UI
  - [ ] Update `resources/views/dashboard.blade.php`
  - [ ] Add dynamic cards (contacts, campaigns, emails sent)
  - [ ] Add Chart.js (line + bar)
  - [ ] Add Recent Campaigns table from existing data

- [ ] Upgrade contacts page UI
  - [ ] Update `resources/views/contacts/index.blade.php`
  - [ ] Add search/filter UI (frontend only)
  - [ ] Add bulk select UI
  - [ ] Preserve existing CRUD routes/forms

- [ ] Upgrade campaign pages UI
  - [ ] Update `resources/views/campaigns/index.blade.php`
  - [ ] Update `resources/views/campaigns/create.blade.php` with 4-step builder shell
  - [ ] Keep form logic unchanged

- [ ] Upgrade SMTP page UI
  - [ ] Update `resources/views/smtp/index.blade.php`
  - [ ] Card-based server UI + active toggle switch UI
  - [ ] Keep form submissions intact

- [ ] Create reports page
  - [ ] Add `resources/views/reports/index.blade.php`
  - [ ] Chart.js visualizations for opens/clicks/unsubscribes
  - [ ] Table summaries

- [ ] Critical-path UI testing
  - [ ] Dashboard load
  - [ ] Contacts CRUD UI
  - [ ] Campaign create flow UI
  - [ ] SMTP page interactions
