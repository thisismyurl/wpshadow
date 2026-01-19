# WPShadow Feature Audit - Implementation Fixes

## Nonce Verification Fixes

### Pattern to Apply

Add `check_ajax_referer()` at the start of each AJAX handler:

```php
public function ajax_handler_name(): void {
    // Add nonce verification first
    check_ajax_referer( 'wpshadow_nonce_name', 'nonce' );
    
    // Then capability check
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
    }
    
    // ... rest of handler
}
```

### JavaScript Pattern

Update AJAX calls to include nonce:

```javascript
fetch(settings.ajaxUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
        action: 'wpshadow_handler_name',
        nonce: settings.nonce,  // Add nonce
        // ... other parameters
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        // Handle success
    } else {
        console.error(data.data?.message || 'Error');
    }
});
```

---

## Specific File Fixes

### 1. class-wps-feature-core-diagnostics.php

**Location:** Line ~210 (in ajax_run_diagnostics method)

**Before:**
```php
public function ajax_run_diagnostics(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Permission denied' );
    }
```

**After:**
```php
public function ajax_run_diagnostics(): void {
    check_ajax_referer( 'wpshadow_run_diagnostics_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Permission denied' );
    }
```

---

### 2. class-wps-feature-core-integrity.php

**Location:** Multiple AJAX handlers

**Fix all 3 handlers:**
- `ajax_scan_core_files()`
- `ajax_repair_core_file()`
- `ajax_repair_all_core_files()`

**Pattern:**
```php
public function ajax_scan_core_files(): void {
    check_ajax_referer( 'wpshadow_scan_core_files_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Permission denied' );
    }
    // ... rest of implementation
}
```

---

### 3. class-wps-feature-dark-mode.php (both files)

**Files:**
- `includes/features/class-wps-feature-dark-mode.php`

**Handler:** `ajax_set_dark_mode()`

**Fix:**
```php
public function ajax_set_dark_mode(): void {
    check_ajax_referer( 'wpshadow_dark_mode_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Permission denied' );
    }
    // ... rest of implementation
}
```

---

### 4. class-wps-feature-external-fonts-disabler.php

**Handler:** `ajax_save_external_fonts_settings()`

**Fix:**
```php
public function ajax_save_external_fonts_settings(): void {
    check_ajax_referer( 'wpshadow_external_fonts_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Permission denied' );
    }
    // ... rest of implementation
}
```

---

### 5. class-wps-feature-search.php

**Handler:** `ajax_search_features()`

**Fix:**
```php
public static function ajax_search_features(): void {
    check_ajax_referer( 'wpshadow_search_nonce', 'nonce' );
    // Note: This can be nopriv, but still check nonce
    // ... rest of implementation
}
```

---

### 6. class-wps-feature-a11y-audit.php

**Handler:** `ajax_audit_page()`

**Before:**
```php
public function ajax_audit_page(): void {
    if ( ! check_ajax_referer( 'wpshadow_a11y_audit_nonce', 'nonce', false ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed', 'wpshadow' ) ) );
        return;
    }
```

**After:**
```php
public function ajax_audit_page(): void {
    check_ajax_referer( 'wpshadow_a11y_audit_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Permission denied', 'wpshadow' ) ) );
        return;
    }
```

**Note:** This file already has nonce checks, just needs proper placement

---

### 7. class-wps-feature-color-contrast-checker.php

**Handler:** `ajax_check_contrast()`

**Fix:**
```php
public function ajax_check_contrast(): void {
    check_ajax_referer( 'wpshadow_contrast_check_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Permission denied' );
    }
    // ... rest of implementation
}
```

---

### 8. class-wps-feature-content-optimizer.php

**Fixes needed:**
1. Add nonce to `ajax_run_content_check()`
2. Change minimum_capability to edit_posts

**Fix 1 - Nonce (around line 250):**
```php
public function ajax_run_content_check(): void {
    check_ajax_referer( 'wpshadow_content_check_nonce', 'nonce' );
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Permission denied' );
    }
    // ... rest of implementation
}
```

**Fix 2 - Capability (in constructor around line 35):**
```php
parent::__construct(
    array(
        'id'              => 'content-optimizer',
        'name'            => __( 'Complete Content Quality Optimizer', 'wpshadow' ),
        // ... other config ...
        'minimum_capability' => 'edit_posts',  // ADD THIS LINE
        // ... rest of config ...
    )
);
```

---

### 9. class-wps-feature-pre-publish-review.php

**Fixes needed:**
1. Add nonce to both AJAX handlers
2. Change minimum_capability to edit_posts

**Fix 1 - Nonce in `ajax_run_pre_publish_check()` (around line 120):**
```php
public function ajax_run_pre_publish_check(): void {
    check_ajax_referer( 'wpshadow_pre_publish_nonce', 'nonce' );
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Permission denied' );
    }
    // ... rest of implementation
}
```

