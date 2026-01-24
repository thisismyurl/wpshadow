# QUICK START: Implementing Documented Diagnostics Testing

## What You're Looking At

You have **2,576 documented diagnostic stubs** in `/includes/diagnostics/documented/` that are ready to have their test methods implemented.

**Current State:**
- ✅ Framework exists (class structure, check() method, metadata)
- ✅ KB links exist (philosophy compliance)
- ✅ Training links exist (educational)
- ❌ Test methods are stubs (return `'Test not yet implemented'`)

**Goal:**
- Implement `test_live_[slug]()` methods to validate each diagnostic
- Move completed tests to `/includes/diagnostics/` (active registry)
- Achieve full test coverage for all diagnostics

---

## Phase 1: Quick Wins (Compliance Category - 2-3 hours)

Start with the **9 COMPLIANCE diagnostics** - highest business value, smallest category.

### Files to Implement:

```
includes/diagnostics/documented/compliance/
├── class-diagnostic-ccpa-compliance.php
├── class-diagnostic-cookie-consent.php
├── class-diagnostic-data-retention-policy.php
├── class-diagnostic-gdpr-compliance.php
├── class-diagnostic-privacy-policy-current.php
├── class-diagnostic-terms-of-service-current.php
├── class-diagnostic-user-data-deletion.php
├── class-diagnostic-user-data-export.php
└── class-diagnostic-wcag-accessibility.php
```

### Example Implementation:

**File**: `includes/diagnostics/documented/compliance/class-diagnostic-privacy-policy-current.php`

```php
public static function test_live_privacy_policy_current(): array {
    $result = self::check();

    // If result is null, diagnostic passed (policy page exists)
    // If result is array, diagnostic failed (policy page missing)

    $policy_id = get_option('wp_page_for_privacy_policy', 0);
    $expected_failure = !$policy_id; // Should fail if no policy page
    $actual_failure = !is_null($result); // Actually failed if result is not null

    return [
        'passed' => $expected_failure === $actual_failure,
        'message' => 'Privacy policy page check: ' . ($policy_id ? 'Found' : 'Missing')
    ];
}
```

---

## Phase 2: General Category (2-3 hours)

After compliance, move to **GENERAL** (20 diagnostics from top 100):

### Why General?
- Core site functionality
- Most users interact with these daily
- Easy to test with WordPress functions
- Clear success/failure criteria

---

## Phase 3: System + Security (5-6 hours)

Continue with **SYSTEM** (20) and **SECURITY** (20) diagnostics.

### Testing Pattern for Security:

```php
public static function test_live_automatic_security_updates(): array {
    $result = self::check();

    // Check if auto-updates are enabled
    $auto_updates = get_option('auto_update_core_dev') ||
                   get_option('auto_update_core_minor') ||
                   get_option('auto_update_plugins');

    // Result should be null if auto-updates ON, array if OFF
    $test_passes = !is_null($result) === !$auto_updates;

    return [
        'passed' => $test_passes,
        'message' => 'Auto-updates: ' . ($auto_updates ? 'Enabled' : 'Disabled')
    ];
}
```

---

## Testing These Implementations

### Local Test (No Docker Required)

```php
<?php
// In wp-admin or local test:
wp_set_current_user(1); // Admin user

// Include the diagnostic
require_once 'includes/diagnostics/documented/compliance/class-diagnostic-privacy-policy-current.php';

use WPShadow\Diagnostics\Diagnostic_Privacy_Policy_Current;

// Test it
$result = Diagnostic_Privacy_Policy_Current::test_live_privacy_policy_current();
var_dump($result);
// Expected: ['passed' => true/false, 'message' => '...']
```

### Docker Test

```bash
docker exec wpshadow-test bash -c 'cd /var/www/html && php -r "
define(\"WP_USE_THEMES\", false);
require(\"./wp-load.php\");
wp_set_current_user(1);

// Your test code here
"'
```

---

## Implementation Checklist

For each diagnostic, verify:

