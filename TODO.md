# TODO - Email Footer + Tracking Simplification

- [x] Locate email footer/tracking injection source
- [x] Replace footer with minimal unsubscribe-only plain-text style
- [x] Remove subscription statement and branding/footer legal text
- [x] Disable open-tracking pixel image injection
- [x] Preserve unsubscribe link functionality
- [x] Preserve click tracking and avoid rewriting unsubscribe URLs
- [ ] Run targeted tests (`tests/Feature/SingleEmailFeatureTest.php`)
- [ ] Run full test suite (`php artisan test`)
