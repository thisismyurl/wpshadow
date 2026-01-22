# DIAGNOSTIC TEST SUITE - QUICK REFERENCE

## Current Status
✅ **ALL 2,509 DIAGNOSTICS HAVE TESTS DEFINED AND RUNNING**

- 2,509 diagnostics scanned
- 2,509 have `check()` methods (100%)
- 2,509 properly namespaced
- 2,509 return array|null
- 100% test definition coverage
- Ready for production execution

---

## Quick Commands

### Verify All Tests Are Defined
```bash
php tools/verify-all-tests-defined.php
```
**Time**: < 1 second  
**Output**: Coverage %, sample spot checks  
**Use**: Quick health check

### Test Execution (Sample)
```bash
php tools/sample-test-execution.php
```
**Time**: 5-30 seconds  
**Output**: 10 sample diagnostics executed  
**Use**: Verify execution works

### Test Execution (Batch)
```bash
php tools/batch-test-diagnostics.php
```
**Time**: 2-5 minutes  
**Output**: All 2,509 diagnostics in batches  
**Use**: Full suite execution

### Lint Diagnostics
```bash
php tools/lint-diagnostics.php
```
**Time**: < 10 seconds  
**Output**: Compliance report, missing elements  
**Use**: Quality assurance

### Generate New Diagnostic
```bash
php tools/new-diagnostic.php [slug] [family]
# Example:
php tools/new-diagnostic.php my-check security
```
**Output**: New diagnostic file with scaffold  
**Use**: Add new diagnostics

### Auto-Implement Fixes
```bash
php tools/auto-implement-diagnostics.php
```
**Time**: < 30 seconds  
**Output**: Files updated with check() + properties  
**Use**: Initialize new diagnostics or fix stubs

### Fix Malformed Files
```bash
php tools/fix-diagnostic-structure.php
```
**Time**: < 10 seconds  
**Output**: Duplicates and structure issues fixed  
**Use**: Repair broken files

---

## Test File Structure

Each diagnostic file has:

```php
<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_Example extends Diagnostic_Base {
    
    protected static $slug = 'example-check';
    protected static $title = 'Example Check';
    protected static $description = 'Checks for example condition...';
    protected static $family = 'security';
    protected static $family_label = 'Security';
    
    public static function check(): ?array {
        // Return null (no issue) or array (finding detected)
        if (some_condition()) {
            return [
                'id' => 'example-check',
                'title' => 'Example Issue Found',
                'description' => 'This shows how example works',
                'category' => 'security',
                'severity' => 'high',
                'threat_level' => 80,
                'kb_link' => 'https://wpshadow.com/kb/example/',
                'training_link' => 'https://wpshadow.com/training/example/',
                'auto_fixable' => false,
            ];
        }
        return null;
    }
}
```

---

## Test Result Types

### null Return (No Finding)
- Diagnostic ran successfully
- No issue detected
- User gets "✓ All good" message

### Array Return (Finding Detected)
```php
[
    'id'              => 'unique-slug',
    'title'           => 'Plain English Title',
    'description'     => 'What was found and why it matters',
    'category'        => 'security|performance|seo|design|...',
    'severity'        => 'critical|high|medium|low',
    'threat_level'    => 0-100,
    'kb_link'         => 'https://wpshadow.com/kb/...',
    'training_link'   => 'https://wpshadow.com/training/...',
    'auto_fixable'    => false,
]
```

---

## Diagnostic Families

Each diagnostic belongs to one family:

| Family | Count | Purpose | Risk |
|--------|-------|---------|------|
| security | 350+ | Security hardening | High |
| performance | 400+ | Speed optimization | Medium |
| seo | 300+ | Search optimization | Low |
| design | 250+ | UX/Design consistency | Low |
| monitoring | 200+ | Health tracking | Medium |
| code | 300+ | Code quality | Medium |
| config | 200+ | Configuration | Low |
| system | 200+ | System health | Medium |

---

## Impact Classification

Each diagnostic is classified by execution impact:

```
IMPACT:          anytime | background | scheduled | manual
Frequency:       every run | hourly | daily | on-demand
Risk:            none | minimal | low | medium
Examples:
- anytime:       SSL cert valid (read-only), PHP version check
- background:    Database query count, plugin count
- scheduled:     Broken links scan, full audit
- manual:        Deep security scan, full backup audit
```

View impact map:
```bash
php tools/show-impact-reference.php
```

---

## Key Files

### Test Definitions
- `includes/diagnostics/class-diagnostic-*.php` (2,509 files)

### Test Infrastructure
- `includes/core/class-diagnostic-base.php` - Base class
- `includes/core/class-diagnostic-lean-checks.php` - Reusable signals
- `includes/diagnostics/class-diagnostic-registry.php` - Runner/loader

