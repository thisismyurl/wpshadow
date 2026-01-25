# WP Shadow Development Environment

## 🚀 Quick Start

1. Open this repository in GitHub Codespaces or VS Code with Dev Containers
2. Wait for the environment to build (3-5 minutes)
3. Access WordPress at http://localhost:8080
4. Default credentials: admin/admin

## 🌐 Available Services

- **WordPress**: http://localhost:8080 (admin/admin)
- **phpMyAdmin**: http://localhost:8081 (wordpress/wordpress)
- **MySQL**: localhost:3306 (root/rootpass or wordpress/wordpress)

## 🐛 Debug Plugins (Pre-installed)

The following debug plugins are automatically installed and activated:

- **Query Monitor** - Performance debugging and SQL query analysis
- **Debug Bar** - PHP and WordPress debugging information
- **WP Crontrol** - Cron event management and debugging
- **User Switching** - Quickly switch between user accounts

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
# Install WordPress test suite
bash bin/install-wp-tests.sh wordpress_test root rootpass db latest true

# Run PHPUnit tests
composer test

# Run specific test
vendor/bin/phpunit tests/test-sample.php

# Check PHP compatibility
phpcs -p . --standard=PHPCompatibilityWP
```

### Static Analysis
```bash
# Run PHPStan
composer phpstan

# Or directly
phpstan analyse
```

### Test Data Generation
```bash
# Generate test users, posts, pages, and comments
bash .devcontainer/generate-test-data.sh
```

### Build Release Package
```bash
# Create WordPress.org-ready ZIP file
bash .devcontainer/build-release.sh
# Output: build/wpshadow-{version}.zip
```

## 🎯 Philosophy Integration

This environment is designed to support WPShadow's core principles:

### 11 Commandments
Helpful Neighbor • Free as Possible • Advice Not Sales • Drive to KB • Drive to Training • Ridiculously Good for Free • Inspire Confidence • Everything Has KPI • Beyond Pure Privacy • Talk-About-Worthy • Accessibility & Inclusivity

### 3 CANON Pillars
🌍 **Accessibility First** • 🎓 **Learning Inclusive** • 🌐 **Culturally Respectful**

📋 **Checklists:**
- [PHILOSOPHY_CHECKLIST.md](./PHILOSOPHY_CHECKLIST.md) - Full 11 Commandments checklist
- [ACCESSIBILITY_TESTING.md](./ACCESSIBILITY_TESTING.md) - CANON testing guide

## ✅ WordPress.org Submission Checklist

- [ ] All code passes `phpcs` with WordPress standards
- [ ] All code passes PHPCompatibilityWP for PHP 8.1+
- [ ] PHPUnit tests pass
- [ ] PHPStan analysis passes (level 8)
- [ ] All strings are internationalized with `wpshadow` text domain
- [ ] No development files in build (use `.distignore`)
- [ ] readme.txt follows WordPress.org format
- [ ] Screenshots added and documented
- [ ] Tested on WordPress 6.4+
- [ ] Accessibility audit passed (see ACCESSIBILITY_TESTING.md)
- [ ] Philosophy checklist completed (see PHILOSOPHY_CHECKLIST.md)

## Debugging

Enable Xdebug in the terminal:
```bash
echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini
```

## 💡 Useful VS Code Commands

- `F1` → "WordPress: Activate Plugin"
- `F1` → "PHP CS Fixer: Fix this file"
- `F1` → "ESLint: Fix all auto-fixable problems"

## 🔄 CI/CD Integration

GitHub Actions workflows are configured for:

- **Continuous Integration** (`.github/workflows/ci.yml`)
  - PHPCS checks across PHP 8.1-8.3
  - PHPUnit tests across WordPress 6.4-latest
  - Accessibility validation
  - Privacy checks

- **Automated Releases** (`.github/workflows/release.yml`)
  - Build WordPress.org-ready packages
  - Create GitHub releases
  - Generate release notes

## 🎓 Learning Resources

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [WPShadow Knowledge Base](https://wpshadow.com/kb/)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)

## 🆘 Troubleshooting

### WordPress not loading?
```bash
# Check container status
docker-compose ps

# Restart services
docker-compose restart

# View logs
docker-compose logs -f wordpress
```

### Permission issues?
```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/html/wp-content/plugins/wpshadow
```

### Plugin not activating?
```bash
# Manually activate via WP-CLI
wp plugin activate wpshadow --allow-root
```

---

**Built with ❤️ following the WPShadow Philosophy**
