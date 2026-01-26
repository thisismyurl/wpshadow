# Consent Handler Testing

## Status After Update (v1.2601.212257)

### AJAX Handler Registration ✅
- Consent_Preferences_Handler::register() now adds BOTH:
  - `wp_ajax_wpshadow_save_consent` (authenticated)
  - `wp_ajax_nopriv_wpshadow_save_consent` (unauthenticated)
  - `wp_ajax_wpshadow_dismiss_consent` (authenticated)
  - `wp_ajax_nopriv_wpshadow_dismiss_consent` (unauthenticated)

### Test Results
- **Handler Registration**: ✅ NOW WORKING
  - Before: Returned "0" (action not found)
  - After: Returns proper error message: `{"success":false,"data":{"message":"Security check failed..."}}`
  
- **Nonce Verification**: ✅ WORKING
  - Handler properly validates nonce
  - Error message is user-friendly
  - Nonce is created via `wp_create_nonce('wpshadow_consent')`
  - Nonce is passed to frontend via `wp_localize_script()` in `wpshadow` global object

### Frontend Banner
- Banner only shows to logged-in admins who:
  1. Haven't consented yet
  2. Haven't dismissed it in the last 30 days
  
- When clicked, JS calls AJAX with:
  - action: `wpshadow_save_consent` or `wpshadow_dismiss_consent`
  - nonce: `getConsentNonce()` (from `window.wpshadow.consent_nonce`)
  - Additional params as needed

### Next Steps for Testing
1. Access WordPress admin panel (logged in as admin)
2. Clear user consent: `DELETE FROM wp_usermeta WHERE meta_key = 'wpshadow_initial_consent'`
3. Open admin panel - consent banner should appear
4. Click "Save Preferences" or "Not now"
5. Should succeed and banner should hide
6. Check activity log for "consent_saved" event

### Debug
Check debug log at `/home/sailmar1/public_html/wpshadow/wp-content/plugins/wpshadow/debug-ajax.log`