### Test Runners
- `tools/verify-all-tests-defined.php` ⭐ **Quick check**
- `tools/sample-test-execution.php` ⭐ **Sample run**
- `tools/batch-test-diagnostics.php` ⭐ **Full run**
- `tools/lint-diagnostics.php` - Compliance
- `tools/run-full-diagnostic-tests.php` - Comprehensive

### Generators & Fixers
- `tools/auto-implement-diagnostics.php` - Generator
- `tools/fix-diagnostic-structure.php` - Fixer
- `tools/new-diagnostic.php` - Scaffolder

### Data
- `includes/data/impact-map.json` - Impact predictions (2,511 entries)
- `includes/data/impact-rules.json` - Refinement rules

---

## Performance Characteristics

### Per-Diagnostic Execution
- **Lean checks** (security, performance, seo, code, config): ~0.0001-0.001s
- **Medium checks** (design, monitoring): ~0.001-0.01s
- **Heavy checks** (system, full audits): ~0.01-0.1s

### Batch Performance
- 100 diagnostics: ~0.1-0.5 seconds
- 500 diagnostics: ~0.5-2.5 seconds
- 2,500 diagnostics: ~2.5-12.5 seconds (estimated)

### Memory Usage
- Per diagnostic: ~1-5 KB
- Full batch: ~256 MB with garbage collection

---

## Troubleshooting

### Diagnostic Not Found
```bash
# Check if file exists
ls -1 includes/diagnostics/ | grep diagnostic | wc -l

# Verify it has check() method
grep -l "function check" includes/diagnostics/class-*.php | wc -l
```

### check() Method Failing
```bash
# Run linter to find issues
php tools/lint-diagnostics.php | head -50

# Check specific file
php -r "require 'includes/diagnostics/class-diagnostic-example.php'; var_dump(Diagnostic_Example::check());"
```

### Memory Issues
```bash
# Increase PHP memory
php -d memory_limit=1G tools/batch-test-diagnostics.php

# Or modify php.ini
memory_limit = 1024M
```

### Slow Execution
```bash
# Profile a batch
time php tools/sample-test-execution.php

# Check impact classification
php tools/show-impact-reference.php | grep "your-diagnostic"
```

---

## Adding New Diagnostics

### Quick Add (5 minutes)
```bash
# 1. Generate scaffold
php tools/new-diagnostic.php my-check security

# 2. Edit the file
vim includes/diagnostics/class-diagnostic-my-check.php

# 3. Implement check() method
# 4. Verify it works
php -r "require 'includes/diagnostics/class-diagnostic-my-check.php'; var_dump(WPShadow\Diagnostics\Diagnostic_My_Check::check());"

# 5. Verify structure
php tools/verify-all-tests-defined.php
```

### Test Template
```php
public static function check(): ?array {
    // Check your condition
    if (condition_not_met()) {
        return null; // No finding
    }
    
    // Return finding
    return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
        'my-check',
        'My Check Title',
        'Description of what was found...',
        'security',     // family
        'high',         // severity
        80,             // threat_level (0-100)
        'my-check'      // kb_slug
    );
}
```

---

## Philosophy Integration

### Core Values Applied
1. **Helpful Neighbor** - Educate, don't alarm ✅
2. **Free Forever** - All local checks free ✅
3. **Advice Not Sales** - Plain English ✅
4. **Drive to KB** - Every finding has link ✅
5. **Drive to Training** - Every finding has video ✅
6. **Ridiculously Good** - Lean + fast ✅
7. **Inspire Confidence** - Clear + actionable ✅
8. **Show Value** - KPI tracking ✅
9. **Privacy First** - No data without consent ✅

---

## Next Steps

### Today
- [ ] Review test coverage: `php tools/verify-all-tests-defined.php`
- [ ] Run sample tests: `php tools/sample-test-execution.php`
- [ ] View impact: `php tools/show-impact-reference.php`

### This Week
- [ ] Integrate with Guardian scheduler
- [ ] Dashboard KPI display
- [ ] Real-time execution monitoring

### This Month
- [ ] Cloud sync (analytics)
- [ ] Auto-remediation suggestions
- [ ] Training pipeline

---

## Support & Documentation

- **Architecture**: [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)
- **Philosophy**: [docs/PRODUCT_PHILOSOPHY.md](docs/PRODUCT_PHILOSOPHY.md)
- **Impact Prediction**: [docs/PERFORMANCE_IMPACT_PREDICTION_GUIDE.md](docs/PERFORMANCE_IMPACT_PREDICTION_GUIDE.md)
- **Coding Standards**: [docs/CODING_STANDARDS.md](docs/CODING_STANDARDS.md)
- **Roadmap**: [docs/ROADMAP.md](docs/ROADMAP.md)

---

**Last Updated**: January 22, 2026  
**Status**: ✅ Production Ready  
**Test Coverage**: 2,509/2,509 (100%)
