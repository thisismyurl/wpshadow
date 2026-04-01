# Persona-Specific Diagnostics Implementation Guide

## Overview

This guide covers the implementation of persona-specific diagnostic collections that enable WPShadow to provide tailored health assessments for different WordPress user types.

---

## What We've Created

### 1. Persona Registry System (`includes/core/class-persona-registry.php`)

**Core Classes:**
```php
Persona_Registry::get_personas()                     // Get all 7 personas
Persona_Registry::get_persona($slug)                 // Get specific persona
Persona_Registry::get_diagnostics_for_persona($slug) // Get top 20 for persona
Persona_Registry::get_diagnostic_priority(...)       // Get priority score (1-100)
Persona_Registry::generate_action_plan(...)          // Generate persona-specific action plan
```

**The 7 Personas:**
1. **DIY Website Owners** (35%) - Peace of mind, disaster prevention
2. **Agency Owners** (15%) - Reduce support tickets, proactive monitoring
3. **E-commerce Store Owners** (20%) - Revenue protection, conversion optimization
4. **Content Publishers** (20%) - Audience reach, content preservation
5. **Web Developers** (10%) - Quality deliverables, fewer post-launch issues
6. **Corporate/Enterprise (Compliance)** (5%) - Regulatory compliance, audit trails
7. **Large Enterprises** (5%) - High availability, infrastructure scale

---

## Diagnostic Implementations

### Sample Diagnostics Created

#### 1. Backup Restoration Test
**File:** `includes/diagnostics/tests/reliability/class-diagnostic-backup-restoration-test.php`
**Primary Personas:** Agency (95), Corporate (100), E-commerce (100)

```php
// Tests that backups can actually be restored, not just that they exist
// Critical for: Agency owners (liability), Corporate (compliance), E-commerce (revenue)
$test_result = Test_Backup_Restoration();
if ($test_result === false) {
    return Finding::CRITICAL("Backups are corrupted and cannot be restored");
}
```

**Business Impact:**
- Agency: Prevents client data loss disasters
- E-commerce: Protects revenue-critical data
- Corporate: Ensures GDPR compliance (backups must be recoverable)

---

#### 2. Checkout Speed
**File:** `includes/diagnostics/tests/ecommerce/class-diagnostic-checkout-speed.php`
**Primary Personas:** E-commerce (100), Agency (80), Developer (85)

```php
// Measures checkout page load time
// Target: <2 seconds (industry best practice)
// Formula: Every 100ms delay = ~1% cart abandonment
$load_time = Measure_Checkout_Page_Load();

if ($load_time > 3000) {
    // 3000ms checkout = ~10% abandoned carts
    return Finding::CRITICAL(
        "Checkout loads in {$load_time}ms. Estimated revenue loss: ~10%"
    );
}
```

**Business Impact:**
- E-commerce: $5K-100K revenue impact (depending on volume)
- Direct correlation to conversion rate

---

#### 3. Core Web Vitals Score
**File:** `includes/diagnostics/tests/seo/class-diagnostic-core-web-vitals-score.php`
**Primary Personas:** Publisher (100), Agency (85), Developer (90)

```php
// Measures Google's Core Web Vitals (LCP, FID, CLS)
// These metrics directly impact SEO rankings
$metrics = Fetch_Core_Web_Vitals();

if ($metrics['lcp'] > 2500 || $metrics['fid'] > 100 || $metrics['cls'] > 0.1) {
    return Finding::CRITICAL(
        "Core Web Vitals failing. Expected SEO ranking loss: 1-3 positions"
    );
}
```

**Business Impact:**
- Publishers: 30% traffic loss from poor SEO rankings
- Directly tied to audience reach (primary goal)

---

#### 4. Audit Log Activity
**File:** `includes/diagnostics/tests/compliance/class-diagnostic-audit-log-activity.php`
**Primary Personas:** Corporate (100), Enterprise (100), Agency (75)

```php
// Verifies admin actions are being logged for audit trails
// Required for: GDPR, HIPAA, SOC2, ISO27001 compliance
$logs_created = Count_Logs_Last_24_Hours();

if (!$logs_created) {
    return Finding::CRITICAL(
        "No audit logs in 24 hours. Required for compliance - audit failure imminent"
    );
}
```

