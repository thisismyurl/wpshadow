# Auto Deploy Setup Guide

**Feature:** Automatic deployment from GitHub to test server  
**Status:** Available in WPShadow v1.2601+  
**Security:** Test/staging servers only - NOT for production

---

## Overview

WPShadow can automatically pull the latest code from GitHub whenever you push to the `main` branch. This creates a seamless workflow:

1. You commit and push code to GitHub
2. GitHub sends a webhook to your test server
3. WPShadow automatically pulls the latest code
4. Your test server is updated within seconds

---

## Prerequisites

### Server Requirements

- Git installed and accessible by web server user
- Plugin directory must be a git repository
- Web server user must have permissions to execute `git pull`

**Check git access:**
```bash
# As web server user (usually www-data or nginx)
sudo -u www-data git --version
sudo -u www-data git -C /path/to/wp-content/plugins/wpshadow pull --dry-run
```

### SSH Key Setup (Recommended)

For private repositories, configure SSH keys:

```bash
# Generate SSH key for web server user
sudo -u www-data ssh-keygen -t ed25519 -C "server@wpshadow.com"

# Add public key to GitHub
sudo cat /home/www-data/.ssh/id_ed25519.pub
# Copy and add to GitHub: Settings → SSH and GPG keys → New SSH key

# Test connection
sudo -u www-data ssh -T git@github.com
```

---

## Setup Steps

### 1. Enable Auto-Deploy in wp-config.php

Add this constant to enable the feature (TEST SERVERS ONLY):

```php
/**
 * Enable WPShadow Auto-Deploy (TEST SERVERS ONLY!)
 * DO NOT use on production servers
 */
define( 'WPSHADOW_AUTO_DEPLOY', true );
```

**⚠️ CRITICAL:** Only add this to test/staging servers. NEVER on production.

---

### 2. Configure Webhook in WPShadow

