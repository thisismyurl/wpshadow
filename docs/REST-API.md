# WP Support Suite - REST API Documentation

## Base URL
All API endpoints are prefixed with:
```
/wp-json/timu/v1/
```

## Authentication
All endpoints require WordPress authentication. You can authenticate using:
- WordPress nonce (for same-origin requests)
- Application passwords (recommended for external applications)
- Basic authentication (with plugin)

## Standard Response Format

### Success Response
```json
{
  "success": true,
  "data": {},
  "message": "Operation completed successfully"
}
```

### Error Response
```json
{
  "code": "error_code",
  "message": "Human-readable error message",
  "data": {
    "status": 400
  }
}
```

## Rate Limiting
Expensive operations have rate limits:
- Module installation: 5 requests per 5 minutes
- Module uninstallation: 3 requests per 5 minutes
- License registration: 5 requests per 5 minutes
- License verification: 10 requests per 5 minutes
- Vault file verification: 20 requests per 5 minutes
- Vault file restoration: 10 requests per 5 minutes
- Vault cleanup: 2 requests per hour
- Settings reset: 5 requests per 10 minutes

Rate limit exceeded responses return HTTP 429 status code.

## Pagination
List endpoints support pagination with these parameters:
- `page` (default: 1) - Page number
- `per_page` (default: 20, max: 100) - Items per page

Pagination information is returned in headers:
- `X-WP-Total` - Total number of items
- `X-WP-TotalPages` - Total number of pages

---

## Module Management Endpoints

### List Modules
List all registered modules with their status.

**Endpoint:** `GET /modules`

**Parameters:**
- `page` (optional) - Page number for pagination
- `per_page` (optional) - Items per page (max 100)
- `type` (optional) - Filter by type: `hub` or `spoke`
- `status` (optional) - Filter by status: `active`, `installed`, or `available`

**Required Permission:** `manage_options`

**Example Request:**
```bash
curl -X GET "https://example.com/wp-json/timu/v1/modules?type=hub&status=active" \
  --user username:application_password
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "slug": "vault-support-thisismyurl",
      "name": "Vault Support",
      "type": "hub",
      "installed": true,
      "status": {
        "active": true
      }
    }
  ]
}
```

---

### Get Module Status
Get detailed status information for a specific module.

**Endpoint:** `GET /modules/{slug}/status`

**Parameters:**
- `slug` (required) - Module slug

**Required Permission:** `manage_options`

**Example Request:**
```bash
curl -X GET "https://example.com/wp-json/timu/v1/modules/vault-support-thisismyurl/status" \
  --user username:application_password
```

---

### Install Module
Install a module from the catalog.

**Endpoint:** `POST /modules/{slug}/install`

**Parameters:**
- `slug` (required) - Module slug to install

**Required Permission:** `manage_options`

**Rate Limit:** 5 requests per 5 minutes

**Example Request:**
```bash
curl -X POST "https://example.com/wp-json/timu/v1/modules/vault-support-thisismyurl/install" \
  --user username:application_password
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "slug": "vault-support-thisismyurl"
  },
  "message": "Module installed successfully."
}
```

---

### Activate Module
Activate an installed module.

**Endpoint:** `POST /modules/{slug}/activate`

**Parameters:**
- `slug` (required) - Module slug to activate
- `network` (optional) - Network-wide activation for multisite (default: false)

**Required Permission:** `manage_options`

**Example Request:**
```bash
curl -X POST "https://example.com/wp-json/timu/v1/modules/vault-support-thisismyurl/activate" \
  -H "Content-Type: application/json" \
  -d '{"network": false}' \
  --user username:application_password
```

---

### Deactivate Module
Deactivate an active module.

**Endpoint:** `POST /modules/{slug}/deactivate`

**Parameters:**
- `slug` (required) - Module slug to deactivate
- `network` (optional) - Network-wide deactivation for multisite (default: false)

**Required Permission:** `manage_options`

---

### Uninstall Module
Completely remove a module.

**Endpoint:** `DELETE /modules/{slug}`

**Parameters:**
- `slug` (required) - Module slug to uninstall

**Required Permission:** `manage_options`

**Rate Limit:** 3 requests per 5 minutes

**Example Request:**
```bash
curl -X DELETE "https://example.com/wp-json/timu/v1/modules/vault-support-thisismyurl" \
  --user username:application_password
```

---

### Toggle Feature Flag
Enable or disable a module feature flag.

**Endpoint:** `PATCH /modules/{slug}/toggle/{feature}`

