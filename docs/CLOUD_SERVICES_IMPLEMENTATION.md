# Cloud Services Implementation - Complete Guide

## Overview

WPShadow now includes comprehensive cloud services architecture supporting 15 external services that require offsite hosting for technical reasons.

## Architecture Components

### Client Side (WPShadow Plugin)

**Location**: `/workspaces/wpshadow/`

**Key Files**:
1. `includes/integration/cloud/class-cloud-service-connector.php` - API communication layer
2. `includes/views/tools/cloud-registration.php` - Registration interface
3. `includes/admin/ajax/class-cloud-registration-handler.php` - AJAX handlers
4. `includes/views/tools/*.php` - 15 individual tool interfaces
5. `includes/screens/class-utilities-page-module.php` - Updated with cloud utilities

**Functionality**:
- Site registration with cloud.wpshadow.com
- API key management
- Service interfaces and dashboards
- Usage tracking display
- "Why External?" educational content

### Server Side (WPShadow Cloud Services Plugin)

**Location**: `/workspaces/wpshadow/build/wpshadow-cloud-services/`

**Key Files**:
1. `wpshadow-cloud-services.php` - Main plugin file
2. `includes/class-api-router.php` - REST API routing
3. `includes/class-authentication.php` - API key verification
4. `includes/class-database.php` - Database schema
5. `includes/class-usage-tracker.php` - Usage tracking and limits
6. `includes/class-rate-limiter.php` - Rate limiting
7. `includes/services/class-uptime-monitor.php` - Example service implementation
8. `includes/services/*.php` - 14 additional services (to be implemented)

**Functionality**:
- Site registration and authentication
- 15 cloud services
- Usage tracking and enforcement
- Rate limiting
- Cron job execution
- Email notifications

## Implementation Status

### ✅ Complete

1. **Cloud Service Connector** (`class-cloud-service-connector.php`)
   - Registration with email + site URL
   - API key storage
   - Authenticated API requests
   - Free tier limit definitions
   - Error handling

2. **Registration Interface** (`cloud-registration.php`)
   - Educational "Why do you need registration?" section
   - Technical explanations for each service type
   - Free tier limits display
   - Registration form with email + site URL
   - Usage dashboard when registered
   - Disconnect option

3. **AJAX Handlers** (`class-cloud-registration-handler.php`)
   - Registration handler with nonce verification
   - Deregistration handler
   - Both extend AJAX_Handler_Base for security

4. **Utilities Catalog** (`class-utilities-page-module.php`)
   - 15 cloud utilities added with `requires_cloud => true` flag
   - Grouped by family: monitoring, ai, security, performance, seo
   - Free tier info included in descriptions

5. **Example Tool: Uptime Monitor** (`uptime-monitor.php`)
   - Complete interface with status dashboard
   - Settings form for alerts and thresholds
   - 30-day uptime visualization
   - "Why External?" explanation
   - Registration check with redirect

6. **Server Plugin Foundation** (`wpshadow-cloud-services.php`)
   - Plugin initialization
   - Service file loading
   - Cron job registration (5min, daily, weekly)
   - Activation/deactivation hooks

7. **API Router** (`class-api-router.php`)
   - `/register` endpoint with API key generation
   - `/deregister` endpoint
   - `/usage/stats` endpoint
   - Service route registration stubs

8. **Authentication** (`class-authentication.php`)
   - API key verification from X-WPShadow-API-Key header
   - Site ID extraction
   - Tier determination (free/pro)
   - Last seen timestamp updates

9. **Database Schema** (`class-database.php`)
   - wpshadow_cloud_sites table
   - wpshadow_cloud_usage table
   - 7 service-specific tables (uptime, ssl, domains, malware, blacklist, performance, keywords)
   - dbDelta-based creation
   - Drop tables method

10. **Usage Tracker** (`class-usage-tracker.php`)
    - Track service usage by site/service/month
    - Check if within free tier limits
    - Get usage stats endpoint
    - Free tier limit definitions

11. **Rate Limiter** (`class-rate-limiter.php`)
    - Transient-based rate limiting
    - Configurable window and max requests
    - Get remaining requests helper

12. **Uptime Monitor Service** (`class-uptime-monitor.php`)
    - Complete server-side implementation
    - `/uptime/status`, `/uptime/history`, `/uptime/settings` endpoints
    - Cron execution with wp_remote_get checks
    - Email alerts on downtime
    - Database logging

### 🔄 Partially Complete (Templates/Stubs)

1. **Cloud Utility Template** (`_cloud-utility-template.php`)
   - Complete template with all patterns
   - Ready to copy for remaining 14 utilities

2. **Service Implementations** (14 remaining)
   - Stubs in API router
   - Need individual class files:
     - class-ssl-monitor.php
     - class-domain-monitor.php
     - class-ai-content-optimizer.php
     - class-ai-image-alt.php
     - class-ai-spam-detection.php
     - class-malware-scanner.php
     - class-blacklist-monitor.php
     - class-ddos-detection.php
     - class-global-performance.php
     - class-keyword-tracker.php
     - class-link-checker.php
     - class-ai-writing-assistant.php
     - class-ai-translation.php
     - class-ai-chatbot.php

### ⏳ Not Started

