# WP Shadow Development Environment

## Quick Start

1. Open this repository in GitHub Codespaces or VS Code with Dev Containers
2. Wait for the environment to build (3-5 minutes)
3. Access WordPress at http://localhost:8080
4. Default credentials will be shown in the terminal

## Available Services

- **WordPress**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081 (user: `wordpress`, pass: `wordpress`)
- **MySQL**: localhost:3306

## Development Tools

### WP-CLI
```bash
wp plugin list
wp user list
wp db query "SHOW TABLES"
```

### Coding Standards
```bash
# Check coding standards
phpcs

# Auto-fix coding standards
phpcbf

# Check specific file
phpcs includes/core/class-plugin.php

# List installed standards
phpcs -i
```

### Testing
```bash
# Run PHPUnit tests (when configured)
composer test

# Check PHP compatibility
phpcs -p . --standard=PHPCompatibilityWP
```

### Static Analysis
```bash
# Run PHPStan
phpstan analyse
```

## WordPress.org Submission Checklist

- [ ] All code passes `phpcs` with WordPress standards
- [ ] All code passes PHPCompatibilityWP for PHP 8.1+
- [ ] All strings are internationalized with `wpshadow` text domain
- [ ] No development files in build (use `.distignore`)
- [ ] readme.txt follows WordPress.org format
- [ ] Screenshots added and documented
- [ ] Tested on WordPress 6.4+

## Debugging

Enable Xdebug in the terminal:
```bash
echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini
```

## Useful VS Code Commands

- `F1` → "WordPress: Activate Plugin"
- `F1` → "PHP CS Fixer: Fix this file"
- `F1` → "ESLint: Fix all auto-fixable problems"
