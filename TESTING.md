# WPShadow Plugin Testing Guide

Complete guide to setting up and running tests for WPShadow plugins.

## Quick Start

### Docker Environment Setup (Recommended)

```bash
# Start the WordPress development environment
docker-compose up -d

# Wait for containers to be healthy
docker-compose ps

# Access WordPress at http://localhost:8000
# Access phpMyAdmin at http://localhost:8080
```

**Container Details:**
- **WordPress**: `http://localhost:8000`
  - DB Host: `mysql`
  - DB Name: `wordpress`
  - DB User: `wordpress`
  - DB Password: `wordpress`
- **phpMyAdmin**: `http://localhost:8080`
- **Database**: `mysql:8.0` (container: `wpshadow-db`)

### First-Time Setup

Once WordPress is running:

```bash
# Install WordPress CLI in the container
docker exec wpshadow-dev wp --info

# Create a test admin user
docker exec wpshadow-dev wp user create testadmin test@example.com \
  --role=administrator \
  --user_pass=testpass123

# Check that plugins are detected
docker exec wpshadow-dev wp plugin list

# Activate the free plugin
docker exec wpshadow-dev wp plugin activate plugin-wpshadow

# Activate the pro plugin (if available)
docker exec wpshadow-dev wp plugin activate plugin-wpshadow-pro
```

---

## Unit Testing with PHPUnit

### Run All Tests

```bash
composer test
```

### Run Specific Test Suite

```bash
# Bootstrap tests only
composer test -- tests/unit/PluginBootstrapTest.php

# Feature registry tests
composer test -- tests/unit/FeatureRegistryTest.php

# Code quality tests
composer test -- tests/unit/CodeQualityTest.php

# Free/Pro separation tests
composer test -- tests/unit/FreeProSeparationTest.php
```

### Run Tests with Coverage

```bash
composer test -- --coverage-html=coverage/
```

View the report at `coverage/index.html`

### Test Files

#### 1. **PluginBootstrapTest.php**
Validates core plugin structure and file existence:
- Plugin files exist
- Directory structure is correct
- Key files are in place
- Free/pro separation is correct

**Run:**
```bash
composer test -- tests/unit/PluginBootstrapTest.php
```

#### 2. **FeatureRegistryTest.php**
Tests feature system integrity:
- Feature abstract class exists
- Feature interface is properly defined
- All features follow naming conventions
- Features are properly registered
- No duplicate requires

**Run:**
```bash
composer test -- tests/unit/FeatureRegistryTest.php
```

#### 3. **CodeQualityTest.php**
Enforces code standards:
- No PHP syntax errors
- Proper namespace usage
- Class naming conventions
- No deprecated functions
- `declare(strict_types=1)` usage

**Run:**
```bash
composer test -- tests/unit/CodeQualityTest.php
```

#### 4. **FreeProSeparationTest.php**
Ensures free/pro code separation:
- Pro-only files exist only in pro
- License code not in free plugin
- Plugin upgrader not in free
- Ghost features in pro only
- Module downloader in both

**Run:**
```bash
composer test -- tests/unit/FreeProSeparationTest.php
```

---

## Integration Testing (Docker)

Test plugins in real WordPress environment:

### Plugin Activation Test

```bash
# Check if plugins are detected
docker exec wpshadow-dev wp plugin list

# Activate free plugin
docker exec wpshadow-dev wp plugin activate plugin-wpshadow

# Verify activation
docker exec wpshadow-dev wp option get siteurl
```

### WordPress CLI Management

```bash
# Run WordPress CLI commands in container
docker exec wpshadow-dev wp [command]

# Examples:
docker exec wpshadow-dev wp db tables
docker exec wpshadow-dev wp user list
docker exec wpshadow-dev wp post list
docker exec wpshadow-dev wp site info
```

### Database Operations

```bash
# Access MySQL directly
docker exec -it wpshadow-db mysql -u wordpress -pwordpress wordpress

# Common queries:
# SELECT * FROM wp_options WHERE option_name LIKE '%wpshadow%';
# SELECT * FROM wp_plugins WHERE plugin_status = 'active';
```

---

## Code Quality Checks

### PHP CodeSniffer (WordPress Standards)

