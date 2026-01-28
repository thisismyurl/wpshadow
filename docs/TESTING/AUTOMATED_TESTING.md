# WPShadow Automated Testing Suite

**Version:** 1.0  
**Date:** January 26, 2026  
**Status:** ✅ Complete  
**Test Framework:** PHPUnit 11.0+

---

## 📋 Overview

Comprehensive automated test suite covering unit tests, integration tests, and WCAG 2.1 Level AA accessibility compliance.

### Test Coverage Summary

| Test Suite | Files | Tests | Status |
|------------|-------|-------|--------|
| **Unit Tests** | 2 | 16 | ✅ Ready |
| **Integration Tests** | 1 | 10 | ✅ Ready |
| **Accessibility Tests** | 1 | 7 | ✅ Ready |
| **Total** | 4 | 33 | ✅ Complete |

---

## 🚀 Quick Start

### Run All Tests

```bash
./run-tests.sh
```

### Run Specific Test Suites

```bash
# Unit tests only
./run-tests.sh --unit

# Integration tests only  
./run-tests.sh --integration

# Accessibility tests only
./run-tests.sh --accessibility

# With code coverage
./run-tests.sh --coverage

# Verbose output
./run-tests.sh --verbose
```

### Using Composer

```bash
composer test
```

---

## 📁 Test Structure

```
tests/
├── bootstrap.php                    # Test environment setup
├── TestCase.php                     # Base test class with helpers
├── Unit/
│   ├── DiagnosticBaseTest.php      # 8 diagnostic system tests
│   └── TreatmentBaseTest.php       # 8 treatment system tests
├── Integration/
│   └── FeatureIntegrationTest.php  # 10 integration tests
└── Accessibility/
    └── WCAGComplianceTest.php      # 7 WCAG compliance tests
```

---

## 🧪 Test Suites

### 1. Unit Tests (16 tests)

**DiagnosticBaseTest** - 8 tests:
- ✅ Diagnostic instantiation
- ✅ Returns null when no issues
- ✅ Returns valid finding structure
- ✅ Family grouping functionality
- ✅ Metadata getters (slug, title, description)
- ✅ Hook execution
- ✅ Severity level validation
- ✅ Threat level range validation (0-100)

**TreatmentBaseTest** - 8 tests:
- ✅ Treatment instantiation
- ✅ Valid result structure
- ✅ Success result handling
- ✅ Failure result handling
- ✅ Dry run mode
- ✅ Backup creation
- ✅ Capability checking
- ✅ Error handling with additional data

### 2. Integration Tests (10 tests)

**FeatureIntegrationTest** - 10 tests:
- ✅ Plugin constants defined (WPSHADOW_VERSION, WPSHADOW_PATH, etc.)
- ✅ Version format validation (1.YDDD.HHMM in Toronto time)
- ✅ Diagnostic/treatment file pairing
- ✅ File structure integrity
- ✅ Required files exist
- ✅ Namespace consistency (WPShadow\\)
- ✅ PSR-4 autoloader configuration
- ✅ Documentation completeness
- ✅ Workflow builder assets exist
- ✅ CSS syntax validation

### 3. Accessibility Tests (7 tests)

**WCAGComplianceTest** - 7 tests:
- ✅ Color contrast ratios (gray-500+ for text: 5.14:1)
- ✅ No gray-400 for text (3.86:1 fails WCAG AA)
- ✅ warning-dark for icons (4.6:1+ passes)
- ✅ WCAG documentation in CSS header
- ✅ ARIA labels in templates
- ✅ Form label associations
- ✅ Touch targets ≥44x44px
- ✅ Reduced motion support

---

## 📊 Test Results

### Expected Output

```
======================================================================
WPShadow Test Suite
======================================================================

----------------------------------------------------------------------
Running Unit Tests
----------------------------------------------------------------------

Test files found: 2

PHPUnit 11.0.0 by Sebastian Bergmann and contributors.

................                                                 16 / 16 (100%)

✓ Unit tests passed

----------------------------------------------------------------------
Running Integration Tests
----------------------------------------------------------------------

Test files found: 1

..........                                                       10 / 10 (100%)

✓ Integration tests passed

----------------------------------------------------------------------
Running Accessibility Tests
----------------------------------------------------------------------

Test files found: 1

.......                                                          7 / 7 (100%)

✓ Accessibility tests passed

======================================================================
Test Summary
======================================================================

Test Suites Run:    3
Test Suites Passed: 3
Test Suites Failed: 0

✓ All tests passed!
```

