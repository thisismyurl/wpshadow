# Real Diagnostic Testing Protocol

## Workflow

Only diagnostics with real, functional, world-accurate tests move to `tests/diagnostics/`.
Diagnostics requiring implementation go to `tests/diagnostics-todo/`.

### Validation Process

For each diagnostic, before moving to tests:

1. **Understand the check() logic**
   - Read the check() method fully
   - Identify what WordPress state it evaluates
   - Note all functions/options it reads

2. **Create real test logic**
   - Simulate or read actual WordPress state using native functions
   - Compare diagnostic result against expected state
   - Return true/false based on alignment

3. **Test both scenarios** (when applicable)
   - Healthy site state (check() returns null)
   - Unhealthy site state (check() returns array)
   - Edge cases (missing config, unpublished, etc.)

4. **Syntax validation**
   - `php -l` passes with no errors
   - All WordPress functions exist
   - No undefined variables

5. **Move to tests/**
   - Only after all validations pass
   - Copy to `tests/diagnostics/{category}/`

### Implementation Priority

**Phase 1: COMPLIANCE (9 files)** ✅ Starting
- All have well-defined WordPress options/posts
- Privacy policy, CCPA, GDPR, Cookie consent, ToS, Data retention, User deletion/export, WCAG

**Phase 2: SECURITY (20+ files)**
- Plugin/option checks
- Force strong passwords, 2FA, audit logging, rate limiting

**Phase 3: SYSTEM (30 files)**
- Disk space, memory, cron, backups
- Server-level checks (some may need server access)

**Phase 4: MARKETING (14 files)**
- AB testing, analytics, tracking

**Phase 5: GENERAL (499 files)**
- Largest category
- AI, compatibility, feature-specific checks

**Phase 6: CODE-QUALITY, MONITORING, PERFORMANCE, etc.**
- Continue with available diagnostics

**Unstestable diagnostics → diagnostics-todo/**
- Server-level (CPU monitoring, load average)
- Require shell/system access
- Require external services
- Can't determine pass/fail from WordPress alone

## Current Status

**Implemented & Moved:**
- ✅ privacy-policy-current (compliance)

**Next:**
- ccpa-compliance
- cookie-consent
- gdpr-compliance
- (continue compliance batch)

**Awaiting implementation:**
- All security, system, marketing, general, etc. (2,384 files)

## Example Implementation Template

```php
public static function test_live_[slug](): array {
    // Get diagnostic result
    $result = self::check();

    // Determine expected state (what should check() return?)
    $condition_met = /* evaluate actual WordPress state */;
    $should_return_error = $condition_met; // or !$condition_met based on diagnostic

    // Compare result against expectation
    $diagnostic_returned_error = !is_null($result);
    $test_passes = ($should_return_error === $diagnostic_returned_error);

    return array(
        'passed' => $test_passes,
        'message' => $test_passes ? 'Check aligned with site state' :
            "Mismatch: expected " . ($should_return_error ? 'error' : 'pass'),
    );
}
```

---

**Goal:** Every test in `tests/diagnostics/` is real, functional, and achieves its diagnostic goal.
