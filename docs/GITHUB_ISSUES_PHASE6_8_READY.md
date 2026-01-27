# Phase 6-8 & Quick Wins: GitHub Issues - Copy-Paste Ready

Use these exact templates to quickly create all 24 diagnostic issues. Just copy each section and paste into GitHub.

---

# PHASE 6: DATABASE INTELLIGENCE (5 Issues)

## 1️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Database Table Corruption Detection`  
Labels: `diagnostic,database,security,enhancement,phase6`

```
## Description
Implement diagnostic to detect database table corruption using MySQL CHECK TABLE and repair status commands.

## What It Checks
- Runs CHECK TABLE on WordPress tables
- Detects InnoDB errors and MyISAM issues
- Identifies fragmented tables
- Flags tables requiring REPAIR

## Why Valuable
- Data loss prevention is #1 user concern
- Users never manually check table integrity
- Early detection prevents catastrophic failures
- Auto-repairable via REPAIR TABLE

## Success Criteria
✅ Detects corrupted tables  
✅ Distinguishes error types  
✅ Shows affected table count  
✅ Auto-suggests REPAIR TABLE  
✅ KPI: "Tables repaired"  
✅ Unit tests pass (mock CHECK TABLE response)  
✅ Performance < 10 seconds for 100+ tables  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/database/class-diagnostic-table-corruption-detection.php`
- **Slug:** `database-table-corruption`
- **Category:** `database`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 90 (critical - data integrity)
- **Auto-fixable:** Yes (REPAIR TABLE via treatment)
- **KB Article:** `https://wpshadow.com/kb/database-table-corruption-detection`

## Testing Pattern
```php
// Test 1: Detects corrupted table
public function testDetectsCorruptedTable() {
    // Mock CHECK TABLE response with error
    // Run diagnostic
    // Assert finding returned with threat_level >= 85
}

// Test 2: Passes with healthy tables
public function testPassesWithHealthyTables() {
    // Mock CHECK TABLE OK response
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Extends Diagnostic_Base correctly
- [ ] Returns null when no corruption
- [ ] Returns proper finding array structure
- [ ] All metadata fields present (id, title, description, threat_level, site_health_status, auto_fixable)
- [ ] Threat level 85+ (critical)
- [ ] PHPCS standards pass
- [ ] Activity Logger KPI tracking works
- [ ] No database modifications in check()
```

---

## 2️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Slow Query Detection`  
Labels: `diagnostic,database,performance,enhancement,phase6`

```
## Description
Detect slow queries in MySQL slow query log and identify performance bottlenecks.

## What It Checks
- Reads MySQL slow_query_log if enabled
- Identifies queries exceeding slow_query_time threshold
- Shows query count and execution patterns
- Suggests query optimization

## Why Valuable
- Performance bottlenecks are invisible to users
- Identifies database efficiency issues
- Provides data-driven optimization targets
- Helps prevent scaling problems

## Success Criteria
✅ Detects slow queries  
✅ Shows query patterns  
✅ Explains performance impact  
✅ Suggests MySQL configuration  
✅ KPI: "Slow queries optimized"  
✅ Unit tests pass (mock slow query log)  
✅ Graceful handling when slow logs disabled  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/database/class-diagnostic-slow-query-detection.php`
- **Slug:** `database-slow-queries`
- **Category:** `database`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 60 (performance)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/database-slow-query-detection`

## Testing Pattern
```php
// Test 1: Detects slow queries in log
public function testDetectsSlowQueries() {
    // Mock slow query log with entries
    // Run diagnostic
    // Assert finding with slow query count
}

// Test 2: Passes with optimized queries
public function testPassesWithOptimizedQueries() {
    // Mock slow query log (empty/minimal)
    // Run diagnostic
    // Assert null or low threat
}
```

## Validation Checklist
- [ ] Reads slow query log safely (file exists check)
- [ ] Returns null when no slow queries
- [ ] Returns proper finding array
- [ ] All metadata fields present
- [ ] Threat level 50-70 (performance)
- [ ] PHPCS standards pass
- [ ] KB article links to optimization guide
```

---

## 3️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Database Backup Availability`  
Labels: `diagnostic,database,backup,enhancement,phase6`

```
## Description
Verify database has been backed up recently and backup is accessible.

## What It Checks
- Verifies backup plugin is active
- Checks last backup timestamp
- Ensures backup file exists and is readable
- Validates backup size > 0 bytes

## Why Valuable
- Backups are useless if they don't exist
- Users often think backups are happening when they're not
- Data loss prevention critical
- Peace of mind assurance

## Success Criteria
✅ Detects missing backups  
✅ Shows last backup time  
✅ Checks backup file accessibility  
✅ Verifies backup completeness  
✅ KPI: "Backups verified"  
✅ Unit tests pass (mock backup data)  
✅ Handles multiple backup plugins  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/database/class-diagnostic-database-backup-check.php`
- **Slug:** `database-backup-check`
- **Category:** `database`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 85 (critical - data loss)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/database-backup-availability`

## Testing Pattern
```php
// Test 1: Detects missing recent backup
public function testDetectsMissingBackup() {
    // Mock backup plugin option with old timestamp
    // Run diagnostic
    // Assert finding with threat_level >= 80
}