**Parameters:**
- `slug` (required) - Module slug
- `feature` (required) - Feature identifier
- `enabled` (required) - Boolean value

**Required Permission:** `manage_options`

**Example Request:**
```bash
curl -X PATCH "https://example.com/wp-json/timu/v1/modules/vault-support-thisismyurl/toggle/auto-backup" \
  -H "Content-Type: application/json" \
  -d '{"enabled": true}' \
  --user username:application_password
```

---

## Vault Operations Endpoints

### Get Vault Status
Get vault configuration and statistics.

**Endpoint:** `GET /vault/status`

**Required Permission:** `upload_files`

**Example Response:**
```json
{
  "success": true,
  "data": {
    "settings": {
      "enabled": true
    },
    "stats": {
      "enabled": true,
      "files_count": 42,
      "total_size": 1073741824
    }
  }
}
```

---

### List Vault Files
List all files in the vault with pagination.

**Endpoint:** `GET /vault/files`

**Parameters:**
- `page` (optional) - Page number
- `per_page` (optional) - Items per page

**Required Permission:** `upload_files`

---

### Get Vault File Details
Get detailed information about a specific vaulted file.

**Endpoint:** `GET /vault/files/{attachment_id}`

**Parameters:**
- `attachment_id` (required) - Attachment ID

**Required Permission:** `upload_files`

---

### Verify File Integrity
Verify the integrity of a vaulted file.

**Endpoint:** `POST /vault/files/{attachment_id}/verify`

**Parameters:**
- `attachment_id` (required) - Attachment ID

**Required Permission:** `manage_options`

**Rate Limit:** 20 requests per 5 minutes

---

### Restore File
Restore a file from the vault.

**Endpoint:** `POST /vault/files/{attachment_id}/restore`

**Parameters:**
- `attachment_id` (required) - Attachment ID

**Required Permission:** `manage_options`

**Rate Limit:** 10 requests per 5 minutes

---

### Get Vault Size
Get current vault size and limits.

**Endpoint:** `GET /vault/size`

**Required Permission:** `upload_files`

**Example Response:**
```json
{
  "success": true,
  "data": {
    "total_size": 1073741824,
    "files_count": 42,
    "limit": 0,
    "percentage": 0
  }
}
```

---

### Cleanup Vault
Remove orphaned files from the vault.

**Endpoint:** `POST /vault/cleanup`

**Required Permission:** `manage_options`

**Rate Limit:** 2 requests per hour

---

## License Management Endpoints

### Get License Status
Get current license registration status.

**Endpoint:** `GET /license`

**Required Permission:** `manage_options`

**Example Response:**
```json
{
  "success": true,
  "data": {
    "key": "****-****-****-****",
    "status": "valid",
    "message": "License is active",
    "checked_at": 1705176000
  }
}
```

---

### Register License
Register a new license key.

**Endpoint:** `POST /license/register`

**Parameters:**
- `license_key` (required) - License key to register

**Required Permission:** `manage_options`

**Rate Limit:** 5 requests per 5 minutes

**Example Request:**
```bash
curl -X POST "https://example.com/wp-json/timu/v1/license/register" \
  -H "Content-Type: application/json" \
  -d '{"license_key": "XXXX-XXXX-XXXX-XXXX"}' \
  --user username:application_password
```

---

### Remove License
Remove the current license registration.

**Endpoint:** `DELETE /license`

**Required Permission:** `manage_options`

---

### Verify License
Force a remote verification check of the current license.

**Endpoint:** `POST /license/verify`

**Required Permission:** `manage_options`

**Rate Limit:** 10 requests per 5 minutes

---

### Get Network License Status
Get license status for the entire network (multisite only).

**Endpoint:** `GET /license/network`

**Required Permission:** `manage_network_options`

**Multisite Only:** Yes

---

### Broadcast License
Broadcast a license key to multiple sites in a network (multisite only).

**Endpoint:** `POST /license/network/broadcast`

**Parameters:**
- `license_key` (required) - License key to broadcast
- `site_ids` (optional) - Array of site IDs (empty = all sites)
- `auto_new` (optional) - Automatically apply to new sites (default: false)

**Required Permission:** `manage_network_options`

**Rate Limit:** 3 requests per 10 minutes

**Multisite Only:** Yes

---

## Suite Configuration Endpoints

### Get Settings
Get suite settings for a module or all modules.

**Endpoint:** `GET /settings`

**Parameters:**
- `module` (optional) - Get settings for specific module only