```bash
# Check for violations
composer phpcs

# Auto-fix violations
composer phpcbf
```

### PHPStan Static Analysis

```bash
# Run static analysis
composer phpstan

# Target level 8 for strict type checking
composer phpstan -- --level=8
```

---

## Pre-Deployment Checklist

Before committing code, run:

```bash
# 1. Unit tests
composer test

# 2. Code standards
composer phpcs

# 3. Static analysis
composer phpstan

# 4. Build/verify
docker-compose up -d
docker exec wpshadow-dev wp plugin activate plugin-wpshadow
docker exec wpshadow-dev wp option get siteurl
```

---

## Docker Management

### View Container Status

```bash
docker-compose ps
```

### View Logs

```bash
# All containers
docker-compose logs -f

# Specific container
docker-compose logs -f wordpress
docker-compose logs -f mysql
```

### Stop Services

```bash
docker-compose stop
```

### Full Cleanup

```bash
# Stop containers
docker-compose stop

# Remove containers
docker-compose down

# Remove volumes (database data)
docker-compose down -v
```

### Restart Services

```bash
docker-compose restart
```

---

## Debugging

### Enable WordPress Debug

WordPress debug is already enabled in `docker-compose.yml`. Check debug log:

```bash
docker exec wpshadow-dev tail -f /var/www/html/wp-content/debug.log
```

### Access Container Shell

```bash
# WordPress container
docker exec -it wpshadow-dev bash

# MySQL container
docker exec -it wpshadow-db bash
```

### Inspect Plugin Installation

```bash
# Check if plugins are installed correctly
docker exec wpshadow-dev ls -la /var/www/html/wp-content/plugins/

# Check plugin files
docker exec wpshadow-dev php -l /var/www/html/wp-content/plugins/plugin-wpshadow/wpshadow.php
```

---

## Writing New Tests

### Test Template

```php
<?php
namespace WPShadow\CoreSupport\Tests;

use PHPUnit\Framework\TestCase;

class YourNewTest extends TestCase {
    
    /**
     * Test description
     */
    public function test_your_feature(): void {
        $this->assertTrue( true );
    }
}
```

### Common Assertions

```php
// File checks
$this->assertFileExists( $path );
$this->assertFileDoesNotExist( $path );

// Directory checks
$this->assertDirectoryExists( $path );

// String checks
$this->assertStringContainsString( $substring, $string );
$this->assertStringNotContainsString( $substring, $string );

// Type checks
$this->assertIsArray( $value );
$this->assertIsString( $value );
$this->assertIsObject( $value );

// Comparison
$this->assertEquals( $expected, $actual );
$this->assertGreaterThan( $expected, $actual );
$this->assertLessThan( $expected, $actual );
```

### Add to composer.json

Tests in `tests/unit/` are automatically discovered by PHPUnit.

---

## Continuous Integration

To run tests in GitHub Actions, create `.github/workflows/tests.yml`:

```yaml
name: PHPUnit Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: wordpress
          MYSQL_USER: wordpress
          MYSQL_PASSWORD: wordpress
          MYSQL_ROOT_PASSWORD: root
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mysql
      
      - name: Install dependencies
        run: composer install
      
      - name: Run tests
        run: composer test
      
      - name: Run code standards
        run: composer phpcs
      
      - name: Run static analysis
        run: composer phpstan
```

---

## Troubleshooting

### Port Already in Use

```bash
# Free up port 8000
lsof -i :8000
kill -9 <PID>

# Or use different port in docker-compose.yml:
# ports:
#   - "8001:80"
```

### Database Connection Issues

```bash
# Check MySQL container health
docker-compose ps

# Restart database
docker-compose restart mysql
```

### Tests Fail with Missing Functions

Ensure `tests/bootstrap.php` is loaded. Check `phpunit.xml`:

```xml
<phpunit bootstrap="tests/bootstrap.php">
```

### Docker Out of Disk Space

```bash
docker system prune
docker volume prune
```

---

## Resources

- [PHPUnit Documentation](https://docs.phpunit.de/)
- [WordPress Plugin Development](https://developer.wordpress.org/plugins/)
- [Docker Documentation](https://docs.docker.com/)
- [WP-CLI Documentation](https://developer.wordpress.org/cli/commands/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