// Test 2: Passes with recent backup
public function testPassesWithRecentBackup() {
    // Mock backup plugin with recent timestamp
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Detects missing backups
- [ ] Checks backup timestamp accuracy
- [ ] Returns proper finding structure
- [ ] All metadata fields present
- [ ] Threat level 80+ (critical)
- [ ] PHPCS standards pass
- [ ] Links to backup setup guide
```

---

## 4️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Database User Permissions Audit`  
Labels: `diagnostic,database,security,enhancement,phase6`

```
## Description
Audit MySQL database user permissions and enforce least-privilege principle.

## What It Checks
- Runs SHOW GRANTS for current database user
- Flags excessive permissions (SUPER, FILE, PROCESS)
- Verifies minimal required privileges
- Suggests principle of least privilege

## Why Valuable
- Database hardening best practice
- Reduces attack surface if credentials compromised
- Required for security audit compliance
- Prevents accidental destructive operations

## Success Criteria
✅ Detects excessive privileges  
✅ Shows current permissions  
✅ Explains security impact  
✅ Suggests secure configuration  
✅ KPI: "Permissions hardened"  
✅ Unit tests pass (mock SHOW GRANTS)  
✅ Handles single-site and multisite  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/database/class-diagnostic-database-permissions-check.php`
- **Slug:** `database-permissions-check`
- **Category:** `database`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 75 (security)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/database-permissions-audit`

## Testing Pattern
```php
// Test 1: Detects excessive privileges
public function testDetectsExcessivePrivileges() {
    // Mock SHOW GRANTS with SUPER privilege
    // Run diagnostic
    // Assert finding with threat_level >= 70
}

// Test 2: Passes with minimal privileges
public function testPassesWithMinimalPrivileges() {
    // Mock SHOW GRANTS with SELECT,INSERT,UPDATE,DELETE
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Queries SHOW GRANTS safely
- [ ] Returns null for safe permissions
- [ ] Returns finding for excessive permissions
- [ ] All metadata fields present
- [ ] Threat level 70-80 (security)
- [ ] PHPCS standards pass
```

---

## 5️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Database Charset/Collation Consistency`  
Labels: `diagnostic,database,performance,enhancement,phase6`

```
## Description
Verify UTF-8mb4 charset consistency across all database tables for emoji and international character support.

## What It Checks
- Checks database charset (should be utf8mb4)
- Verifies all tables use utf8mb4
- Flags mixed charset tables
- Detects collation mismatches

## Why Valuable
- Emoji support prevents data truncation
- International character support
- Prevents encoding errors
- UX improvement for global users

## Success Criteria
✅ Detects mixed charsets  
✅ Shows affected tables  
✅ Explains impact of inconsistency  
✅ Suggests utf8mb4 conversion  
✅ KPI: "Character encoding optimized"  
✅ Unit tests pass (mock charset queries)  
✅ Safe detection (no modifications)  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/database/class-diagnostic-database-charset-consistency.php`
- **Slug:** `database-charset-consistency`
- **Category:** `database`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 40 (UX/performance)
- **Auto-fixable:** Yes (via treatment)
- **KB Article:** `https://wpshadow.com/kb/database-charset-consistency`

## Testing Pattern
```php
// Test 1: Detects mixed charsets
public function testDetectsMixedCharsets() {
    // Mock charset query with mixed results
    // Run diagnostic
    // Assert finding with affected table count
}

// Test 2: Passes with consistent UTF8MB4
public function testPassesWithConsistentUtf8mb4() {
    // Mock all tables utf8mb4
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Queries information_schema safely
- [ ] Returns null for consistent charset
- [ ] Returns finding for mixed charsets
- [ ] All metadata fields present
- [ ] Threat level 30-50 (UX)
- [ ] PHPCS standards pass
```

---

# PHASE 7: BACKUP & DISASTER RECOVERY (5 Issues)

## 6️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Automated Backup Configuration`  
Labels: `diagnostic,backup,security,enhancement,phase7`

```
## Description
Verify automated backups are configured and running on schedule.

## What It Checks
- Detects backup plugin active
- Verifies backup schedule configured
- Checks if backups run automatically
- Confirms backup frequency is adequate

## Why Valuable
- Most critical disaster prevention feature
- Users often think backups are happening when they're not
- Peace of mind essential
- Prevents catastrophic data loss

## Success Criteria
✅ Detects backup plugin status  
✅ Shows backup schedule  
✅ Checks automation status  
✅ Suggests backup frequency  
✅ KPI: "Automated backups enabled"  
✅ Unit tests pass (mock plugin data)  
✅ Handles multiple backup solutions  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/backup/class-diagnostic-backup-automation-status.php`
- **Slug:** `backup-automation-status`
- **Category:** `backup`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 95 (critical - disaster prevention)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/backup-automation-configuration`

## Testing Pattern
```php
// Test 1: Detects disabled automation
public function testDetectsDisabledAutomation() {
    // Mock backup plugin disabled
    // Run diagnostic
    // Assert finding with threat_level >= 90
}

// Test 2: Passes with enabled automation
public function testPassesWithEnabledAutomation() {
    // Mock backup plugin active with schedule
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Detects backup plugin status
- [ ] Returns null for automated backups
- [ ] Returns finding for disabled automation
- [ ] All metadata fields present
- [ ] Threat level 90+ (critical)
- [ ] PHPCS standards pass
```

---

## 7️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Backup Age & Retention Policy`  
Labels: `diagnostic,backup,security,enhancement,phase7`

```
## Description
Check that backups exist and are recent (not stale) and retention policy is configured.

## What It Checks
- Verifies last backup timestamp is recent (< 24 hours)
- Checks backup count to ensure retention
- Flags stale backups
- Ensures redundant copies exist

## Why Valuable
- Stale backups are useless in emergency
- Ensures disaster recovery actually works
- Critical peace of mind
- Verifies backup automation is actually running

## Success Criteria
✅ Detects stale backups  
✅ Shows backup age  
✅ Checks retention policy  
✅ Suggests backup frequency  
✅ KPI: "Recent backups verified"  
✅ Unit tests pass (mock backup timestamps)  
✅ Configurable age threshold  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/backup/class-diagnostic-backup-age-check.php`
- **Slug:** `backup-age-check`
- **Category:** `backup`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 85 (critical)
- **Auto-fixable:** Partially (can trigger manual backup)
- **KB Article:** `https://wpshadow.com/kb/backup-age-retention-policy`

## Testing Pattern
```php
// Test 1: Detects old backups
public function testDetectsOldBackups() {
    // Mock backup timestamp from 48 hours ago
    // Run diagnostic
    // Assert finding with threat_level >= 80
}

// Test 2: Passes with recent backups
public function testPassesWithRecentBackups() {
    // Mock backup timestamp from 6 hours ago
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Checks backup timestamps
- [ ] Returns null for recent backups
- [ ] Returns finding for stale backups
- [ ] All metadata fields present
- [ ] Threat level 80+ (critical)
- [ ] PHPCS standards pass
```

---

## 8️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Database Backup Integrity`  
Labels: `diagnostic,backup,security,enhancement,phase7`

```
## Description
Verify database backup files exist, are accessible, and appear to have valid content.

## What It Checks
- Checks backup file exists
- Verifies file is readable
- Checks file size > 0 bytes
- Validates backup file headers/integrity

## Why Valuable
- Backups can be corrupted or inaccessible
- Discovers backup failures before disaster
- Ensures restore will actually work
- Critical verification

## Success Criteria
✅ Detects missing backup files  
✅ Checks file accessibility  
✅ Verifies file size  
✅ Validates backup integrity  
✅ KPI: "Backup integrity verified"  
✅ Unit tests pass (mock file checks)  
✅ Handles various backup formats  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/backup/class-diagnostic-database-backup-integrity.php`
- **Slug:** `database-backup-integrity`
- **Category:** `backup`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 80 (critical)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/database-backup-integrity`

## Testing Pattern
```php
// Test 1: Detects missing backup file
public function testDetectsMissingBackupFile() {
    // Mock backup option pointing to non-existent file
    // Run diagnostic
    // Assert finding with threat_level >= 75
}

// Test 2: Passes with valid backup file
public function testPassesWithValidBackupFile() {
    // Mock backup file exists and readable
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Checks file existence
- [ ] Checks file readability
- [ ] Verifies file size > 0
- [ ] Returns proper finding structure
- [ ] All metadata fields present
- [ ] Threat level 75+ (critical)
- [ ] PHPCS standards pass
```

---

## 9️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Offsite Backup Verification`  
Labels: `diagnostic,backup,security,enhancement,phase7`

```
## Description
Verify backups are sent to offsite location (cloud storage, remote server) for disaster protection.

## What It Checks
- Detects offsite backup configuration
- Checks if backups are synced to cloud
- Verifies offsite storage is accessible
- Confirms remote backup retention

## Why Valuable
- Protects against server/hosting failure
- Critical for disaster recovery
- Users often backup locally only
- Essential for data protection

## Success Criteria
✅ Detects offsite backup configuration  
✅ Shows sync status  
✅ Checks remote storage access  
✅ Verifies backup redundancy  
✅ KPI: "Offsite backups enabled"  
✅ Unit tests pass (mock service config)  
✅ Supports major cloud providers  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/backup/class-diagnostic-offsite-backup-configured.php`
- **Slug:** `offsite-backup-configured`
- **Category:** `backup`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 75 (security/disaster prevention)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/offsite-backup-verification`

## Testing Pattern
```php
// Test 1: Detects missing offsite backup
public function testDetectsMissingOffsiteBackup() {
    // Mock backup config without cloud storage
    // Run diagnostic
    // Assert finding with threat_level >= 70
}

// Test 2: Passes with offsite backup
public function testPassesWithOffsiteBackup() {
    // Mock backup synced to cloud storage
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Detects offsite backup config
- [ ] Returns null for configured offsite
- [ ] Returns finding for missing offsite
- [ ] All metadata fields present
- [ ] Threat level 70-80 (security)
- [ ] PHPCS standards pass
```

---

## 1️⃣0️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Disaster Recovery Test`  
Labels: `diagnostic,backup,security,enhancement,phase7`

```
## Description
Verify site has tested disaster recovery procedure and can restore from backup.

## What It Checks
- Checks if disaster recovery plan exists
- Verifies restore procedure is documented
- Confirms backup restoration is tested
- Ensures team knows recovery process

## Why Valuable
- Backups are useless if you can't restore
- Many sites have backups but can't recover
- Procedures change and get forgotten
- Critical verification before disaster

## Success Criteria
✅ Detects recovery procedure  
✅ Checks testing status  
✅ Shows recovery readiness  
✅ Suggests testing schedule  
✅ KPI: "Recovery plan tested"  
✅ Unit tests pass (mock recovery config)  
✅ Verifiable checklist  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/backup/class-diagnostic-disaster-recovery-testable.php`
- **Slug:** `disaster-recovery-testable`
- **Category:** `backup`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 70 (critical process)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/disaster-recovery-test`

## Testing Pattern
```php
// Test 1: Detects untested recovery
public function testDetectsUntestedRecovery() {
    // Mock recovery procedure not tested
    // Run diagnostic
    // Assert finding with threat_level >= 65
}

// Test 2: Passes with tested recovery
public function testPassesWithTestedRecovery() {
    // Mock recovery procedure tested recently
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Checks recovery procedure documentation
- [ ] Returns null for tested procedures
- [ ] Returns finding for untested
- [ ] All metadata fields present
- [ ] Threat level 65-75 (process)
- [ ] PHPCS standards pass
```

---

# PHASE 8: REST API & INTEGRATION SECURITY (5 Issues)

## 1️⃣1️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: REST API Anonymous Access Control`  
Labels: `diagnostic,rest-api,security,enhancement,phase8`

```
## Description
Verify REST API endpoints require authentication and don't expose data to anonymous users.

## What It Checks
- Tests REST API endpoints for anonymous access
- Identifies public endpoints that should be private
- Checks response codes (403 for unauthorized)
- Flags data exposure risks

## Why Valuable
- REST API is growing attack surface
- Users often don't know what's publicly accessible
- Prevents accidental data exposure
- Critical security best practice

## Success Criteria
✅ Tests anonymous REST access  
✅ Detects public endpoints  
✅ Shows exposed data types  
✅ Suggests authentication  
✅ KPI: "REST endpoints secured"  
✅ Unit tests pass (mock API responses)  
✅ Tests core WordPress endpoints  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/rest_api/class-diagnostic-rest-api-anonymous-access.php`
- **Slug:** `rest-api-anonymous-access`
- **Category:** `rest_api`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 75 (security - data exposure)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/rest-api-anonymous-access-control`

## Testing Pattern
```php
// Test 1: Detects anonymous access
public function testDetectsAnonymousAccess() {
    // Mock REST endpoint returning data to anonymous request
    // Run diagnostic
    // Assert finding with threat_level >= 70
}

// Test 2: Passes with authentication required
public function testPassesWithAuthenticationRequired() {
    // Mock REST endpoint returning 403 to anonymous
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Tests REST endpoints safely
- [ ] Returns null for secure endpoints
- [ ] Returns finding for exposed endpoints
- [ ] All metadata fields present
- [ ] Threat level 70-80 (security)
- [ ] PHPCS standards pass
```

---

## 1️⃣2️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: REST API Authentication Method`  
Labels: `diagnostic,rest-api,security,enhancement,phase8`

```
## Description
Check that REST API uses secure authentication (token) vs weak methods (basic auth).

## What It Checks
- Detects authentication method in use
- Flags basic auth (should use tokens)
- Verifies authentication headers
- Suggests OAuth 2.0 or JWT

## Why Valuable
- Weak REST auth = credentials at risk
- Token-based auth is security best practice
- Prevents credential interception
- Critical for API security

## Success Criteria
✅ Detects auth method  
✅ Identifies weak methods  
✅ Shows authentication flow  
✅ Suggests secure alternatives  
✅ KPI: "API auth strength improved"  
✅ Unit tests pass (mock auth config)  
✅ Handles custom auth  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/rest_api/class-diagnostic-rest-api-auth-strength.php`
- **Slug:** `rest-api-auth-strength`
- **Category:** `rest_api`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 70 (security)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/rest-api-authentication-method`

## Testing Pattern
```php
// Test 1: Detects weak authentication
public function testDetectsWeakAuthentication() {
    // Mock basic auth in use
    // Run diagnostic
    // Assert finding with threat_level >= 65
}

// Test 2: Passes with strong authentication
public function testPassesWithStrongAuthentication() {
    // Mock JWT/OAuth configured
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Detects auth method
- [ ] Returns null for strong auth
- [ ] Returns finding for weak auth
- [ ] All metadata fields present
- [ ] Threat level 65-75 (security)
- [ ] PHPCS standards pass
```

---

## 1️⃣3️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: REST API Rate Limiting`  
Labels: `diagnostic,rest-api,security,enhancement,phase8`

```
## Description
Verify rate limiting is configured on REST API to prevent abuse and DDoS attacks.

## What It Checks
- Detects if rate limiting is enabled
- Checks rate limit thresholds
- Verifies protection against API abuse
- Suggests appropriate limits

## Why Valuable
- REST API abuse = performance degradation
- Users often leave APIs completely open
- Prevents brute force attacks
- Critical for site stability

## Success Criteria
✅ Detects rate limiting status  
✅ Shows rate limits  
✅ Checks threshold reasonableness  
✅ Suggests optimal limits  
✅ KPI: "API rate limiting enabled"  
✅ Unit tests pass (mock rate limit config)  
✅ Handles multiple limit types  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/rest_api/class-diagnostic-rest-api-rate-limiting.php`
- **Slug:** `rest-api-rate-limiting`
- **Category:** `rest_api`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 65 (security/performance)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/rest-api-rate-limiting`

## Testing Pattern
```php
// Test 1: Detects missing rate limiting
public function testDetectsMissingRateLimiting() {
    // Mock rate limiting disabled
    // Run diagnostic
    // Assert finding with threat_level >= 60
}

// Test 2: Passes with rate limiting
public function testPassesWithRateLimiting() {
    // Mock rate limiting configured
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Detects rate limiting config
- [ ] Returns null when enabled
- [ ] Returns finding when disabled
- [ ] All metadata fields present
- [ ] Threat level 60-70 (security)
- [ ] PHPCS standards pass
```

---

## 1️⃣4️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Webhook Endpoint Security`  
Labels: `diagnostic,rest-api,security,enhancement,phase8`

```
## Description
Verify webhook endpoints are registered with HTTPS and use secure verification methods.

## What It Checks
- Detects registered webhooks
- Checks if endpoints use HTTPS
- Verifies webhook signature verification
- Flags insecure webhook configurations

## Why Valuable
- Webhooks transmit sensitive data
- Unencrypted webhooks = data exposure
- Webhook hijacking = security breach
- Critical for integration security

## Success Criteria
✅ Detects webhook endpoints  
✅ Checks HTTPS usage  
✅ Verifies signature validation  
✅ Shows webhook count  
✅ KPI: "Webhook security verified"  
✅ Unit tests pass (mock webhook config)  
✅ Handles multiple webhooks  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/rest_api/class-diagnostic-webhook-security-check.php`
- **Slug:** `webhook-security-check`
- **Category:** `rest_api`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 70 (security - data transmission)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/webhook-endpoint-security`

## Testing Pattern
```php
// Test 1: Detects insecure webhooks
public function testDetectsInsecureWebhooks() {
    // Mock webhook with HTTP (not HTTPS)
    // Run diagnostic
    // Assert finding with threat_level >= 65
}

// Test 2: Passes with secure webhooks
public function testPassesWithSecureWebhooks() {
    // Mock webhook with HTTPS and signatures
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Detects webhook endpoints
- [ ] Returns null for secure webhooks
- [ ] Returns finding for insecure
- [ ] All metadata fields present
- [ ] Threat level 65-75 (security)
- [ ] PHPCS standards pass
```

---

## 1️⃣5️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: API Key Exposure Risk`  
Labels: `diagnostic,rest-api,security,enhancement,phase8`

```
## Description
Scan wp-config.php and environment files for hardcoded API keys and credentials.

## What It Checks
- Scans wp-config.php for API keys
- Detects hardcoded credentials
- Identifies exposed secrets
- Flags insecure storage patterns

## Why Valuable
- API keys in code = credential exposure
- Version control leaks are common
- Compromised credentials = account takeover
- Critical security prevention

## Success Criteria
✅ Detects hardcoded API keys  
✅ Shows exposed services  
✅ Identifies credential storage issues  
✅ Suggests secure alternatives  
✅ KPI: "Credentials secured"  
✅ Unit tests pass (mock wp-config scan)  
✅ Scans safely without modifying  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/rest_api/class-diagnostic-api-key-exposure-check.php`
- **Slug:** `api-key-exposure-check`
- **Category:** `rest_api`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 80 (critical - credential exposure)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/api-key-exposure-risk`

## Testing Pattern
```php
// Test 1: Detects exposed API keys
public function testDetectsExposedApiKeys() {
    // Mock wp-config with define('API_KEY', 'secret')
    // Run diagnostic
    // Assert finding with threat_level >= 75
}

// Test 2: Passes with secure configuration
public function testPassesWithSecureConfiguration() {
    // Mock wp-config using environment variables
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Scans wp-config safely
- [ ] Returns null for secure config
- [ ] Returns finding for exposed keys
- [ ] All metadata fields present
- [ ] Threat level 75+ (security)
- [ ] PHPCS standards pass
```

---

# QUICK WINS (9 Issues)

## 1️⃣6️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: WordPress Table Prefix Security`  
Labels: `diagnostic,database,security,enhancement,quick-win`

```
## Description
Verify WordPress database table prefix has been changed from default "wp_" for security hardening.

## What It Checks
- Queries information_schema
- Detects table prefix
- Flags default "wp_" prefix
- Suggests custom prefix

## Why Valuable
- Default prefix makes SQL injection easier
- Simple security best practice
- No performance impact
- Easy hardening step

## Success Criteria
✅ Detects table prefix  
✅ Flags default "wp_" prefix  
✅ Shows security impact  
✅ Suggests custom prefix  
✅ KPI: "Table prefix hardened"  
✅ Unit tests pass (mock table query)  
✅ Works with custom prefixes  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/database/class-diagnostic-table-prefix-security.php`
- **Slug:** `table-prefix-changed`
- **Category:** `database`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 60 (security hardening)
- **Auto-fixable:** No (requires migration)
- **KB Article:** `https://wpshadow.com/kb/table-prefix-security`

## Testing Pattern
```php
// Test 1: Detects default prefix
public function testDetectsDefaultPrefix() {
    // Mock wp_posts, wp_postmeta tables
    // Run diagnostic
    // Assert finding with threat_level >= 55
}

// Test 2: Passes with custom prefix
public function testPassesWithCustomPrefix() {
    // Mock custom_posts, custom_postmeta
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Queries table prefix safely
- [ ] Returns null for custom prefix
- [ ] Returns finding for default prefix
- [ ] All metadata fields present
- [ ] Threat level 55-65 (security)
- [ ] PHPCS standards pass
```

---

## 1️⃣7️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Database UTF-8mb4 Support`  
Labels: `diagnostic,database,performance,enhancement,quick-win`

```
## Description
Verify database is configured for UTF-8mb4 charset to support emojis and international characters.

## What It Checks
- Checks database default charset
- Verifies utf8mb4 configuration
- Detects legacy utf8 encoding
- Suggests utf8mb4 migration

## Why Valuable
- Emoji support improves UX
- International character support
- Prevents text truncation
- Modern best practice

## Success Criteria
✅ Detects charset  
✅ Flags non-UTF8MB4  
✅ Shows impact  
✅ Suggests migration  
✅ KPI: "Charset optimized"  
✅ Unit tests pass (mock charset query)  
✅ Safe detection  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/database/class-diagnostic-database-utf8mb4.php`
- **Slug:** `database-utf8mb4`
- **Category:** `database`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 30 (UX)
- **Auto-fixable:** Yes
- **KB Article:** `https://wpshadow.com/kb/utf8mb4-support`

## Testing Pattern
```php
// Test 1: Detects non-UTF8MB4
public function testDetectsNonUtf8mb4() {
    // Mock database charset utf8
    // Run diagnostic
    // Assert finding
}

// Test 2: Passes with UTF8MB4
public function testPassesWithUtf8mb4() {
    // Mock database charset utf8mb4
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Queries charset configuration
- [ ] Returns null for UTF8MB4
- [ ] Returns finding for other charsets
- [ ] All metadata fields present
- [ ] Threat level 25-35 (UX)
- [ ] PHPCS standards pass
```

---

## 1️⃣8️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Recent Backup Exists`  
Labels: `diagnostic,backup,security,enhancement,quick-win`

```
## Description
Quick check that a backup from the last 24 hours exists.

## What It Checks
- Checks last backup timestamp
- Verifies backup < 24 hours old
- Shows backup age
- Confirms recent backup existence

## Why Valuable
- Stale backups are useless
- Quick verification of backup health
- Peace of mind assurance
- Critical quick-win

## Success Criteria
✅ Detects backup age  
✅ Flags backups > 24 hours  
✅ Shows hours since backup  
✅ Suggests backup frequency  
✅ KPI: "Recent backups verified"  
✅ Unit tests pass (mock timestamps)  
✅ Configurable age threshold  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/backup/class-diagnostic-recent-backup-exists.php`
- **Slug:** `recent-backup-exists`
- **Category:** `backup`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 80 (critical)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/recent-backup-exists`

## Testing Pattern
```php
// Test 1: Detects old backup
public function testDetectsOldBackup() {
    // Mock backup from 48 hours ago
    // Run diagnostic
    // Assert finding with threat_level >= 75
}

// Test 2: Passes with recent backup
public function testPassesWithRecentBackup() {
    // Mock backup from 12 hours ago
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Checks backup timestamps
- [ ] Returns null for recent backups
- [ ] Returns finding for stale backups
- [ ] All metadata fields present
- [ ] Threat level 75+ (critical)
- [ ] PHPCS standards pass
```

---

## 1️⃣9️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Backup Location Accessible`  
Labels: `diagnostic,backup,security,enhancement,quick-win`

```
## Description
Verify backup destination directory is accessible and writable for backup creation.

## What It Checks
- Checks backup directory exists
- Verifies directory is writable
- Checks free disk space
- Ensures backups can be created

## Why Valuable
- Backups fail silently if destination inaccessible
- Users think backups are running when they fail
- Prevents data loss surprises
- Critical quick verification

## Success Criteria
✅ Checks directory existence  
✅ Verifies write permissions  
✅ Checks disk space  
✅ Shows backup status  
✅ KPI: "Backup location verified"  
✅ Unit tests pass (mock file checks)  
✅ Safe verification  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/backup/class-diagnostic-backup-location-accessible.php`
- **Slug:** `backup-location-accessible`
- **Category:** `backup`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 75 (critical)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/backup-location-accessible`

## Testing Pattern
```php
// Test 1: Detects inaccessible location
public function testDetectsInaccessibleLocation() {
    // Mock backup directory not writable
    // Run diagnostic
    // Assert finding with threat_level >= 70
}

// Test 2: Passes with accessible location
public function testPassesWithAccessibleLocation() {
    // Mock backup directory writable
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Checks directory permissions
- [ ] Returns null when accessible
- [ ] Returns finding when not accessible
- [ ] All metadata fields present
- [ ] Threat level 70+ (critical)
- [ ] PHPCS standards pass
```

---

## 2️⃣0️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: REST API Authentication Required`  
Labels: `diagnostic,rest-api,security,enhancement,quick-win`

```
## Description
Verify public REST API endpoints require authentication by default.

## What It Checks
- Tests REST API endpoints
- Checks if authentication is required
- Verifies proper 403 responses
- Flags unauthenticated access

## Why Valuable
- REST API is major attack surface
- Users often don't realize data is public
- Quick security verification
- Critical quick-win

## Success Criteria
✅ Tests REST endpoints  
✅ Checks auth requirement  
✅ Shows endpoint status  
✅ Suggests security fixes  
✅ KPI: "REST endpoints secured"  
✅ Unit tests pass (mock API responses)  
✅ Quick verification  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/rest_api/class-diagnostic-rest-api-auth-required.php`
- **Slug:** `rest-api-auth-required`
- **Category:** `rest_api`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 70 (security)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/rest-api-auth-required`

## Testing Pattern
```php
// Test 1: Detects unauthenticated access
public function testDetectsUnauthenticatedAccess() {
    // Mock REST endpoint accessible without auth
    // Run diagnostic
    // Assert finding with threat_level >= 65
}

// Test 2: Passes with auth required
public function testPassesWithAuthRequired() {
    // Mock REST endpoint requires auth
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Tests REST endpoints safely
- [ ] Returns null when auth required
- [ ] Returns finding when public
- [ ] All metadata fields present
- [ ] Threat level 65-75 (security)
- [ ] PHPCS standards pass
```

---

## 2️⃣1️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: REST API Version Consistency`  
Labels: `diagnostic,rest-api,performance,enhancement,quick-win`

```
## Description
Verify all REST API endpoints use consistent versioning scheme to prevent integration breaks.

## What It Checks
- Queries available REST routes
- Checks API versioning
- Detects version mismatches
- Flags inconsistent versions

## Why Valuable
- API version mismatches break integrations
- Users often don't realize there's a version mismatch
- Quick reliability verification
- Prevents integration issues

## Success Criteria
✅ Detects REST versions  
✅ Checks consistency  
✅ Shows endpoint versions  
✅ Suggests standardization  
✅ KPI: "API versions standardized"  
✅ Unit tests pass (mock REST routes)  
✅ Works with custom endpoints  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/rest_api/class-diagnostic-rest-api-version-consistent.php`
- **Slug:** `rest-api-version-consistent`
- **Category:** `rest_api`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 40 (integration reliability)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/rest-api-version-consistency`

## Testing Pattern
```php
// Test 1: Detects version mismatch
public function testDetectsVersionMismatch() {
    // Mock mixed API versions (v1 and v2)
    // Run diagnostic
    // Assert finding
}

// Test 2: Passes with consistent versions
public function testPassesWithConsistentVersions() {
    // Mock all endpoints same version
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Queries REST routes safely
- [ ] Returns null for consistent versions
- [ ] Returns finding for mixed versions
- [ ] All metadata fields present
- [ ] Threat level 35-45 (reliability)
- [ ] PHPCS standards pass
```

---

## 2️⃣2️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Debug Mode Status`  
Labels: `diagnostic,settings,security,enhancement,quick-win`

```
## Description
Verify WP_DEBUG mode is appropriate for the environment (off in production, on in dev).

## What It Checks
- Checks WP_DEBUG constant
- Verifies WP_DEBUG_LOG enabled
- Checks WP_DEBUG_DISPLAY setting
- Detects debug info in headers

## Why Valuable
- Debug mode in production = information leak
- Debug mode off in dev = hard to debug
- Security best practice
- Quick configuration verification

## Success Criteria
✅ Detects debug mode status  
✅ Verifies appropriate setting  
✅ Shows debug configuration  
✅ Suggests fixes  
✅ KPI: "Debug mode optimized"  
✅ Unit tests pass (mock wp-config)  
✅ Quick verification  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/settings/class-diagnostic-debug-mode-status.php`
- **Slug:** `debug-mode-status`
- **Category:** `settings`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 40 (security/performance)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/debug-mode-status`

## Testing Pattern
```php
// Test 1: Detects debug on in production
public function testDetectsDebugOnInProduction() {
    // Mock WP_DEBUG true in production
    // Run diagnostic
    // Assert finding with threat_level >= 35
}

// Test 2: Passes with appropriate setting
public function testPassesWithAppropriateDebug() {
    // Mock WP_DEBUG false in production
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Checks debug constants
- [ ] Returns null when appropriate
- [ ] Returns finding when inappropriate
- [ ] All metadata fields present
- [ ] Threat level 35-45 (security)
- [ ] PHPCS standards pass
```

---

## 2️⃣3️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Site Health Check Age`  
Labels: `diagnostic,monitoring,maintenance,enhancement,quick-win`

```
## Description
Verify WordPress Site Health has run recently and is up to date.

## What It Checks
- Checks Site Health last run timestamp
- Verifies check age < 7 days
- Detects stale health status
- Suggests fresh check

## Why Valuable
- Stale health checks are outdated
- Users forget to run health checks
- Quick verification system status
- Maintenance best practice

## Success Criteria
✅ Detects health check age  
✅ Flags stale checks  
✅ Shows last check time  
✅ Suggests fresh check  
✅ KPI: "Health checks current"  
✅ Unit tests pass (mock options)  
✅ Quick verification  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/monitoring/class-diagnostic-site-health-check-age.php`
- **Slug:** `site-health-check-age`
- **Category:** `monitoring`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 30 (maintenance)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/site-health-check-age`

## Testing Pattern
```php
// Test 1: Detects stale health check
public function testDetectsStaleHealthCheck() {
    // Mock health check from 30 days ago
    // Run diagnostic
    // Assert finding
}

// Test 2: Passes with recent check
public function testPassesWithRecentCheck() {
    // Mock health check from today
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Checks health check timestamp
- [ ] Returns null for recent checks
- [ ] Returns finding for stale checks
- [ ] All metadata fields present
- [ ] Threat level 25-35 (maintenance)
- [ ] PHPCS standards pass
```

---

## 2️⃣4️⃣ COPY-PASTE THIS ENTIRE TEXT:

Title: `Diagnostic: Update Check Frequency`  
Labels: `diagnostic,monitoring,security,enhancement,quick-win`

```
## Description
Verify WordPress checks for plugin and theme updates regularly.

## What It Checks
- Checks wp_update_last_checked timestamp
- Verifies update checks run daily
- Flags outdated update cache
- Suggests manual update check

## Why Valuable
- Updates prevent security vulnerabilities
- Stale update cache = missed patches
- Critical security practice
- Quick verification

## Success Criteria
✅ Detects update check age  
✅ Flags infrequent checks  
✅ Shows check frequency  
✅ Suggests enable updates  
✅ KPI: "Update checks current"  
✅ Unit tests pass (mock options)  
✅ Quick verification  
✅ PHPCS standards pass  

## Technical Requirements
- **File:** `includes/diagnostics/tests/monitoring/class-diagnostic-update-check-frequency.php`
- **Slug:** `update-check-frequency`
- **Category:** `monitoring`
- **Extends:** `Diagnostic_Base`
- **Threat Level:** 50 (security - missed patches)
- **Auto-fixable:** No
- **KB Article:** `https://wpshadow.com/kb/update-check-frequency`

## Testing Pattern
```php
// Test 1: Detects old update check
public function testDetectsOldUpdateCheck() {
    // Mock update check from 7 days ago
    // Run diagnostic
    // Assert finding with threat_level >= 45
}

// Test 2: Passes with recent check
public function testPassesWithRecentUpdateCheck() {
    // Mock update check from today
    // Run diagnostic
    // Assert null returned
}
```

## Validation Checklist
- [ ] Checks update check timestamp
- [ ] Returns null for frequent checks
- [ ] Returns finding for stale checks
- [ ] All metadata fields present
- [ ] Threat level 45-55 (security)
- [ ] PHPCS standards pass
```

---

Now copy each issue and create via GitHub Issues!
