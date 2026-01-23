# EXAMPLE IMPLEMENTATION: Privacy Policy Diagnostic Test

## The Diagnostic (Already Exists)

File: `includes/diagnostics/documented/compliance/class-diagnostic-privacy-policy-current.php`

### Current State:
- ✅ `check()` method: Fully implemented, verifies privacy policy exists and is current
- ❌ `test_live_privacy_policy_current()` method: Stub only

### What check() Does:
1. Gets privacy policy page ID from WordPress option
2. Returns null if page exists AND updated within 12 months (PASS)
3. Returns error array if page missing OR not updated in 12+ months (FAIL)

---

## Step 1: Read & Understand the check() Method

```php
public static function check(): ?array {
    // Step 1: Get privacy policy page ID
    $privacy_policy_id = (int) get_option('wp_page_for_privacy_policy', 0);

    // Step 2: If no ID, return error
    if ($privacy_policy_id === 0) {
        return array(/* error details */);
    }

    // Step 3: Get the page post object
    $policy_post = get_post($privacy_policy_id);

    // Step 4: If page doesn't exist or not published, return error
    if (!$policy_post || $policy_post->post_status !== 'publish') {
        return array(/* error details */);
    }

    // Step 5: Check if updated within 12 months
    $last_modified = strtotime($policy_post->post_modified);
    $twelve_months_ago = strtotime('-12 months');

    // Step 6: If updated recently, return null (pass)
    if ($last_modified >= $twelve_months_ago) {
        return null; // SUCCESS
    }

    // Step 7: If outdated, return warning
    return array(/* warning details */);
}
```

**Key Insights:**
- Returns `null` = diagnostic PASSED (no issue)
- Returns array = diagnostic FAILED (issue found)
- Tests 3 things: page exists + published + recently updated

---

## Step 2: Identify WordPress Functions Used

```
✓ get_option('wp_page_for_privacy_policy')   - Get privacy policy page ID
✓ get_post($id)                              - Get page details
✓ strtotime()                                - Parse date strings
✓ time()                                     - Current timestamp
```

All native PHP/WordPress functions - no custom logic!

---

## Step 3: Implement the Test Method

Create scenarios that validate the `check()` method:

### BEFORE: Stub Version
```php
public static function test_live_privacy_policy_current(): array {
    // TODO: Implement actual test logic
    return array(
        'passed' => false,
        'message' => 'Test not yet implemented for ' . self::$slug,
    );
}
```

### AFTER: Working Implementation

```php
public static function test_live_privacy_policy_current(): array {
    /*
     * Test Strategy:
     * 1. Get current state using check()
     * 2. Verify actual WordPress state matches
     * 3. Return test result
     */

    $result = self::check();

    // Get actual WordPress state
    $policy_id = (int) get_option('wp_page_for_privacy_policy', 0);
    $policy_exists = $policy_id > 0;

    if ($policy_exists) {
        $policy_post = get_post($policy_id);
        $is_published = $policy_post && 'publish' === $policy_post->post_status;
        $is_current = false;

        if ($is_published) {
            $last_modified = strtotime($policy_post->post_modified);
            $twelve_months_ago = strtotime('-12 months');
            $is_current = $last_modified >= $twelve_months_ago;
        }
    } else {
        $is_published = false;
        $is_current = false;
    }

    // Expected state:
    // - If all good: result should be null
    // - If any problem: result should be array
    $expected_state = $is_current ? 'pass' : 'fail';
    $actual_state = is_null($result) ? 'pass' : 'fail';

    $test_passes = ($expected_state === $actual_state);

    return [
        'passed' => $test_passes,
        'message' => sprintf(
            'Privacy policy: %s | Expected: %s | Actual: %s',
            ($policy_exists ? 'exists' : 'missing'),
            $expected_state,
            $actual_state
        )
    ];
}
```

---

## Step 4: Test Scenarios Covered

### Scenario 1: No Privacy Policy Configured
```php
// WordPress state:
update_option('wp_page_for_privacy_policy', 0);

// Expected: check() returns array (error)
// Test validates this
```

### Scenario 2: Privacy Policy Page Missing
```php
// WordPress state:
update_option('wp_page_for_privacy_policy', 99999); // Non-existent page

// Expected: check() returns array (error)
// Test validates this
```

### Scenario 3: Privacy Policy Not Published
```php
// WordPress state:
$page_id = wp_insert_post([
    'post_type' => 'page',
    'post_status' => 'draft', // Not published
    'post_title' => 'Privacy Policy'
]);
update_option('wp_page_for_privacy_policy', $page_id);

// Expected: check() returns array (error)
// Test validates this
```

