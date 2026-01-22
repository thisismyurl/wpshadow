# Diagnostic Implementation Orchestrator

## Overview

The orchestrator automates the implementation and testing of diagnostic checks in the WPShadow plugin. It scans for stub implementations, generates conservative WordPress-safe code, creates unit tests, and manages batch PR creation.

## Features

- **Automatic Stub Detection**: Identifies diagnostics that return `null`, empty arrays, or have TODO markers
- **Conservative Implementation**: Generates safe, WordPress-compatible diagnostic checks using existing helper functions
- **Unit Test Generation**: Creates PHPUnit tests with Brain Monkey for WordPress function mocking
- **Batch Processing**: Organizes changes into manageable batches (default: 100 files per PR)
- **Git Integration**: Automates branch creation, commits, and prepares for PR creation
- **CI Integration**: Works with GitHub Actions workflow for automatic testing and merging

## Prerequisites

1. PHP 8.0 or higher
2. Composer dependencies installed (`composer install`)
3. Git repository initialized
4. GitHub CLI (`gh`) for PR creation (optional)

## Usage

### Basic Usage

```bash
# Dry run to see what would be processed
php scripts/orchestrator.php --dry-run

# Process all stub files and create batches
php scripts/orchestrator.php

# Custom batch size
php scripts/orchestrator.php --batch-size=50

# Custom branch prefix
php scripts/orchestrator.php --branch-prefix=diag/automated/batch-
```

### Options

- `--dry-run`: Preview what would be processed without making changes
- `--batch-size=N`: Number of files per batch/PR (default: 100)
- `--branch-prefix=PREFIX`: Prefix for branch names (default: diag/copilot/batch-)

## Implementation Strategy

The orchestrator generates implementations based on diagnostic categories:

### Security Diagnostics
Uses `Diagnostic_Lean_Checks::security_basics_issue()` to check for common security misconfigurations like file editing enabled in wp-admin.

### Performance Diagnostics
Uses `Diagnostic_Lean_Checks::performance_basics_issue()` to detect performance opportunities like missing object cache.

### SEO Diagnostics
Uses `Diagnostic_Lean_Checks::seo_basics_issue()` to identify SEO problems like search engine visibility disabled.

### Compatibility Diagnostics
Checks PHP version compatibility and WordPress requirements.

### Generic Diagnostics
Falls back to safe constant checks for other categories.

## Generated Test Structure

Each diagnostic gets two tests:
1. **Positive test**: Asserts diagnostic returns `null` when no issues present
2. **Negative test**: Asserts diagnostic returns findings array when issues detected

Example test structure:
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

## Workflow

1. **Scan**: Identifies stub diagnostics in `includes/diagnostics/`
2. **Generate**: Creates conservative implementations using helper functions
3. **Test**: Generates unit tests in `tests/diagnostics/` mirroring the structure
4. **Batch**: Groups files into batches (default 100 files per batch)
5. **Commit**: Creates branches and commits each batch
6. **Push**: Branches ready to push to GitHub
7. **PR**: Create PRs (manual or automated with GitHub CLI)
8. **Auto-merge**: CI workflow will auto-merge when tests pass

## Post-Orchestrator Steps

After running the orchestrator:

```bash
# Push all created branches
git push origin --all

# Create PRs using GitHub CLI (requires gh CLI)
for branch in $(git branch | grep 'diag/copilot/batch-'); do
    gh pr create \
        --title "Diagnostic Implementations: Batch $(echo $branch | grep -oP 'batch-\K\d+')" \
        --body "Automated implementation of diagnostic stubs with unit tests. Auto-merges when CI passes." \
        --base main \
        --head $branch
done
```

## CI Integration

The `.github/workflows/ci.yml` workflow:
- Runs on all PRs
- Tests against PHP 8.0, 8.1, 8.2, 8.3
- Runs PHPUnit tests
- Runs PHPCS and PHPStan (non-blocking)
- Auto-merges PRs created by github-actions[bot] when tests pass

## Safety Features

- **Conservative Implementation**: No network calls, no filesystem operations, no database writes
- **WordPress-Safe**: Uses only WordPress core functions and existing helpers
- **Testable**: All implementations are unit-testable without WordPress bootstrap
- **Batch Limit**: Prevents overwhelming the review process
- **Dry Run**: Preview before making changes

## Troubleshooting

### "No stub files found"
- Check that diagnostic files contain `return null;` or `return array();` in their `check()` method
- Verify files are in `includes/diagnostics/` directory

### "Failed to update implementation"
- Check file permissions
- Verify class structure matches expected pattern

### "Error creating branch"
- Ensure you're on a clean working tree
- Check you have permission to create branches

## Example Output

```
WPShadow Diagnostic Orchestrator
================================

Configuration:
  Dry Run: No
  Batch Size: 100 files per PR
  Branch Prefix: diag/copilot/batch-

Scanning for stub diagnostic files...

Found 1287 total diagnostic files
Found 856 stub files to implement

Organizing into 9 batches of up to 100 files each

=== Processing Batch 1 (100 files) ===

Processing: security/class-diagnostic-audit-logging.php
  Class: Diagnostic_Audit_Logging
  Category: security
  ✓ Updated implementation
  ✓ Created test file

[... continues for all files ...]

✓ Created branch and committed changes

=== Orchestrator Complete ===
Processed 856 stub files across 9 batches
```

## Architecture

The orchestrator consists of several key functions:

- `find_diagnostic_files()`: Recursively finds all PHP files
- `is_stub_file()`: Detects stub patterns in files
- `extract_diagnostic_metadata()`: Parses class information
- `generate_implementation()`: Creates category-specific implementations
- `generate_test_file()`: Creates PHPUnit test files
- `process_batch()`: Handles a batch of files
- `create_branch_and_commit()`: Git operations for each batch

## Contributing

When modifying the orchestrator:

1. Test with `--dry-run` first
2. Start with small batch sizes for testing
3. Verify generated code follows WordPress coding standards
4. Ensure tests can run without WordPress bootstrap

## License

GPL-2.0-or-later (matches WPShadow plugin license)
