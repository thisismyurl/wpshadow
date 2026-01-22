# WPShadow Testing

This directory contains PHPUnit tests for WPShadow diagnostics.

## Running Tests

### All Tests
```bash
composer test
```

### Specific Test File
```bash
vendor/bin/phpunit tests/Diagnostics/Test_Diagnostic_Ai_Structured_Data.php
```

## Test Structure

- **bootstrap.php**: Initializes WP_Mock and loads necessary classes
- **Diagnostics/**: Contains test files for diagnostic classes

## Writing Tests

Each diagnostic test should:
1. Extend `PHPUnit\Framework\TestCase`
2. Use `WP_Mock` to mock WordPress functions
3. Include at least two tests:
   - One testing the healthy/no-issue scenario (should return empty array or null)
   - One testing the issue-detected scenario (should return a finding array)

### Example Test Structure

```php
<?php
namespace WPShadow\Tests\Diagnostics;

use PHPUnit\Framework\TestCase;
use WP_Mock;
use WPShadow\Diagnostics\Diagnostic_Example;

class Test_Diagnostic_Example extends TestCase {
    
    public function setUp(): void {
        WP_Mock::setUp();
    }
    
    public function tearDown(): void {
        WP_Mock::tearDown();
    }
    
    public function test_returns_no_finding_when_healthy() {
        // Mock WordPress functions
        WP_Mock::userFunction( 'get_option' )
            ->andReturn( 'expected_value' );
        
        $result = Diagnostic_Example::run();
        
        $this->assertEmpty( $result );
    }
    
    public function test_returns_finding_when_issue_detected() {
        // Mock WordPress functions
        WP_Mock::userFunction( 'get_option' )
            ->andReturn( null );
        
        $result = Diagnostic_Example::run();
        
        $this->assertNotEmpty( $result );
        $this->assertArrayHasKey( 'id', $result );
    }
}
```

## Continuous Integration

Tests run automatically on pull requests via GitHub Actions. See `.github/workflows/php-tests.yml` for CI configuration.

The CI pipeline tests against:
- PHP 8.0
- PHP 8.1
- PHP 8.2
