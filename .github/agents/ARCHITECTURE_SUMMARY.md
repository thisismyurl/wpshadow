# WPShadow Architecture Summary

## Two-Plugin Structure

### 🆓 Free Plugin: `wpshadow`
**Location**: `/workspaces/wpshadow/`  
**Distribution**: WordPress.org Plugin Repository  
**License**: GPL v2+

#### Must Comply With WordPress.org Guidelines:
- ✅ 100% GPL-licensed code only
- ✅ No external API calls (except WordPress.org)
- ✅ No phone-home or tracking code
- ✅ No aggressive upselling or fake warnings
- ✅ All assets must be included (no CDN loading)
- ✅ No obfuscated or encoded code
- ✅ Sanitize inputs, escape outputs
- ✅ Security best practices

#### Features:
- Core WordPress health diagnostics
- Emergency recovery tools
- Backup verification
- Documentation management
- Core integrity checks
- Basic security features

#### Technical:
- **Namespace**: `WPShadow\CoreSupport`
- **Text Domain**: `plugin-wpshadow`
- **Main File**: `wpshadow.php`
- **66+ Features**: All in `features/` directory

---

### 💎 Pro Plugin: `wpshadow-pro`
**Location**: `/workspaces/wpshadow-pro/`  
**Distribution**: Self-hosted, Private Repository  
**License**: Commercial

#### No WordPress.org Restrictions:
- ✅ External API calls allowed (license validation, etc.)
- ✅ Marketing and upgrade prompts allowed
- ✅ External service integrations
- ✅ CDN usage for assets
- ✅ Commercial licensing model
- ✅ Premium support systems

#### Features:
- Advanced performance optimization
- Page caching
- CDN integration
- Malware scanning
- Auto-rollback
- Database cleanup
- Advanced security (firewall, 2FA)
- Image optimization
- Critical CSS generation
- Script deferral

#### Technical:
- **Namespace**: `WPShadow\Pro`
- **Text Domain**: `wpshadow-pro`
- **Main File**: `wpshadow-pro.php`
- **Requires**: `wpshadow` (free plugin) must be active
- **Header**: `Requires Plugin: wpshadow`

---

## Docker Testing Environment

### Quick Start
```bash
cd /workspaces/wpshadow
./docker-setup.sh
```

### Services
| Service | Port | Credentials | URL |
|---------|------|-------------|-----|
| WordPress | 8000 | admin / admin | http://localhost:8000 |
| phpMyAdmin | 8080 | wordpress / wordpress | http://localhost:8080 |
| MySQL | 3306 | wordpress / wordpress | (internal) |

### Volume Mounts
```
/workspaces/wpshadow     → /var/www/html/wp-content/plugins/wpshadow
/workspaces/wpshadow-pro → /var/www/html/wp-content/plugins/wpshadow-pro
```

### Testing Workflow
1. Start: `docker-compose up -d`
2. Setup: `./docker-setup.sh` (first time)
3. Develop: Edit files in VS Code
4. Test: Refresh browser (live changes)
5. Check logs: `docker exec wpshadow-dev tail -f /var/www/html/wp-content/debug.log`
6. Stop: `docker-compose stop`
7. Clean: `docker-compose down -v` (deletes all data)

---

## Development Guidelines

### Free Plugin (`wpshadow`)
1. ✅ Check WordPress.org compliance before committing
2. ✅ No premium features - keep it simple
3. ✅ No external dependencies
4. ✅ Focus on core WordPress health and diagnostics

### Pro Plugin (`wpshadow-pro`)
1. ✅ Always check free plugin is active first
2. ✅ Premium features only
3. ✅ Can use external services
4. ✅ Advanced functionality and optimization

### Both Plugins
1. ✅ Follow WordPress Coding Standards
2. ✅ Run `composer phpcs && composer phpstan && composer test`
3. ✅ Test in Docker environment
4. ✅ Security: sanitize inputs, escape outputs
5. ✅ Document all code with PHPDoc blocks

---

## Key Differences

| Aspect | Free Plugin | Pro Plugin |
|--------|-------------|------------|
| **Distribution** | WordPress.org | Self-hosted |
| **License** | GPL v2+ | Commercial |
| **External APIs** | ❌ Not allowed | ✅ Allowed |
| **Upselling** | ❌ Minimal only | ✅ Allowed |
| **CDN Loading** | ❌ Not allowed | ✅ Allowed |
| **Obfuscation** | ❌ Not allowed | ✅ Allowed |
| **Premium Features** | ❌ Not allowed | ✅ Required |
| **License Validation** | ❌ Not allowed | ✅ Required |
| **Namespace** | `WPShadow\CoreSupport` | `WPShadow\Pro` |
| **Text Domain** | `plugin-wpshadow` | `wpshadow-pro` |

---

## Feature Placement Guide

### Should it go in FREE or PRO?

#### Free Plugin Features ✅
- Core WordPress diagnostics
- Basic health checks
- Simple security hardening
- Emergency recovery
- Backup verification
- Core file integrity
- Basic optimization (disable features)
- Documentation management

#### Pro Plugin Features 💎
- Advanced caching (page cache, object cache)
- CDN integration
- Image optimization
- Malware scanning
- Firewall
- Two-factor authentication
- Database optimization/cleanup
- Auto-rollback system
- Performance monitoring
- Critical CSS generation
- Script deferral/minification
- License management

**Rule of Thumb**: If it's complex, requires external services, or provides significant competitive advantage → Pro Plugin

---

## Pre-Commit Checklist

### Free Plugin
- [ ] No external API calls
- [ ] No aggressive upselling
- [ ] All assets included locally
- [ ] GPL license headers
- [ ] WordPress.org compliant
- [ ] `composer phpcs && composer phpstan && composer test`
- [ ] Docker test: `./docker-setup.sh`

### Pro Plugin
- [ ] Checks for free plugin activation
- [ ] `Requires Plugin: wpshadow` in header
- [ ] License validation works
- [ ] `composer phpcs && composer phpstan && composer test`
- [ ] Docker test with both plugins active

---

## Resources

- **WordPress.org Guidelines**: https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
- **Docker Setup Guide**: [DOCKER_README.md](../../DOCKER_README.md)
- **Testing Guide**: [TESTING.md](../../TESTING.md)
- **Agent Instructions**: [wpsupport-agent.md](wpsupport-agent.md)

---

**Last Updated**: January 16, 2026
