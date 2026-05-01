# Inline-first CSS delivery TODO

- [x] Update `SingleEmailMail` flow to: preprocess → inline → sanitize (light) → tracking → send
- [x] Ensure `SingleEmailMail` sanitization only strips flex/grid, position, and CSS variables
- [x] Update `CampaignMail` to use inline-first flow with real inliner and keep `<style>` fallback
- [x] Ensure tracking rewrite remains untouched and happens after inlining
- [x] Keep inline HTML preview logging before sending
- [ ] Run `php artisan test` and confirm 100% passing
- [ ] Manual verification: send 1 email and verify inline styles, content-type, rendering, and tracked links
