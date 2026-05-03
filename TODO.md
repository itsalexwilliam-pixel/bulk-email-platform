# Campaign Fix TODO

- [ ] Add/verify campaign account context persistence (`Campaign::$fillable`, `CampaignController@store`)
- [ ] Scope campaign CRUD listing/forms data by authenticated account
- [ ] Fix campaign body preview formatting for plain text line breaks
- [ ] Improve queue worker missing-account debug log context
- [ ] Run focused validation commands/tests

---

# Reports Tab Fix TODO

- [x] Update global form-submit lock logic in `resources/views/layouts/app.blade.php`
  - [x] Skip GET forms (reports filters should remain usable)
  - [x] Apply lock only to mutating methods: POST/PUT/PATCH/DELETE
  - [x] Guard with `form.checkValidity()` before disabling submit button

- [ ] Run focused test: `tests/Feature/ReportsFeatureTest.php`

---

# Campaign Rate-Control Fix TODO

- [ ] Add `emails_per_minute` nullable integer column to campaigns table
- [ ] Update `Campaign` model fillable/casts for speed config
- [ ] Update campaign create/edit validation + persistence
- [ ] Update campaign create/edit UI to set emails per minute
- [ ] Keep SendController queue-only flow (status + dispatch job only)
- [ ] Update worker (`queue:work-mails`) to apply per-campaign dynamic limits (no hardcoded 60)
- [ ] Ensure fair grouped processing by campaign with per-campaign cap
- [ ] Show send speed on campaign index (`X emails/min`)
- [ ] Add/update tests for speed persistence and rate-respecting worker behavior
- [ ] Run focused tests and verify no queue flow regression
