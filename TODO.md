# TODO - Campaign Pause/Warmup Fix + Latest Push

- [x] Reproduce and identify broken open/click behaviors across main modules
- [x] Inspect relevant routes, controllers, and Blade views tied to broken actions
- [x] Implement fixes for campaign pause (still sending) and warmup behavior
- [x] Add/adjust automated tests for pause/warmup behavior
- [x] Run targeted tests for campaign queue behavior
- [x] Run full automated test suite (`php artisan test`) and verify pass status
- [x] Extend warmup schedule from 7 days to 21 days (3 weeks)
- [x] Update/add tests for 21-day warmup behavior
- [x] Re-run targeted and full automated tests
- [x] Add Warmup Report tab (route + controller + view + nav links)
- [x] Add/update feature tests for Warmup Report tab
- [x] Run targeted and full tests after Warmup Report changes
- [ ] Consolidate sidebar to a single "Reports" item and keep report options inside report pages
- [ ] Update report navigation labels/UI to unified "Reports" experience
- [ ] Re-run targeted and full tests after reports consolidation
- [ ] Commit fixes with clear message
- [ ] Push latest fixed code to `https://github.com/itsalexwilliam-pixel/bulk-email-platform.git` (using your GitHub auth)