- [ ] Read the `check()` method to understand what it detects
- [ ] Identify what WordPress functions it uses
- [ ] Implement test logic that validates check() returns expected result
- [ ] Test returns `['passed' => bool, 'message' => string]`
- [ ] Test covers both pass and fail scenarios
- [ ] No PHP errors/warnings in output
- [ ] Test runs in WordPress context (uses wp functions correctly)

---

## Common Testing Patterns

### Pattern 1: Plugin-Based Detection
```php
public static function test_live_[slug](): array {
    $result = self::check();

    $plugin = 'some-plugin/file.php';
    $plugin_active = is_plugin_active($plugin);

    // Result should be null if plugin IS active (no issue)
    // Result should be array if plugin NOT active (issue found)
    return [
        'passed' => !is_null($result) === !$plugin_active,
        'message' => 'Plugin ' . ($plugin_active ? 'active' : 'inactive')
    ];
}
```

### Pattern 2: Option-Based Detection
```php
public static function test_live_[slug](): array {
    $result = self::check();

    $option_value = get_option('option_name', false);

    // Result should be null if option is good
    // Result should be array if option is bad
    return [
        'passed' => !is_null($result) === !$option_value,
        'message' => 'Option value: ' . var_export($option_value, true)
    ];
}
```

### Pattern 3: Page/Post Check
```php
public static function test_live_[slug](): array {
    $result = self::check();

    $page_id = get_option('wp_page_for_privacy_policy', 0);
    $page_exists = $page_id > 0;

    // Result should be null if page exists
    // Result should be array if page missing
    return [
        'passed' => !is_null($result) === !$page_exists,
        'message' => 'Page ' . ($page_exists ? 'exists' : 'missing')
    ];
}
```

### Pattern 4: Count-Based Detection
```php
public static function test_live_[slug](): array {
    $result = self::check();

    $posts = get_posts(['post_type' => 'post', 'numberposts' => -1]);
    $has_posts = count($posts) > 0;

    // Check if diagnostic result matches expected state
    return [
        'passed' => !!$result === !$has_posts,
        'message' => 'Posts found: ' . count($posts)
    ];
}
```

---

## Next Steps After Implementation

1. **Batch Test**: Run all 100 tests in sequence to catch errors
2. **Move to Active**: Move passing tests to `/includes/diagnostics/` (active registry)
3. **Register**: Update `Diagnostic_Registry` to include new tests
4. **Verify**: Test in WordPress admin to ensure they run without errors
5. **Phase 2**: Repeat for remaining 1,976 diagnostics

---

## Resources

- **Diagnostic Template**: [docs/DIAGNOSTIC_TEMPLATE.md](docs/DIAGNOSTIC_TEMPLATE.md)
- **Full List**: [TOP_100_DIAGNOSTICS_FOR_TESTING.md](TOP_100_DIAGNOSTICS_FOR_TESTING.md)
- **Docker Environment**: [docs/DOCKER_TESTING_ENVIRONMENT.md](docs/DOCKER_TESTING_ENVIRONMENT.md)
- **Diagnostic Registry**: [includes/diagnostics/class-diagnostic-registry.php](includes/diagnostics/class-diagnostic-registry.php)
- **Philosophy Compliance**: [docs/PRODUCT_PHILOSOPHY.md](docs/PRODUCT_PHILOSOPHY.md)

---

## FAQ

**Q: Do I need to modify the `check()` method?**
A: No - the `check()` method is already correct. Only implement the `test_live_*()` method.

**Q: What if the diagnostic is complex (not just WordPress functions)?**
A: For complex diagnostics (e.g., performance analysis, code scanning), keep the stub or mark as TODO for Phase 2.

**Q: Should I test edge cases?**
A: Yes - verify both pass and fail scenarios. For example, test with plugin both active and inactive.

**Q: How do I know if a test passes?**
A: The test should return `['passed' => true, 'message' => '...']` when the WordPress site state matches what the diagnostic expects.

**Q: Can I run all 100 tests at once?**
A: Yes - use the Docker test environment or a batch script to run all test methods sequentially.

---

*Start with COMPLIANCE (9 diagnostics) → then GENERAL (20) → then SYSTEM + SECURITY (40). After 25 hours, you'll have 100 fully tested diagnostics ready for production.*
