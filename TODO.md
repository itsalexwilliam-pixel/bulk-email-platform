# SMTP + Sending Engine TODO

- [ ] Update auth routes for SMTP CRUD/toggle and campaign send-now
- [ ] Create SMTP index view (list + add form + toggle + delete)
- [ ] Create SMTP edit view (update without exposing password)
- [ ] Refine SendController queue dedupe behavior and route compatibility
- [ ] Refine queue:work-mails command (pending/failed attempts<3, rotation, retries)
- [ ] Register/schedule command (optional recommended)
- [ ] Run critical-path tests:
  - [ ] Add SMTP server
  - [ ] Toggle active/inactive
  - [ ] Trigger send and verify queue creation
  - [ ] Verify dedupe (no duplicate queue rows)
  - [ ] Run queue:work-mails and verify sent/failed + retries up to 3