**Required Permission:** `manage_options`

**Example Request:**
```bash
curl -X GET "https://example.com/wp-json/timu/v1/settings?module=vault-support-thisismyurl" \
  --user username:application_password
```

---

### Update Settings
Update suite settings for a module.

**Endpoint:** `PATCH /settings`

**Parameters:**
- `module` (required) - Module slug
- `settings` (required) - Object with setting key-value pairs
- `network` (optional) - Update network settings (default: false)

**Required Permission:** `manage_options`

**Example Request:**
```bash
curl -X PATCH "https://example.com/wp-json/timu/v1/settings" \
  -H "Content-Type: application/json" \
  -d '{
    "module": "vault-support-thisismyurl",
    "settings": {
      "auto_backup": true,
      "retention_days": 90
    }
  }' \
  --user username:application_password
```

---

### Reset Settings
Reset settings to defaults.

**Endpoint:** `POST /settings/reset`

**Parameters:**
- `module` (optional) - Reset specific module only (empty = reset all)
- `network` (optional) - Reset network settings (default: false)

**Required Permission:** `manage_options`

**Rate Limit:** 5 requests per 10 minutes

---

### Health Check
Fast health check endpoint for monitoring systems.

**Endpoint:** `GET /health`

**Required Permission:** None (public endpoint)

**Example Response:**
```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "timestamp": "2026-01-13 22:30:00",
    "version": "1.2601.73002",
    "checks": {
      "database": {
        "status": "ok",
        "message": "Database is accessible"
      },
      "modules": {
        "status": "ok",
        "message": "Module system is available"
      },
      "vault": {
        "status": "info",
        "message": "Vault module not installed"
      },
      "licensing": {
        "status": "ok",
        "message": "Licensing system is available"
      }
    }
  }
}
```

---

## Error Codes

Common error codes returned by the API:

- `rest_forbidden` - User lacks required permissions
- `invalid_slug` - Invalid or empty slug parameter
- `module_not_found` - Module not found in catalog
- `already_installed` - Module is already installed
- `install_failed` - Module installation failed
- `activation_failed` - Module activation failed
- `uninstall_failed` - Module uninstallation failed
- `toggle_failed` - Feature toggle failed
- `vault_not_available` - Vault module not installed
- `file_not_found` - Attachment not found
- `not_vaulted` - File is not in vault
- `verify_failed` - File verification failed
- `restore_failed` - File restoration failed
- `license_not_available` - License system not available
- `invalid_license_key` - Invalid license key format
- `registration_failed` - License registration failed
- `removal_failed` - License removal failed
- `verification_failed` - License verification failed
- `not_multisite` - Endpoint requires multisite
- `settings_not_available` - Settings system not available
- `invalid_module` - Invalid module parameter
- `invalid_settings` - Invalid settings format
- `update_failed` - Settings update failed
- `reset_failed` - Settings reset failed
- `rate_limit_exceeded` - Too many requests

---

## Use Cases

### Headless WordPress
Use the REST API to manage modules and vault operations from a headless CMS:
```javascript
// Install and activate vault module
await fetch('/wp-json/timu/v1/modules/vault-support-thisismyurl/install', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token
  }
});

await fetch('/wp-json/timu/v1/modules/vault-support-thisismyurl/activate', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token
  }
});
```

### CI/CD Deployment
Automate module deployment in your CI/CD pipeline:
```bash
# Deploy all modules
for module in vault-support media-support; do
  curl -X POST "https://staging.example.com/wp-json/timu/v1/modules/${module}/install" \
    --user deploy:$APP_PASSWORD
  curl -X POST "https://staging.example.com/wp-json/timu/v1/modules/${module}/activate" \
    --user deploy:$APP_PASSWORD
done
```

### Monitoring Integration
Use the health endpoint for uptime monitoring:
```bash
#!/bin/bash
response=$(curl -s https://example.com/wp-json/timu/v1/health)
status=$(echo $response | jq -r '.data.status')

if [ "$status" != "healthy" ]; then
  # Send alert
  echo "Site health check failed!"
  exit 1
fi
```

### Custom Dashboard
Build a custom management dashboard using React or Vue:
```javascript
// Get all modules status
const modules = await fetch('/wp-json/timu/v1/modules')
  .then(r => r.json());

// Get vault statistics
const vault = await fetch('/wp-json/timu/v1/vault/status')
  .then(r => r.json());

// Display in dashboard
return {
  modules: modules.data,
  vaultStats: vault.data.stats
};
```