**Business Impact:**
- Corporate: Audit failure = failed compliance review
- Enterprise: Breach liability uninsurable without audit logs

---

## Integration Points

### 1. Persona Selection UI

Users select their persona during onboarding:

```javascript
// User selects: "I'm an E-commerce Store Owner"
// WPShadow then:
// 1. Loads the 20 diagnostics most relevant to e-commerce
// 2. Prioritizes checkout speed, payment gateway, inventory checks
// 3. De-emphasizes SEO and content management diagnostics
// 4. Shows ROI metrics relevant to e-commerce ($5K-100K revenue impact)
```

### 2. Dashboard Filtering

```php
// Persona_Dashboard_Generator creates filtered dashboard
$diagnostics = Persona_Registry::get_diagnostics_for_persona('ecommerce');
// Returns only e-commerce relevant checks, prioritized by impact

// Generates action plan with persona-specific emphasis
$action_plan = Persona_Registry::generate_action_plan('ecommerce', $findings);
// Returns:
// - Critical e-commerce issues first
// - Grouped by revenue impact
// - Links to e-commerce KB articles
// - ROI estimates for each fix
```

### 3. Reporting

```php
// Generate persona-specific report
$report = Generate_Persona_Report('diy-owner', $findings);
// Report emphasizes:
// - "Peace of mind" language
// - DIY-friendly solutions
// - Disaster recovery preparedness
// - Backup verification (their #1 concern)
```

---

## Priority Matrix Implementation

Each diagnostic is scored 1-100 for each persona:

```php
// Example: SSL Certificate Expiration
$priorities = [
    'diy-owner'       => 95,  // Critical for peace of mind
    'agency'          => 95,  // Critical for all clients
    'ecommerce'       => 100, // Critical for checkout pages
    'publisher'       => 90,  // High for HTTPS requirement
    'developer'       => 85,  // High for best practices
    'corporate'       => 95,  // Critical for compliance
    'enterprise-corp' => 90,  // High for infrastructure
];
```

### Scoring Methodology

- **95-100**: Critical - Addresses primary goal, high business impact
- **80-94**: High - Supports primary goal, medium business impact
- **50-79**: Medium - Related to primary goal, lower business impact
- **1-49**: Low - Not aligned with persona priorities

---

## Adding New Diagnostics

### Step 1: Create Diagnostic Class

```php
class Diagnostic_Example extends Diagnostic_Base {
    protected static $slug = 'example-check';
    protected static $personas = ['diy-owner', 'agency'];

    public static function check() {
        // Implementation
    }
}
```

### Step 2: Add to Persona Registry

```php
// In includes/core/class-persona-registry.php
// Add to get_persona_diagnostics_map():

'diy-owner' => [
    // ... existing diagnostics ...
    'example-check',  // <-- Add here
],
```

### Step 3: Set Priority Scores

```php
// In get_priority_map():
'example-check' => [
    'diy-owner'       => 75,  // Medium priority for DIY
    'agency'          => 85,  // High priority for agencies
    'ecommerce'       => 50,  // Low priority for e-commerce
    'publisher'       => 40,  // Low priority for publishers
    'developer'       => 90,  // High priority for developers
    'corporate'       => 45,  // Medium-low for compliance
    'enterprise-corp' => 70,  // Medium for enterprises
],
```

---

## User Experience Flows

### DIY Owner Onboarding

```
1. "Welcome to WPShadow!"
   → "Tell us about your WordPress site"

2. "I run a small business website"
   → "Great! Here's your DIY Owner Dashboard"

3. Dashboard shows:
   ✓ Backup Last Run (your #1 concern)
   ✓ SSL Certificate expiration (peace of mind)
   ✓ Uptime status (disaster prevention)
   ✓ Easy-to-fix issues first
   ✗ Hides developer-focused checks
```

### E-commerce Owner Onboarding

```
1. "Welcome to WPShadow!"
   → "Tell us about your WordPress site"

2. "I run an online store"
   → "Perfect! Here's your E-commerce Dashboard"

3. Dashboard shows:
   💰 Checkout speed (revenue impact: ~10% per 1s delay)
   💰 Payment gateway status (revenue flow critical)
   💰 Cart abandonment tracking (conversion focus)
   💰 Inventory accuracy (stock sync critical)
   ✓ Backup & recovery (business continuity)
```

