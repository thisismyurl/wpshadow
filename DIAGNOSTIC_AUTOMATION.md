# WPShadow Diagnostic Pipeline Automation

Complete automation system for implementing and testing diagnostic checks in the WPShadow plugin.

## Overview

This automation pipeline:

1. ✅ **Scans** for stub diagnostic implementations
2. ✅ **Generates** conservative, WordPress-safe implementations
3. ✅ **Creates** unit tests with Brain Monkey mocking
4. ✅ **Batches** changes into manageable PRs (100 files each)
5. ✅ **Integrates** with CI for automatic testing
6. ✅ **Auto-merges** PRs when all tests pass

## Quick Start

### Prerequisites

```bash
# Install dependencies
composer install

# Verify PHP version
php --version  # Should be 8.0+

# (Optional) Install GitHub CLI for PR creation
# See: https://cli.github.com/
gh --version
```

### Basic Workflow

```bash
# 1. Run orchestrator in dry-run mode to preview
php scripts/orchestrator.php --dry-run

# 2. Run orchestrator to generate implementations and tests
php scripts/orchestrator.php --batch-size=100

# 3. Push all batch branches to GitHub
git push origin --all

# 4. Create PRs (requires gh CLI)
./scripts/create-batch-prs.sh

# 5. Monitor CI and auto-merge
gh pr list --label diagnostics
```

## Components

### 1. Testing Infrastructure

**Location:** `/tests/`, `/phpunit.xml`, `/tests/bootstrap.php`

- PHPUnit 9.5+ configuration
- Brain Monkey for WordPress function mocking
- Test structure mirrors `includes/diagnostics/`
- No WordPress bootstrap required

**Run tests:**
```bash
composer test
# or
vendor/bin/phpunit
```

### 2. CI Workflow

**Location:** `.github/workflows/ci.yml`

- Tests on PHP 8.0, 8.1, 8.2, 8.3
- Runs PHPUnit, PHPCS, PHPStan
- Auto-merges PRs from github-actions[bot] when tests pass
- Caches composer dependencies

**Trigger:** Runs on all PRs and pushes to `main`

### 3. Orchestrator Script

**Location:** `scripts/orchestrator.php`

Main automation engine that:
- Scans `includes/diagnostics/` for stub files
- Detects patterns: `return null;`, `return array();`, `TODO`, trivial implementations
- Generates category-specific implementations (security, performance, SEO, etc.)
- Creates matching test files
- Commits in batches with descriptive messages
- Creates branches: `diag/copilot/batch-1`, `diag/copilot/batch-2`, etc.

**Usage:**
```bash
# Dry run - preview only
php scripts/orchestrator.php --dry-run

# Custom batch size
php scripts/orchestrator.php --batch-size=50

# Custom branch prefix
php scripts/orchestrator.php --branch-prefix=impl/batch-

# Full run
php scripts/orchestrator.php
```

**See:** [scripts/ORCHESTRATOR_README.md](scripts/ORCHESTRATOR_README.md) for detailed documentation.

### 4. PR Creation Script

**Location:** `scripts/create-batch-prs.sh`

Automates GitHub PR creation for all batch branches.

**Usage:**
```bash
# Default: creates PRs for diag/copilot/batch-* branches
./scripts/create-batch-prs.sh

# Custom branch prefix
./scripts/create-batch-prs.sh impl/batch-

# Custom base branch
./scripts/create-batch-prs.sh diag/copilot/batch- develop
```

## Implementation Strategy

### Conservative Implementations

All generated implementations are conservative and WordPress-safe:

- ✅ Use existing `Diagnostic_Lean_Checks` helpers
- ✅ WordPress core functions only (`get_option`, `get_transient`, `class_exists`, etc.)
- ✅ No network calls
- ✅ No filesystem writes
- ✅ No server-level checks
- ✅ Testable without WordPress bootstrap

### Category-Specific Logic

**Security:**
```php
if (\WPShadow\Core\Diagnostic_Lean_Checks::security_basics_issue()) {
    return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(...);
}
```

**Performance:**
```php
if (\WPShadow\Core\Diagnostic_Lean_Checks::performance_basics_issue()) {
    return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(...);
}
```

**SEO:**
```php
if (\WPShadow\Core\Diagnostic_Lean_Checks::seo_basics_issue()) {
    return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(...);
}
```

**Generic:**
```php
if (!defined('ABSPATH')) {
    return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(...);
}
```

