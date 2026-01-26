# GitHub Issue Generator Scripts

This directory contains scripts for automatically generating GitHub issues for WPShadow diagnostic stub implementations.

## Overview

WPShadow has 713 diagnostic files, of which 418 are stubs (minimal implementations) that need to be completed. These scripts help create detailed GitHub issues for each stub diagnostic to track implementation work.

## Scripts

### 1. `generate-diagnostic-issues.py`
Main script that creates GitHub issues via the GitHub API.

**Location:** `dev-tools/generate-diagnostic-issues.py`

**Usage:**
```bash
python3 dev-tools/generate-diagnostic-issues.py [OPTIONS]

Options:
  --batch N       Create only N issues at a time (default: all)
  --start N       Start from issue number N (default: 1)
  --filter TEXT   Only create issues matching pattern
  --dry-run       Show what would be created without creating issues
  --repo OWNER/NAME   GitHub repository (default: thisismyurl/wpshadow)
```

**Examples:**
```bash
# Dry run to see what would be created
python3 dev-tools/generate-diagnostic-issues.py --dry-run --batch 10

# Create first 10 issues
python3 dev-tools/generate-diagnostic-issues.py --batch 10

# Create issues 11-20
python3 dev-tools/generate-diagnostic-issues.py --batch 10 --start 11

# Create issues matching "security"
python3 dev-tools/generate-diagnostic-issues.py --filter security
```

**Requirements:**
- Python 3.6+
- `GITHUB_TOKEN` environment variable set
- curl installed

### 2. `create-issues-batch.sh`
Convenient wrapper script for batch processing.

**Usage:**
```bash
./create-issues-batch.sh [start_number] [batch_size]
```

**Examples:**
```bash
# Create 10 issues starting from #1
./create-issues-batch.sh 1 10

# Create 20 issues starting from #51
./create-issues-batch.sh 51 20

# Default: 10 issues from #1
./create-issues-batch.sh
```

### 3. `show-diagnostic-progress.py`
Display current implementation progress.

**Location:** `dev-tools/show-diagnostic-progress.py`

**Usage:**
```bash
python3 dev-tools/show-diagnostic-progress.py
```

**Output:**
```
📊 WPShadow Diagnostic Implementation Progress
==================================================
Total Diagnostics:      713
Implemented:            295 (41.4%)
Stubs Remaining:        418 (58.6%)
==================================================
```

## Issue Structure

Each generated issue includes:

### Header Information
- File path and name
- Class name and namespace
- Auto-fixable status
- Stub implementation notice

### Implementation Requirements
- Core functionality checklist
- Expected data structure with example
- Testing requirements
- Documentation standards
- Security & coding standards

### Testing Scenarios
- Positive detection
- Negative detection (no false positives)
- Edge cases

### Implementation Steps
1. Research what the diagnostic should detect
2. Design the detection logic
3. Implement the check() method
4. Test all scenarios
5. Complete documentation
6. Code review for standards compliance

### Related Files
- Links to diagnostic file
- Links to treatment file (if auto-fixable)
- Links to test file location

## Labels Applied

Each issue is automatically labeled with:
- `diagnostics` - Indicates diagnostic work
- `stub-implementation` - Marks as stub needing implementation
- `needs-implementation` - Actionable status

## Stub Detection

A file is considered a stub if it:
1. Contains `@stub` or `stub implementation` in comments
2. Has a `check()` method that returns only `null`
3. Has a `check()` method with minimal implementation (< 50 chars)
4. Contains `TODO` markers in the check method

## Environment Setup

### Required Environment Variables

```bash
# GitHub Personal Access Token with repo permissions
export GITHUB_TOKEN="ghp_your_token_here"
```

### Getting a GitHub Token

1. Go to GitHub Settings → Developer settings → Personal access tokens
2. Generate new token (classic)
3. Select scopes: `repo` (full control)
4. Copy token and set as environment variable

## Batch Processing Strategy

For 418 stub diagnostics:

```bash
# Create all issues in batches of 20
for i in {1..418..20}; do
  ./create-issues-batch.sh $i 20
  sleep 2  # Avoid rate limiting
done
```

Or manually:
```bash
./create-issues-batch.sh 1 20     # Issues 1-20
./create-issues-batch.sh 21 20    # Issues 21-40
./create-issues-batch.sh 41 20    # Issues 41-60
# ... continue
```

## Completed Work

As of January 26, 2026:
- ✅ **418 GitHub issues created** (issues #693-#1138)
- ✅ All stub diagnostics have tracking issues
- ✅ Issues include comprehensive implementation guides

## Issue Numbers

Created issues span: **#693 - #1138**

View all issues:
```bash
gh issue list --label "stub-implementation" --limit 500
```

## Architecture Reference

See main documentation:
- [ARCHITECTURE.md](../../docs/ARCHITECTURE.md) - System architecture
- [FEATURE_MATRIX_DIAGNOSTICS.md](../../docs/FEATURE_MATRIX_DIAGNOSTICS.md) - All diagnostics
- [CODING_STANDARDS.md](../../docs/CODING_STANDARDS.md) - Code standards

## Contributing

When implementing a stub diagnostic:

1. Find your issue in GitHub (search by diagnostic name)
2. Self-assign the issue
3. Follow the implementation checklist in the issue
4. Reference the issue in your PR: `Fixes #ISSUE_NUMBER`
5. Ensure all checkboxes are completed before marking as done

## Troubleshooting

### "GITHUB_TOKEN not found"
Set the environment variable:
```bash
export GITHUB_TOKEN="your_token_here"
```

### Rate Limiting
If you hit GitHub API rate limits, add delays:
```bash
# Add sleep between batches
for i in {1..418..10}; do
  ./create-issues-batch.sh $i 10
  sleep 5
done
```

### Duplicate Issues
The script doesn't check for existing issues. To avoid duplicates:
1. Use `--dry-run` first
2. Check existing issues before running
3. Keep track of where you left off

## Support

For questions or issues with these scripts:
- Check [ARCHITECTURE.md](../../docs/ARCHITECTURE.md) for system design
- Review existing issues with `stub-implementation` label
- Refer to [PRODUCT_PHILOSOPHY.md](../../docs/PRODUCT_PHILOSOPHY.md) for WPShadow principles