1. Go to **WPShadow → Auto Deploy** in WordPress admin
2. Click **"Generate Random"** to create a webhook secret
3. Click **"Save Changes"**
4. Copy the **Webhook URL** (e.g., `https://wpshadow.com/wpshadow-deploy`)
5. Copy the **Webhook Secret** (you'll need both for GitHub)

---

### 3. Configure GitHub Webhook

1. Go to your GitHub repository: **thisismyurl/wpshadow**
2. Click **Settings → Webhooks → Add webhook**
3. Configure:
   - **Payload URL:** `https://wpshadow.com/wpshadow-deploy`
   - **Content type:** `application/json`
   - **Secret:** [paste the secret from WPShadow]
   - **Which events:** Just the push event
   - **Active:** ✓ (checked)
4. Click **Add webhook**

---

### 4. Test the Webhook

GitHub will send a test ping immediately. Check:

1. **GitHub:** Settings → Webhooks → Recent Deliveries
   - Should show 200 response
2. **WPShadow:** Auto Deploy page → Recent Deployments
   - Should show the deployment attempt

**Manual Test:**
```bash
# Make a small change and commit
git commit --allow-empty -m "Test auto-deploy webhook"
git push origin main

# Watch the WPShadow Auto Deploy page for the deployment
```

---

## Security

### Webhook Signature Verification

WPShadow verifies every webhook using HMAC SHA-256:

```php
$expected_signature = 'sha256=' . hash_hmac( 'sha256', $payload, $secret );
hash_equals( $expected_signature, $hub_signature ); // Constant-time comparison
```

This prevents unauthorized deployments even if someone discovers your webhook URL.

### Best Practices

✅ **DO:**
- Use auto-deploy on test/staging servers only
- Generate a strong random webhook secret (32+ characters)
- Use SSH keys for private repositories
- Monitor deployment logs regularly
- Keep wp-config.php secure (chmod 600)

❌ **DON'T:**
- Enable on production servers
- Use weak or predictable secrets
- Share webhook URLs publicly
- Commit wp-config.php with WPSHADOW_AUTO_DEPLOY enabled
- Deploy without testing first

---

## Troubleshooting

### Webhook Returns 401 (Unauthorized)

**Problem:** GitHub signature doesn't match

**Solution:**
1. Verify secret matches in both GitHub and WPShadow
2. Check for extra spaces or line breaks in secret
3. Regenerate secret and update both locations

### Webhook Returns 500 (Server Error)

**Problem:** Git pull failed

**Solution:**
```bash
# Check git permissions
sudo -u www-data git -C /path/to/wpshadow status

# Check for uncommitted changes
cd /path/to/wpshadow
git status

# If dirty, stash changes
git stash

# Verify remote
git remote -v

# Test pull manually
sudo -u www-data git pull origin main
```

### Deployments Not Showing Up

**Problem:** Webhook not reaching server

**Solution:**
1. Check GitHub webhook recent deliveries for errors
2. Verify webhook URL is publicly accessible
3. Check server firewall rules
4. Verify DNS resolves correctly
5. Check web server access logs

### OPcache Not Clearing

**Problem:** New code not loading after deployment

**Solution:**
```php
// Add to wp-config.php
ini_set( 'opcache.revalidate_freq', 0 );

// Or manually clear after deployment
opcache_reset();
```

---

## Rewrite Rules

If the webhook endpoint returns 404, flush rewrite rules:

```php
// In WordPress admin or via WP-CLI
flush_rewrite_rules();

// Or via WP-CLI
wp rewrite flush
```

Auto-deploy registers this rewrite rule:
```php
// Maps /wpshadow-deploy to our handler
add_rewrite_rule(
    '^wpshadow-deploy/?$',
    'index.php?wpshadow_deploy=1',
    'top'
);
```

---

## Monitoring

### View Recent Deployments

**WordPress Admin:**
- Go to **WPShadow → Auto Deploy**
- Check **Recent Deployments** table

**Shows:**
- Timestamp
- Commit hash (short)
- Who pushed
- Commit message

### GitHub Webhook Logs

**GitHub:**
- Repository → Settings → Webhooks
- Click your webhook
- View **Recent Deliveries** tab

**Shows:**
- Request headers
- Payload
- Response status
- Response body

---

## Advanced Configuration

### Custom Deployment Commands

Extend the deployment process:

```php
add_action( 'wpshadow_after_deploy', function( $result ) {
    if ( $result['success'] ) {
        // Run composer install
        exec( 'cd /path/to/wpshadow && composer install --no-dev 2>&1' );
        
        // Clear WordPress cache
        wp_cache_flush();
        
        // Run database migrations
        // YourMigration::run();
    }
}, 10, 1 );
```

### Deploy to Multiple Servers

Use the same webhook secret on all test servers, and GitHub will trigger all of them simultaneously.

### Branch-Specific Deployment

Modify the handler to deploy different branches:

```php
// In class-auto-deploy.php, change:
if ( $data['ref'] !== 'refs/heads/main' ) {
    // To:
    if ( ! in_array( $data['ref'], [ 'refs/heads/main', 'refs/heads/staging' ] ) ) {
```

---

## Example Workflow

**Development Cycle:**

1. **Local Development:**
   ```bash
   git checkout -b feature/new-diagnostic
   # ... make changes ...
   git commit -m "Add new diagnostic"
   git push origin feature/new-diagnostic
   ```

2. **Pull Request:**
   - Create PR on GitHub
   - Review and merge to main

3. **Automatic Deployment:**
   - GitHub sends webhook → wpshadow.com
   - Server pulls latest code
   - Test server updated in 5-10 seconds

4. **Test on Server:**
   - Visit https://wpshadow.com/wp-admin
   - Test new feature
   - Verify in production-like environment

5. **Production Deployment:**
   - Manual deployment to production (never auto-deploy!)
   - Use proper release process

---

## Disabling Auto-Deploy

### Temporarily Disable

Comment out in wp-config.php:
```php
// define( 'WPSHADOW_AUTO_DEPLOY', true );
```

### Permanently Remove

1. Remove constant from wp-config.php
2. Delete webhook from GitHub
3. (Optional) Remove Auto Deploy submenu

---

## FAQ

**Q: Can I use this on production?**  
A: NO. Auto-deploy is for test servers only. Production should use controlled release processes.

**Q: What if deployment fails?**  
A: Check the deployment log in WPShadow admin. Common issues: permissions, merge conflicts, network issues.

**Q: Does it work with private repos?**  
A: Yes, configure SSH keys for the web server user.

**Q: Can I deploy branches other than main?**  
A: Yes, modify the branch check in `class-auto-deploy.php`.

**Q: What about database migrations?**  
A: Hook into `wpshadow_after_deploy` action to run migrations.

**Q: Is it secure?**  
A: Yes, when properly configured. Uses HMAC SHA-256 signature verification and constant-time comparison.

---

**Last Updated:** January 27, 2026  
**Version:** 1.2601.2148