### Test Structure

Each diagnostic gets two tests:

```php
public function test_check_returns_null_when_no_issues() {
    // Mock healthy state
    Functions\when('get_option')->justReturn(true);
    $result = Diagnostic_Class::check();
    $this->assertNull($result);
}

public function test_check_returns_finding_when_issues_detected() {
    // Mock problematic state
    Functions\when('get_option')->justReturn(false);
    $result = Diagnostic_Class::check();
    
    if ($result !== null) {
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
    }
}
```

## Monitoring Progress

### View Batch Branches

```bash
git branch --list 'diag/copilot/batch-*'
```

### View Created PRs

```bash
gh pr list --label diagnostics
```

### Monitor CI Status

```bash
gh pr checks <PR-NUMBER>
```

### View Auto-Merge Status

```bash
gh pr view <PR-NUMBER> --json autoMergeRequest
```

## Troubleshooting

### "No stub files found"

Check that files contain stub patterns:
```bash
grep -r "return null;" includes/diagnostics/ | wc -l
```

### "Failed to update implementation"

- Verify file permissions
- Check class structure matches expected pattern
- Review orchestrator output for specific errors

### "Tests failing in CI"

```bash
# Run tests locally
composer test

# Check specific test
vendor/bin/phpunit tests/diagnostics/path/to/Test.php

# Run with verbose output
vendor/bin/phpunit --verbose
```

### "PR not auto-merging"

- Verify CI passes: `gh pr checks <PR-NUMBER>`
- Check PR created by `github-actions[bot]`
- Review `.github/workflows/ci.yml` auto-merge configuration

## Statistics

Current status:
- **Total diagnostics:** 2,635 files
- **Stub implementations detected:** 752 files
- **Batch size:** 100 files per PR
- **Expected PRs:** ~8 batches
- **PHP versions tested:** 8.0, 8.1, 8.2, 8.3

## Architecture

```
wpshadow/
├── .github/workflows/
│   └── ci.yml                  # CI workflow with auto-merge
├── includes/
│   ├── core/
│   │   └── class-diagnostic-lean-checks.php  # Helper functions
│   └── diagnostics/            # Diagnostic implementations
│       ├── security/
│       ├── performance/
│       ├── seo/
│       └── ...
├── tests/
│   ├── bootstrap.php           # PHPUnit bootstrap
│   └── diagnostics/            # Test files (mirrors includes/)
├── scripts/
│   ├── orchestrator.php        # Main automation engine
│   ├── create-batch-prs.sh     # PR creation helper
│   └── ORCHESTRATOR_README.md  # Detailed orchestrator docs
├── composer.json               # Dependencies (phpunit, brain/monkey)
└── phpunit.xml                 # PHPUnit configuration
```

## Configuration Files

### composer.json
```json
{
  "require-dev": {
    "phpunit/phpunit": "^9.5",
    "mockery/mockery": "^1.5",
    "brain/monkey": "^2.6"
  },
  "scripts": {
    "test": "vendor/bin/phpunit"
  }
}
```

### phpunit.xml
- Bootstrap: `tests/bootstrap.php`
- Test suite: `tests/`
- Coverage: `includes/` (excluding admin)

### .github/workflows/ci.yml
- Matrix: PHP 8.0, 8.1, 8.2, 8.3
- Steps: validate, install, test, lint
- Auto-merge: Enabled for `github-actions[bot]` PRs

## Safety Features

1. **Dry-run mode:** Preview changes before applying
2. **Conservative implementations:** No risky operations
3. **Comprehensive testing:** Every implementation has tests
4. **CI gating:** PRs only merge when tests pass
5. **Batch limiting:** Manageable review scope (100 files)
6. **Rollback capability:** Each batch is a separate branch

## Contributing

When extending the automation:

1. Test with `--dry-run` first
2. Start with small `--batch-size` for testing
3. Verify generated code follows WordPress coding standards
4. Ensure tests run without WordPress bootstrap
5. Document any new patterns or heuristics

## Support

- **Orchestrator docs:** [scripts/ORCHESTRATOR_README.md](scripts/ORCHESTRATOR_README.md)
- **CI workflow:** [.github/workflows/ci.yml](.github/workflows/ci.yml)
- **PHPUnit config:** [phpunit.xml](phpunit.xml)
- **Test bootstrap:** [tests/bootstrap.php](tests/bootstrap.php)

## License

GPL-2.0-or-later (matches WPShadow plugin license)