1. **Local Tool Interfaces** (14 remaining)
   - Need to create based on template:
     - ssl-monitor.php
     - domain-monitor.php
     - ai-content-optimizer.php
     - ai-image-alt.php
     - ai-spam-detection.php
     - external-malware-scanner.php
     - blacklist-monitor.php
     - ddos-detection.php
     - global-performance.php
     - keyword-tracker.php
     - external-link-checker.php
     - ai-writing-assistant.php
     - ai-translation.php
     - ai-chatbot.php

2. **Admin Dashboard** (cloud.wpshadow.com)
   - Site management interface
   - Usage statistics
   - Service health monitoring
   - Customer support tools

3. **Email Templates**
   - Welcome email on registration
   - Downtime alerts
   - SSL expiration warnings
   - Usage limit warnings
   - Monthly usage reports

## How to Complete Remaining Work

### Step 1: Create Remaining Local Tool Interfaces

For each of the 14 remaining services:

1. Copy `/workspaces/wpshadow/includes/views/tools/_cloud-utility-template.php`
2. Rename to service slug (e.g., `ssl-monitor.php`)
3. Replace placeholders:
   - `{SERVICE_SLUG}` → actual slug (e.g., `ssl_monitor`)
   - `{SERVICE_TITLE}` → human-readable title (e.g., `SSL Certificate Monitor`)
   - `{DESCRIPTION}` → service description
   - `{WHY_EXTERNAL}` → technical explanation
4. Customize the interface:
   - Settings form fields
   - Status display
   - Data visualization
5. Test registration flow and API calls

### Step 2: Implement Server-Side Services

For each of the 14 remaining services:

1. Create file in `/workspaces/wpshadow/build/wpshadow-cloud-services/includes/services/`
2. Use `class-uptime-monitor.php` as template
3. Implement:
   - `register_routes()` - REST API endpoints
   - `execute()` - Cron job execution
   - Helper methods for actual service logic
4. Register routes in main plugin file
5. Test endpoints with cURL

### Step 3: Testing Checklist

For each service:

- [ ] Local tool interface loads without errors
- [ ] Registration check works
- [ ] Registration redirects properly
- [ ] API calls succeed with valid key
- [ ] API calls fail with invalid key
- [ ] Usage tracking increments correctly
- [ ] Free tier limits enforced
- [ ] Rate limiting works
- [ ] Cron jobs execute
- [ ] Database logging works
- [ ] Error handling graceful

### Step 4: Deployment

**Client Plugin (WPShadow):**
```bash
cd /workspaces/wpshadow
./deploy-ftp.sh
```

**Server Plugin (WPShadow Cloud Services):**
1. Package plugin: `zip -r wpshadow-cloud-services.zip build/wpshadow-cloud-services/`
2. Upload to cloud.wpshadow.com via WordPress admin
3. Activate plugin
4. Verify database tables created
5. Test registration endpoint

## Free Tier Limits Reference

| Service | Free Tier |
|---------|-----------|
| Uptime Monitor | 1 site, unlimited checks |
| SSL Monitor | 1 site, 1 check/day |
| Domain Monitor | 3 domains |
| AI Content Optimizer | 50 analyses/month |
| AI Image Alt | 100 images/month |
| AI Spam Detection | 1000 checks/month |
| Malware Scanner | 1 scan/week |
| Blacklist Monitor | 1 site, 1 check/week |
| DDoS Detection | Basic monitoring |
| Global Performance | 5 locations, 3 tests/day |
| Keyword Tracker | 10 keywords |
| Link Checker | 500 URLs/month |
| AI Writing Assistant | 10 requests/day |
| AI Translation | 10,000 words/month |
| AI Chatbot | 100 conversations/month |

## API Usage Examples

### Registration
```php
$response = Cloud_Service_Connector::register(
    'user@example.com',
    'https://example.com'
);
// Returns: ['api_key' => '...', 'free_tier' => [...]]
```

### API Request
```php
$response = Cloud_Service_Connector::request(
    '/uptime/status',
    array(),
    'GET'
);
// Returns: ['success' => true, 'status' => 'up', ...]
```

### Check Registration
```php
if ( Cloud_Service_Connector::is_registered() ) {
    // Show service interface
} else {
    // Redirect to registration
}
```

## Educational Content

Each tool includes a "Why does this require external hosting?" section explaining the technical reasoning:

**Monitoring**: "Your site can't detect its own downtime..."
**AI Services**: "AI requires expensive GPU servers ($500-2000/month)..."
**Security**: "Security scans must be independent..."
**Global Testing**: "Requires distributed servers worldwide..."

This aligns with WPShadow's "Helpful Neighbor" philosophy - educating users rather than just saying "you need this."

## Upgrade Path

When users hit free tier limits:

1. Show friendly message explaining limit
2. Explain what Pro offers (no limits)
3. Link to upgrade page (not pushy sales)
4. Offer alternative: "Here's how to do this yourself..."

## Support & Documentation

- KB Article: "Why do some WPShadow features require registration?"
- KB Article: "Understanding WPShadow Cloud Services"
- KB Article: "Free vs Pro: Cloud Services Comparison"
- Video Tutorial: "Setting up WPShadow Cloud Services"

## Next Steps

1. **Immediate**: Complete remaining 14 local tool interfaces
2. **Next**: Implement remaining 14 server-side services
3. **Then**: Test each service end-to-end
4. **Finally**: Deploy to production and create KB articles

## Questions or Issues?

Contact: Christopher Ross (thisismyurl@gmail.com)
Repository: https://github.com/thisismyurl/wpshadow