**Fix 2 - Nonce in `ajax_save_user_preferences()` (around line 135):**
```php
public function ajax_save_user_preferences(): void {
    check_ajax_referer( 'wpshadow_review_prefs_nonce', 'nonce' );
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Permission denied' );
    }
    // ... rest of implementation
}
```

**Fix 3 - Capability (in constructor around line 33):**
```php
parent::__construct(
    array(
        'id'              => 'pre-publish-review',
        'name'            => __( 'Check Content Before Publishing', 'wpshadow' ),
        // ... other config ...
        'minimum_capability' => 'edit_posts',  // ADD THIS LINE
        // ... rest of config ...
    )
);
```

---

### 10. class-wps-feature-paste-cleanup.php

**Fixes needed:**
1. Change minimum_capability to edit_posts

**Fix (in constructor around line 30):**
```php
parent::__construct(
    array(
        'id'              => 'paste-cleanup',
        'name'            => __( 'Clean Up Pasted Content', 'wpshadow' ),
        // ... other config ...
        'minimum_capability' => 'edit_posts',  // ADD THIS LINE
        // ... rest of config ...
    )
);
```

---

### 11. class-wps-feature-setup-checks.php

**Fixes needed:**
1. Add nonce to `ajax_run_setup_checks()`
2. Add nonce to `ajax_dismiss_setup_notice()`

**Fix 1 - Nonce in `ajax_run_setup_checks()` (around line 180):**
```php
public function ajax_run_setup_checks(): void {
    check_ajax_referer( 'wpshadow_setup_checks_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Permission denied' );
    }
    // ... rest of implementation
}
```

**Fix 2 - Nonce in `ajax_dismiss_setup_notice()` (around line 195):**
```php
public function ajax_dismiss_setup_notice(): void {
    check_ajax_referer( 'wpshadow_setup_dismiss_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Permission denied' );
    }
    // ... rest of implementation
}
```

---

### 12. class-wps-feature-tips-coach.php

**Fixes needed:**
1. Add nonce to `ajax_dismiss_tip()`
2. Add nonce to `ajax_apply_tip_action()`

**Fix 1 - Nonce in `ajax_dismiss_tip()` (around line 170):**
```php
public function ajax_dismiss_tip(): void {
    check_ajax_referer( 'wpshadow_tips_dismiss_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Permission denied' );
    }
    // ... rest of implementation
}
```

**Fix 2 - Nonce in `ajax_apply_tip_action()` (around line 185):**
```php
public function ajax_apply_tip_action(): void {
    check_ajax_referer( 'wpshadow_tips_action_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Permission denied' );
    }
    // ... rest of implementation
}
```

---

## Nonce Registration Pattern

For each AJAX handler, add the nonce to your admin enqueue:

```php
// In enqueue_admin_scripts() or similar:
wp_localize_script(
    'your-script-handle',
    'wpshadowSettings',
    array(
        'nonce'    => wp_create_nonce( 'wpshadow_nonce_name' ),
        'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
        // ... other settings
    )
);
```

---

## Testing Nonce Failures

### Test with cURL:
```bash
# Without nonce - should fail
curl -X POST http://localhost/wp-admin/admin-ajax.php \
  -d "action=wpshadow_run_diagnostics" \
  -d "nonce=invalid"

# With valid nonce - should work
curl -X POST http://localhost/wp-admin/admin-ajax.php \
  -d "action=wpshadow_run_diagnostics" \
  -d "nonce=[VALID_NONCE]"
```

### Test with JavaScript Console:
```javascript
// Try AJAX without nonce in request
fetch(ajaxUrl, {
    method: 'POST',
    body: new URLSearchParams({
        action: 'wpshadow_handler',
        // nonce: missing
    })
})
.then(r => r.json())
.then(d => console.log(d));
// Should return: { success: false }
```

---

## Summary of Changes

### Nonce Additions (17 handlers across 11 files)
- [ ] core-diagnostics.php - 1 handler
- [ ] core-integrity.php - 3 handlers
- [ ] dark-mode.php - 2 handlers (2 files)
- [ ] external-fonts-disabler.php - 1 handler
- [ ] search.php - 1 handler
- [ ] color-contrast-checker.php - 1 handler
- [ ] content-optimizer.php - 1 handler
- [ ] pre-publish-review.php - 2 handlers
- [ ] setup-checks.php - 2 handlers
- [ ] tips-coach.php - 2 handlers

### Capability Updates (3 files)
- [ ] content-optimizer.php - change to edit_posts
- [ ] pre-publish-review.php - change to edit_posts
- [ ] paste-cleanup.php - change to edit_posts

### Testing
- [ ] Verify all nonce checks work
- [ ] Test content features with Editor role
- [ ] Verify AJAX handlers reject invalid nonces
- [ ] Verify backward compatibility

---

**Implementation Priority:** HIGH - Start with security (nonces), then capability fixes
**Estimated Time:** 2-3 hours for implementation + testing
**Testing Time:** 1-2 hours
**Total Effort:** 3-5 hours