### Corporate IT Director Onboarding

```
1. "Welcome to WPShadow!"
   → "Tell us about your WordPress deployment"

2. "Enterprise deployment with compliance requirements"
   → "Compliance Dashboard"

3. Dashboard shows:
   ✓ Audit log activity (SOC2 requirement)
   ✓ Data encryption status (HIPAA/GDPR)
   ✓ Access control lists (audit trail)
   ✓ Compliance checklist (ISO27001 aligned)
   ✓ DLP rules (data loss prevention)
```

---

## Analytics & ROI Tracking

### Persona-Specific Metrics

```php
// Log what we're fixing for each persona
Activity_Logger::log('treatment_applied', [
    'persona'           => 'ecommerce',
    'treatment'         => 'checkout-optimization',
    'before_load_time'  => 3200,
    'after_load_time'   => 1800,
    'time_saved'        => 1400,
    'estimated_revenue_impact' => '$5000-$10000',  // Based on conversion impact
]);
```

### Dashboard Analytics

Each persona dashboard includes:
- **Impact Score**: Business value of fixing issues
- **Time Estimate**: Hours to complete fixes
- **ROI Calculator**: Revenue/cost saved by fixing
- **Comparison**: How this site compares to peers in same persona

---

## File Structure

```
includes/
├── core/
│   └── class-persona-registry.php              # Persona definitions & mappings
├── admin/
│   └── class-persona-dashboard-generator.php   # UI generation
└── diagnostics/
    └── tests/
        ├── ecommerce/
        │   └── class-diagnostic-checkout-speed.php
        ├── seo/
        │   └── class-diagnostic-core-web-vitals-score.php
        ├── compliance/
        │   └── class-diagnostic-audit-log-activity.php
        └── reliability/
            └── class-diagnostic-backup-restoration-test.php
```

---

## Phase 2: Expanded Diagnostics

### Planned Additions

**DIY Owner (10 additional diagnostics):**
- Plugin conflict detection
- Automatic backup verification
- Simple security checklist
- Performance grade

**Agency Owner (10 additional):**
- Client performance benchmarking
- Support ticket pattern analysis
- Uptime SLA tracking
- Team access audit

**E-commerce (10 additional):**
- Conversion funnel analysis
- Payment failure debugging
- Product page performance
- Mobile checkout optimization

**Publisher (10 additional):**
- Content SEO audit
- Broken link detection
- Reading time optimization
- Social sharing metrics

**Developer (10 additional):**
- Code quality scoring
- Performance baseline tracking
- Database optimization suggestions
- API response time monitoring

**Corporate (10 additional):**
- Incident response plan testing
- Backup recovery RPO/RTO validation
- Change management tracking
- Threat assessment scoring

**Enterprise (10 additional):**
- Infrastructure capacity planning
- Load balancer health
- Failover testing
- SLA dashboard

---

## Success Metrics

### User Engagement
- [ ] Persona selection completion rate >90%
- [ ] Dashboard usage >3x per week for active users
- [ ] Action plan completion rate >60%

### Business Impact
- [ ] DIY owners: Backup test runs increase 5x
- [ ] Agencies: Support tickets reduce by 30%
- [ ] E-commerce: Checkout optimization revenue impact >$1M
- [ ] Publishers: SEO ranking improvements 2-3 positions avg
- [ ] Compliance: Audit log adoption >95%

### Product Quality
- [ ] Persona-specific KB articles >90% helpful rating
- [ ] Dashboard load time <2 seconds
- [ ] False positive rate <5% per diagnostic

---

## Next Steps

1. **Register all new diagnostics** in Diagnostic Registry
2. **Add personas to existing 1,594 diagnostics**
3. **Build persona selection UI** in onboarding
4. **Create persona-specific KB articles** (already 20% done)
5. **Implement dashboard filtering** in main dashboard
6. **Add persona reports** to reporting system
7. **Test with 100 users per persona** before full release
8. **Deploy Phase 2 diagnostics** (10 more per persona)