### Scenario 4: Privacy Policy Outdated
```php
// WordPress state:
$page_id = wp_insert_post([
    'post_type' => 'page',
    'post_status' => 'publish',
    'post_title' => 'Privacy Policy',
    'post_date' => date('Y-m-d H:i:s', strtotime('-2 years'))
]);
update_option('wp_page_for_privacy_policy', $page_id);

// Expected: check() returns array (warning)
// Test validates this
```

### Scenario 5: Privacy Policy Up to Date ✓ (PASS)
```php
// WordPress state:
$page_id = wp_insert_post([
    'post_type' => 'page',
    'post_status' => 'publish',
    'post_title' => 'Privacy Policy',
    'post_date' => date('Y-m-d H:i:s') // Today
]);
update_option('wp_page_for_privacy_policy', $page_id);

// Expected: check() returns null
// Test validates this
```

---

## Step 5: Testing in Practice

### Local WordPress Test

```php
<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load WordPress
require_once('/var/www/html/wp-load.php');

// Set admin user
wp_set_current_user(1);

// Import diagnostic class
require_once 'includes/diagnostics/documented/compliance/class-diagnostic-privacy-policy-current.php';
use WPShadow\Diagnostics\Diagnostic_Privacy_Policy_Current;

// Test it
$test_result = Diagnostic_Privacy_Policy_Current::test_live_privacy_policy_current();

// Display result
echo json_encode($test_result, JSON_PRETTY_PRINT);

// Example output:
// {
//     "passed": true,
//     "message": "Privacy policy: exists | Expected: pass | Actual: pass"
// }
```

### Docker Test

```bash
docker exec wpshadow-test bash -c 'cd /var/www/html && php -r "
error_reporting(E_ALL);
define(\"WP_USE_THEMES\", false);
require(\"./wp-load.php\");
wp_set_current_user(1);

require_once \"includes/diagnostics/documented/compliance/class-diagnostic-privacy-policy-current.php\";
use WPShadow\Diagnostics\Diagnostic_Privacy_Policy_Current;

\$result = Diagnostic_Privacy_Policy_Current::test_live_privacy_policy_current();
echo json_encode(\$result, JSON_PRETTY_PRINT);
"'
```

---

## Step 6: Verification Checklist

Before committing implementation:

- ✅ Test method returns `['passed' => bool, 'message' => string]`
- ✅ No PHP warnings/errors in output
- ✅ Test validates all 3-5 key scenarios
- ✅ Correctly interprets null vs array from check()
- ✅ Works in WordPress context
- ✅ Uses only native WordPress functions
- ✅ No database modifications left behind after test
- ✅ Test runs in < 1 second

---

## Key Pattern (Reusable for Other Diagnostics)

This same pattern works for most diagnostics:

```php
public static function test_live_[slug](): array {
    // 1. Call check() to get diagnostic result
    $check_result = self::check();

    // 2. Get actual WordPress state (use same logic as check())
    $actual_state = /* ... WordPress functions ... */;

    // 3. Determine expected state
    $expected_pass = /* boolean based on actual_state */;

    // 4. Compare what check() returned vs what we expected
    $test_passes = (is_null($check_result) === $expected_pass);

    // 5. Return test result
    return [
        'passed' => $test_passes,
        'message' => "Description of state: $description"
    ];
}
```

---

## Implementation Time Estimate

| Step | Time |
|------|------|
| Read check() method | 3 min |
| Understand logic | 2 min |
| Write test method | 7 min |
| Test locally | 2 min |
| Debug if needed | 1 min |
| **Total** | **~15 minutes** |

---

## Common Pitfalls to Avoid

❌ **Wrong**: Calling check() and just checking if result is null/array
```php
$result = self::check();
return ['passed' => $result === null, 'message' => '...'];
// This doesn't actually TEST anything - it just repeats check()!
```

✅ **Right**: Validating check() result matches actual WordPress state
```php
$result = self::check();
$policy_id = get_option('wp_page_for_privacy_policy', 0);
$expected = ($policy_id > 0); // Should pass if policy exists
$test_passes = (is_null($result) === $expected);
return ['passed' => $test_passes, 'message' => '...'];
```

---

## What Happens Next?

Once implemented:

1. Test runs in WordPress admin (no errors)
2. Test passes on multiple site configurations
3. Test moves to `/includes/diagnostics/` (active registry)
4. Users see diagnostic result on dashboard
5. Can create treatments that fix issues found by diagnostic

---

*Total for all 9 COMPLIANCE diagnostics: ~2 hours*