---

## ✅ What Gets Tested

### Diagnostic System
- ✅ Diagnostic class instantiation
- ✅ Finding structure validation
- ✅ Threat levels (0-100 range)
- ✅ Severity levels (low, medium, high, critical)
- ✅ Auto-fixable flag
- ✅ Family grouping
- ✅ Metadata access
- ✅ Hook firing (before/after check)

### Treatment System
- ✅ Treatment class instantiation
- ✅ Result structure validation
- ✅ Success/failure handling
- ✅ Dry run mode
- ✅ Backup creation before changes
- ✅ Capability checks
- ✅ Error handling
- ✅ Before/after data tracking

### Integration
- ✅ Plugin constants defined
- ✅ Version number format
- ✅ Diagnostic/treatment pairing
- ✅ Directory structure
- ✅ Required files present
- ✅ Namespace consistency
- ✅ Autoloader configuration
- ✅ Documentation completeness
- ✅ Asset files exist
- ✅ CSS syntax validity

### Accessibility (WCAG 2.1 Level AA)
- ✅ Text contrast ≥4.5:1
- ✅ UI component contrast ≥3:1
- ✅ ARIA labels present
- ✅ ARIA roles defined
- ✅ Keyboard navigation support
- ✅ Touch targets ≥44x44px
- ✅ Reduced motion support
- ✅ Form label associations

---

## 🔧 Installation

### Prerequisites

- PHP 8.1+
- Composer

### Install Dependencies

```bash
cd /workspaces/wpshadow
composer install
```

This installs:
- PHPUnit 11.0
- PHP_CodeSniffer 3.8
- PHPStan 1.10
- WordPress Coding Standards 3.0

---

## 📖 Usage Examples

### Run All Tests

```bash
./run-tests.sh
```

### Run with Coverage

```bash
./run-tests.sh --coverage
```

Opens HTML report: `tests/coverage/index.html`

### Run Single Test File

```bash
vendor/bin/phpunit tests/Unit/DiagnosticBaseTest.php
```

### Run Single Test Method

```bash
vendor/bin/phpunit --filter testDiagnosticReturnsValidFinding
```

### Verbose Output

```bash
./run-tests.sh --verbose
```

---

## 🏗️ Writing New Tests

### Create Test File

```php
<?php
namespace WPShadow\Tests\Unit;

use WPShadow\Tests\TestCase;

class MyFeatureTest extends TestCase {
    
    public function testMyFeature(): void {
        // Arrange
        $input = 'test';
        
        // Act
        $result = my_feature($input);
        
        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

### Use Helper Assertions

```php
// Validate diagnostic finding
$this->assertValidFinding($finding);

// Validate treatment result
$this->assertValidTreatmentResult($result);

// Standard PHPUnit assertions
$this->assertTrue($value);
$this->assertEquals($expected, $actual);
$this->assertArrayHasKey('key', $array);
```

---

## 🚨 Troubleshooting

### PHPUnit Not Found

```bash
composer install
```

### Permission Denied

```bash
chmod +x run-tests.sh
```

### Class Not Found

```bash
composer dump-autoload
```

### Coverage Not Generating

Install Xdebug:
```bash
pecl install xdebug
```

---

## 📈 Continuous Integration

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      
      - name: Install Dependencies
        run: composer install
      
      - name: Run Tests
        run: ./run-tests.sh
```

---

## 📚 Resources

**PHPUnit:** https://phpunit.de/  
**WCAG 2.1:** https://www.w3.org/WAI/WCAG21/quickref/  
**WPShadow Docs:** [ARCHITECTURE.md](ARCHITECTURE.md), [CODING_STANDARDS.md](CODING_STANDARDS.md)

---

## 🎯 Success Criteria

Tests are passing when:

- ✅ All 33 tests pass
- ✅ No PHP errors or warnings
- ✅ Code coverage >80% (target: 90%)
- ✅ All WCAG AA checks pass
- ✅ No regressions detected

---

**Status:** ✅ All test suites ready  
**Maintained by:** WPShadow Development Team  
**Last Updated:** January 26, 2026
