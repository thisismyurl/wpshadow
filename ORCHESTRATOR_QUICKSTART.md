# Quick Start Guide: Using the Orchestrator

After this PR is merged, follow these steps to implement the 406 stubbed diagnostics:

## Step 1: Test the Orchestrator (Dry Run)

```bash
# Preview what will be changed
php scripts/orchestrator.php --dry-run
```

This will show you all 406 stubbed diagnostics that will be processed.

## Step 2: Run First Batch

```bash
# Process first 100 diagnostics
php scripts/orchestrator.php --batch-size=100
```

This will:
1. Generate implementations for 100 diagnostics
2. Create 100 matching test files
3. Create branch `diag/copilot/batch-001`
4. Commit and push changes

## Step 3: Verify CI Passes

The CI workflow will automatically:
1. Run tests on PHP 8.0, 8.1, 8.2
2. Verify all tests pass
3. Run static analysis

Check the Actions tab in GitHub to monitor progress.

## Step 4: Review and Merge

1. Review the PR for `diag/copilot/batch-001`
2. Verify tests pass in CI
3. Merge if everything looks good

## Step 5: Continue with Remaining Batches

Repeat steps 2-4 for the remaining batches. The orchestrator will automatically:
- Create `diag/copilot/batch-002`
- Create `diag/copilot/batch-003`
- etc.

## Options

### Custom Batch Size

```bash
# Smaller batches for cautious rollout
php scripts/orchestrator.php --batch-size=50

# Larger batches for faster rollout
php scripts/orchestrator.php --batch-size=200
```

### Dry Run Before Each Batch

```bash
# Always safe to preview
php scripts/orchestrator.php --dry-run --batch-size=100
```

## What Gets Generated

For each diagnostic, the orchestrator creates:

### Implementation
```php
public static function check(): ?array {
    // Use lean check helper for baseline signal
    if ( ! \WPShadow\Core\Diagnostic_Lean_Checks::seo_basics_issue() ) {
        return null; // Pass - no baseline issue detected
    }

    // Build finding using helper
    return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
        'diagnostic-slug',
        'Diagnostic Title',
        'Description',
        'category',
        'severity',
        threat_level,
        'kb-slug'
    );
}
```

### Test
```php
class Diagnostic_Name_Test extends TestCase {
    public function test_check_returns_null_when_no_issue() { ... }
    public function test_check_returns_proper_structure_when_issue_found() { ... }
    public function test_diagnostic_metadata() { ... }
}
```

## Monitoring Progress

Track progress with:

```bash
# Count remaining stubbed diagnostics
find includes/diagnostics -name "*.php" -type f -exec grep -l "STUB\|Stub: full implementation pending" {} \; | wc -l

# List completed batches
git branch -a | grep diag/copilot/batch
```

## Expected Timeline

With default batch size of 100:
- Batch 1: 100 diagnostics
- Batch 2: 100 diagnostics
- Batch 3: 100 diagnostics
- Batch 4: 100 diagnostics
- Batch 5: 6 diagnostics

**Total: 5 batches to complete all 406 diagnostics**

## Troubleshooting

### Tests Fail
1. Check CI logs for specific failures
2. Review generated implementation
3. Adjust if needed
4. Re-run tests: `composer test`

### Git Issues
```bash
# If batch branch already exists
git checkout main
git branch -D diag/copilot/batch-NNN
git push origin --delete diag/copilot/batch-NNN

# Then re-run orchestrator
```

### Need to Customize Implementation
1. Edit the diagnostic file manually
2. Update the test if needed
3. Run `composer test` to verify
4. Commit changes

## Best Practices

1. **Always dry-run first** to preview changes
2. **Monitor CI** for each batch
3. **Review implementations** before merging
4. **Keep batch size reasonable** (50-100 files)
5. **Test locally** with `composer test` when in doubt

## Support

For issues or questions:
- Review `scripts/README.md` for detailed documentation
- Check existing test patterns in `tests/diagnostics/`
- Examine sample diagnostic: `includes/diagnostics/security/class-diagnostic-file-edit-disabled.php`

## Success Metrics

Track these metrics as you progress:
- ✅ Tests passing in CI
- ✅ Code coverage increasing
- ✅ Stubbed diagnostics decreasing
- ✅ Implemented diagnostics increasing

Target: **406/406 diagnostics implemented and tested**
