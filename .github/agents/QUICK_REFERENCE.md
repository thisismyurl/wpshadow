# WPShadow Quick Reference Card

## 🚀 Getting Started

```bash
# Clone both repositories
cd /workspaces

# Start Docker environment
cd /workspaces/wpshadow
./docker-setup.sh

# Access WordPress: http://localhost:8000 (admin/admin)
```

## 📁 File Structure

```
/workspaces/
├── wpshadow/              # FREE plugin (WordPress.org)
│   ├── wpshadow.php       # Main plugin file
│   ├── features/          # 66+ free features
│   ├── includes/          # Core classes
│   ├── docker-compose.yml # Testing environment
│   └── docker-setup.sh    # Automated setup
│
└── wpshadow-pro/          # PRO plugin (Self-hosted)
    ├── wpshadow-pro.php   # Main pro file
    ├── features/          # Premium features
    └── includes/          # Pro classes
```

## 🔍 Quick Checks

### WordPress.org Compliance (FREE ONLY)
```bash
# Check for violations
grep -r "phone.home\|tracking\|analytics" /workspaces/wpshadow/
grep -r "http://" /workspaces/wpshadow/assets/  # No CDN URLs
grep -r "eval\|base64_decode" /workspaces/wpshadow/
```

### Code Quality (BOTH)
```bash
cd /workspaces/wpshadow
composer phpcs      # WordPress standards
composer phpstan    # Static analysis
composer test       # PHPUnit tests
```

## 🐳 Docker Commands

```bash
# Start/Stop
docker-compose up -d              # Start all services
docker-compose stop               # Stop (keep data)
docker-compose down -v            # Stop and delete data

# Testing
docker exec wpshadow-dev wp plugin list --allow-root
docker exec wpshadow-dev wp plugin activate wpshadow --allow-root
docker exec wpshadow-dev wp plugin activate wpshadow-pro --allow-root

# Logs
docker-compose logs -f            # All logs
docker exec wpshadow-dev tail -f /var/www/html/wp-content/debug.log
```

## 📝 Code Patterns

### Free Plugin Feature
```php
<?php declare(strict_types=1);
namespace WPShadow\CoreSupport;

final class WPSHADOW_Feature_Example extends WPSHADOW_Abstract_Feature {
    public function register_hooks(): void {
        // NO external APIs!
        // NO upselling!
        add_action('wp_loaded', [$this, 'init']);
    }
}
```

### Pro Plugin Feature
```php
<?php declare(strict_types=1);
namespace WPShadow\Pro;

final class WPSHADOW_Feature_Premium extends WPSHADOW_Abstract_Feature {
    public function register_hooks(): void {
        // External APIs OK
        // License validation OK
        add_action('wp_loaded', [$this, 'init']);
    }
}
```

## ✅ Pre-Commit Checklist

### Free Plugin (`wpshadow`)
- [ ] No external API calls
- [ ] No aggressive upselling
- [ ] All assets local (no CDN)
- [ ] GPL license headers
- [ ] No obfuscated code

### Pro Plugin (`wpshadow-pro`)
- [ ] Requires free plugin
- [ ] `Requires Plugin: wpshadow`
- [ ] License validation present

### Both Plugins
- [ ] `composer phpcs` passes
- [ ] `composer phpstan` passes
- [ ] `composer test` passes
- [ ] Docker tested
- [ ] Security: sanitize/escape

## 🎯 Feature Placement

| Feature Type | Plugin |
|-------------|--------|
| Core diagnostics | FREE |
| Health checks | FREE |
| Basic security | FREE |
| Emergency recovery | FREE |
| **Page caching** | **PRO** |
| **CDN integration** | **PRO** |
| **Malware scanner** | **PRO** |
| **Image optimizer** | **PRO** |
| **License system** | **PRO** |

## 🔐 Security Patterns

```php
// Input sanitization
$value = sanitize_text_field($_POST['input']);
$url = esc_url_raw($_POST['url']);

// Output escaping
echo esc_html($text);
echo esc_attr($attribute);
echo esc_url($url);

// Nonce verification
if (!wp_verify_nonce($_POST['_wpnonce'], 'action_name')) {
    wp_die('Security check failed');
}

// Capability check
if (!current_user_can('manage_options')) {
    wp_die('Insufficient permissions');
}
```

## 📊 Testing Workflow

```bash
# 1. Start Docker
./docker-setup.sh

# 2. Make code changes in VS Code

# 3. Test immediately (live reload)
# Visit: http://localhost:8000

# 4. Check logs
docker exec wpshadow-dev tail -f /var/www/html/wp-content/debug.log

# 5. Run tests
composer test

# 6. Commit
git add .
git commit -m "feat: description"
```

## 🆘 Troubleshooting

### Plugins not showing
```bash
docker exec wpshadow-dev ls -la /var/www/html/wp-content/plugins
docker exec wpshadow-dev wp plugin list --allow-root
```

### Permission errors
```bash
docker exec wpshadow-dev chown -R www-data:www-data /var/www/html/wp-content
```

### Database issues
```bash
docker-compose restart mysql
docker exec wpshadow-dev wp db check --allow-root
```

### Fatal errors
```bash
docker exec wpshadow-dev tail -n 100 /var/www/html/wp-content/debug.log
```

## 📚 Resources

- [ARCHITECTURE_SUMMARY.md](ARCHITECTURE_SUMMARY.md) - Detailed architecture
- [wpsupport-agent.md](wpsupport-agent.md) - Complete agent guide
- [DOCKER_README.md](../../DOCKER_README.md) - Docker documentation
- [WordPress.org Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/)

---

**Last Updated**: January 16, 2026
