# WPShadow Development Environment

## 🎯 Philosophy

This codespace embodies WPShadow's core values:

### The 3 Foundational Pillars (CANON)
- 🌍 **Accessibility First**: Every feature works for people with disabilities
- 🎓 **Learning Inclusive**: Supports all learning styles  
- 🌐 **Culturally Respectful**: Global-first design

### The 11 Commandments
See [docs/PRODUCT_PHILOSOPHY.md](../docs/PRODUCT_PHILOSOPHY.md)

## 🚀 Quick Start

1. Open in Codespaces/Dev Containers
2. Wait for setup (3-5 minutes)
3. Access WordPress: http://localhost:8080
4. Login: admin/admin

## 🧪 Testing

### Run All Tests
```bash
composer test           # PHPUnit tests
composer phpcs          # WordPress Coding Standards
composer phpstan        # Static analysis
npm run test:a11y       # Accessibility tests
```

### Manual Testing
```bash
# Generate test data
bash .devcontainer/generate-test-data.sh

# Test as different user
wp user list --allow-root
# Use User Switching plugin to switch

# Check query performance
# Visit any page → Query Monitor shows in admin bar
```

## 📋 Pre-Commit Checklist

Before every commit, run:
```bash
# Automated checks
composer phpcs
composer phpstan

# Manual checks (see PHILOSOPHY_CHECKLIST.md)
- Keyboard navigation works
- Screen reader compatible
- Documentation updated
- KB article linked
```

## 🌍 Accessibility Testing

See [ACCESSIBILITY_TESTING.md](./ACCESSIBILITY_TESTING.md) for:
- Keyboard navigation tests
- Screen reader tests
- Color contrast checks
- Zoom tests
- Automated testing tools

## 📚 Learning Resources

### For New Contributors
- [PRODUCT_PHILOSOPHY.md](../docs/PRODUCT_PHILOSOPHY.md) - Core values
- [ACCESSIBILITY_AND_INCLUSIVITY_CANON.md](../docs/ACCESSIBILITY_AND_INCLUSIVITY_CANON.md) - Accessibility requirements
- [CODING_STANDARDS.md](../docs/CODING_STANDARDS.md) - Code style

### For Development
- [ARCHITECTURE.md](../docs/ARCHITECTURE.md) - System design
- [DIAGNOSTICS_GUIDE.md](../docs/DIAGNOSTICS_GUIDE.md) - Creating diagnostics
- [TESTING_SETUP.md](../docs/TESTING_SETUP.md) - Testing guide

## 🛠️ Development Tools

### Pre-installed Debug Plugins
- **Query Monitor**: Performance, queries, hooks
- **Debug Bar**: Debug info in admin bar
- **WP Crontrol**: Manage cron events
- **User Switching**: Quick user testing

### Command-Line Tools
```bash
wp --version           # WP-CLI
phpcs --version        # PHP_CodeSniffer
composer --version     # Composer
npm --version          # Node/npm
```

## 📊 WordPress.org Submission

When ready to submit:

### 1. Final Checks
```bash
# Run full test suite
composer test:all

# Check PHP compatibility
composer phpcs -- --standard=PHPCompatibilityWP

# Build release package
bash .devcontainer/build-release.sh
```

### 2. Verify Checklist
- [ ] All tests passing
- [ ] PHPCS clean (WordPress standards)
- [ ] PHP 8.1+ compatible
- [ ] WordPress 6.4+ compatible
- [ ] All strings translatable
- [ ] readme.txt follows WordPress.org format
- [ ] Screenshots included
- [ ] License declared (GPL v2+)
- [ ] No external dependencies (composer/npm bundled)

### 3. Submit
- Upload ZIP to https://wordpress.org/plugins/developers/add/
- Follow WordPress.org review process

## 🐛 Common Issues

### Port Already in Use
```bash
# Kill existing WordPress
docker-compose down
docker-compose up -d
```

### Permission Errors
```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/html
```

### Plugin Not Activating
```bash
# Check for PHP errors
wp plugin list --allow-root
tail -f /var/www/html/wp-content/debug.log
```

## 💡 Pro Tips

- Use Query Monitor to see all plugin hooks
- Test with Debug Mode enabled (wp-config.php)
- Generate test data for realistic testing
- Switch users to test different permission levels
- Check mobile responsiveness (DevTools device mode)

## 🤝 Contributing

See philosophy checklist before opening PRs:
- [PHILOSOPHY_CHECKLIST.md](./PHILOSOPHY_CHECKLIST.md)
- [ACCESSIBILITY_TESTING.md](./ACCESSIBILITY_TESTING.md)

All contributions must meet accessibility standards.
