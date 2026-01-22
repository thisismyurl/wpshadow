# Diagnostics Orchestrator

The diagnostics orchestrator is a tool that automatically scans for stubbed diagnostic classes and generates conservative implementations with matching PHPUnit tests.

## Usage

Run the orchestrator from the repository root:

```bash
php scripts/orchestrator.php
```

## What it does

1. **Scans for stubbed diagnostics**: Finds diagnostic classes in `includes/diagnostics/` with stubbed `check()` or `run()` methods
   - Detects patterns like `return array(); // Stub`
   - Looks for TODO/STUB comments
   - Identifies "full implementation pending" comments

2. **Generates implementations**: Creates conservative, WP-safe implementations
   - Uses `Diagnostic_Lean_Checks` helper methods for family-specific checks
   - Implements security checks (DISALLOW_FILE_EDIT)
   - Implements SEO checks (blog_public option)
   - Implements performance checks (object cache usage)
   - Implements configuration checks (timezone_string)
   - Falls back to safe option checks for other families

3. **Creates PHPUnit tests**: Generates matching test files under `tests/diagnostics/`
   - Tests positive scenarios (no issue detected)
   - Tests negative scenarios (issue detected)
   - Validates finding structure
   - Uses WP_Mock to mock WordPress functions

4. **Batching**: Processes up to 100 files per batch
   - Branch naming: `diag/copilot/batch-{number}`
   - Designed for safe auto-merge workflows

## Example output

```
🔍 Scanning for stubbed diagnostics...
📊 Found 406 stubbed diagnostics
🔨 Generating implementations and tests...
  ✓ Updated Diagnostic_Security_File_Edit_Disabled
  ✓ Created test for Diagnostic_Security_File_Edit_Disabled
📦 Batch 1 complete with 100 files
✅ Done!
```

## Running tests

After the orchestrator generates implementations and tests:

```bash
composer test
```

This will run all PHPUnit tests including the newly generated ones.

## CI Integration

The CI workflow (`.github/workflows/ci.yml`) automatically runs tests on:
- PHP 8.0
- PHP 8.1
- PHP 8.2

Tests must pass on all versions before merging.
