# WPShadow Diagnostics Orchestrator

## Overview

The Diagnostics Orchestrator is a PHP script that automates the implementation of stubbed diagnostic checks in the WPShadow plugin. It scans the `includes/diagnostics/` directory for diagnostic classes with stubbed implementations and:

1. Generates conservative, testable implementations using WP-safe heuristics
2. Creates matching PHPUnit test files
3. Commits changes in batches (up to 100 files per branch by default)

## Usage

### Basic Usage

```bash
# Dry run (no files modified)
php scripts/orchestrator.php --dry-run

# Run with default batch size (100 files per branch)
php scripts/orchestrator.php

# Run with custom batch size
php scripts/orchestrator.php --batch-size=50
```

### Options

- `--dry-run`: Preview what would be changed without modifying any files
- `--batch-size=N`: Set the number of files per batch/branch (default: 100)

## How It Works

### 1. Scanning for Stubbed Diagnostics

The orchestrator identifies stubbed diagnostics by looking for:
- Comments containing "STUB: Check implementation needed"
- Comments containing "Stub: full implementation pending"
- `return null;` or `return array();` with stub comments

### 2. Implementation Generation

For each stubbed diagnostic, the orchestrator:

- Extracts metadata (slug, title, description, category, family)
- Determines the appropriate category-specific baseline check:
  - Security → `security_basics_issue()`
  - Performance → `performance_basics_issue()`
  - SEO → `seo_basics_issue()`
  - Code Quality → `code_basics_issue()`
- Generates a conservative implementation using `Diagnostic_Lean_Checks::build_finding()`

### 3. Test Generation

Each diagnostic gets a corresponding PHPUnit test with:
- Test for null return when no issue detected
- Test for proper array structure when issue found
- Test for diagnostic metadata methods
- WP_Mock setup and teardown

### 4. Batch Commits

Files are committed in batches to branches named:
- `diag/copilot/batch-001`
- `diag/copilot/batch-002`
- etc.

This enables:
- Gradual rollout of implementations
- Easy review of changes
- Safe auto-merge of subsequent batches

## Example Output

```
WPShadow Diagnostics Orchestrator
==================================

Scanning for stubbed diagnostics...
Found 406 stubbed diagnostic(s)

[1/406] Processing: includes/diagnostics/seo/class-diagnostic-seo-mobile-first-indexing.php
  [OK] Updated diagnostic
  [OK] Created test: Diagnostic_Seo_Mobile_First_Indexing_Test.php

[2/406] Processing: includes/diagnostics/seo/class-diagnostic-seo-entity-recognition.php
  [OK] Updated diagnostic
  [OK] Created test: Diagnostic_Seo_Entity_Recognition_Test.php

...

==================================
Summary:
- Processed: 406 diagnostic(s)
- Updated: 406 file(s)
- Created: 406 test(s)
- Batches: 5
```

## Implementation Strategy

The generated implementations follow a conservative strategy:

1. **Lean Checks**: Use `Diagnostic_Lean_Checks` helper methods for baseline signals
2. **WP-Safe**: Only use safe WordPress functions (class_exists, get_option, etc.)
3. **Minimal**: Return null when healthy, structured array when issue detected
4. **Testable**: All implementations are fully testable with WP_Mock

## Generated Code Example

### Before (Stubbed)
```php
public static function check(): ?array {
    // STUB: Check implementation needed
    return null;
}
```

### After (Implemented)
```php
public static function check(): ?array {
    // Use lean check helper for baseline signal
    if ( ! \WPShadow\Core\Diagnostic_Lean_Checks::seo_basics_issue() ) {
        return null; // Pass - no baseline issue detected
    }

    // Build finding using helper
    return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
        'seo-mobile-first-indexing',
        'Seo Mobile First Indexing',
        'Automatically initialized lean diagnostic...',
        'seo',
        'medium',
        55,
        'seo-mobile-first-indexing'
    );
}
```

## CI Integration

The orchestrator is designed to work with the CI workflow:

1. Orchestrator creates batch branches
2. CI runs tests on each branch
3. If tests pass, branch can be auto-merged
4. Process repeats for next batch

## Requirements

- PHP 8.0+
- Git (for batch commits)
- WPShadow plugin structure with:
  - `includes/diagnostics/` directory
  - `includes/core/class-diagnostic-base.php`
  - `includes/core/class-diagnostic-lean-checks.php`

## Safety Features

- Dry-run mode for previewing changes
- Pattern matching only updates stubbed methods
- Preserves existing metadata and class structure
- Creates tests for validation
- Batch commits allow rollback if needed

## Future Enhancements

Potential improvements:
- More sophisticated implementation generation based on diagnostic name/description
- Integration with WPShadow's treatment system
- Automated PR creation for batches
- Enhanced test coverage with more scenarios
- Support for updating existing implementations
