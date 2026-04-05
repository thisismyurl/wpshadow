# Confidence Scoring Framework for WPShadow

**Last Updated:** April 3, 2026  
**Audit Response:** Phase 8 - Confidence Integration  
**Purpose:** Enable intelligent defaults and user guidance based on diagnostic reliability

---

## Philosophy

A diagnostic's **confidence score** represents how reliably it identifies real problems.

- ✅ **High Confidence (90-100%):** Heuristic proven, false-positives rare, safe auto-fix, default on
- 🟡 **Medium Confidence (70-89%):** Generally reliable, occasional false-positives, requires review
- ⚠️ **Low Confidence (50-69%):** Heuristic-based, context-dependent, user input needed
- ❌ **Unproven (<50%):** Experimental, under validation, hidden by default

**Trust principle:** Don't auto-fix what you're uncertain about.

---

## Scoring Methodology

### Data-Driven Scoring (70% weight)

| Factor | High (90+) | Medium (70-89) | Low (50-69) | Unproven (<50) |
|--------|-----------|----------------|------------|--------------|
| **False-positive rate** | <2% | 2-5% | 5-15% | >15% or unknown |
| **Detection accuracy** | >95% | 85-95% | 70-85% | <70% |
| **Production issues** | >1000 sites | 100-1000 sites | 10-100 sites | <10 sites |
| **Manual review ratio** | <5% | 5-15% | 15-40% | >40% |
| **Auto-fix safety** | Safe always | Safe usually | Risky often | Requires review |

### Validation Methods (30% weight)

| Signal | Evidence | Weight |
|--------|----------|--------|
| **Test coverage** | Unit + integration tests pass | 10% |
| **Real-world data** | Confirmed issues in production | 10% |
| **Expert review** | Core team consensus | 10% |

**Calculation Example:**
```
High Confidence = Data Score (75%) + Validation Score (25%)
                = (95% accuracy × 0.7) + (test pass × 0.3)
                = 94-98% → Score 95 (HIGH)
```

---

## Scoring Tiers

### 🟢 HIGH CONFIDENCE (90-100)

**Characteristics:**
- Proven heuristic (>1000 sites tested)
- <2% false-positive rate
- Safe auto-fix (no side effects)
- Clear remediation path
- Actionable result

**User Experience:**
- ✅ Auto-fix offered by default
- ✅ In Core 50 list
- ✅ In "Quick Scan" results
- ✅ Green badge (🟢)
- ✅ Example: "Auth keys not set" (simple boolean)

**Example Diagnostics:**
1. Auth Keys and Salts Set → **95**
2. No Default Admin User → **92**
3. Browser Caching Headers Missing → **91**
4. Compression Enabled → **90**

---

### 🟡 MEDIUM CONFIDENCE (70-89)

**Characteristics:**
- Generally reliable (100-1000 sites tested)
- 2-5% false-positive rate
- Auto-fix possible but risky
- May need context understanding
- Some manual review expected

**User Experience:**
- ⚠️ Auto-fix offered with warning
- ⚠️ In full 230 diagnostics
- ⚠️ In "Full Scan" results
- ⚠️ Yellow badge (🟡)
- ⚠️ Example: "Database not indexed" (context-dependent)

**Example Diagnostics:**
1. WP Options Autoload Too Large → **82**
2. Auto-Draft Accumulation → **78**
3. Stale Sessions Not Cleared → **75**
4. Plugin Compatibility → **72**

---

### ⚠️ LOW CONFIDENCE (50-69)

**Characteristics:**
- Limited validation (10-100 sites)
- 5-15% false-positive rate
- Auto-fix not recommended
- Requires user interpretation
- High false-positive probability

**User Experience:**
- ❌ No auto-fix offered
- ❌ Advanced/Experimental section
- ❌ Requires manual check first
- ❌ Orange badge (⚠️)
- ❌ Example: "Site speed optimized?" (subjective)

**Example Diagnostics:**
1. Heartbeat Optimization Needed → **65**
2. Alt Text Coverage → **62**
3. WooCommerce Session Optimization → **58**

---

### ❌ EXPERIMENTAL (<50)

**Characteristics:**
- Unvalidated or beta (few sites tested)
- Unknown false-positive rate
- Risky auto-fix
- Under active development
- Hidden by default

**User Experience:**
- 🔴 Hidden unless explicitly enabled
- 🔴 Beta/Planned status required
- 🔴 No auto-fix ever offered
- 🔴 Red badge (🔴)
- 🔴 Example: New AI-based optimization checks

**Example Diagnostics:**
1. Advanced Cache Layer Analysis → **35**
2. Predictive Performance Scoring → **25**

---

## Implementation

### 1. Diagnostic Definition Enhancement

Add `confidence` field to every diagnostic:

```php
class Diagnostic_Example extends BaseClass {
    public function get_diagnostic_definition() {
        return [
            'slug'         => 'example-check',
            'name'         => 'Example Diagnostic',
            'description'  => 'Checks if example is configured',
            'category'     => 'security',
            
            // NEW: Confidence field
            'confidence'   => 95,  // 0-100 scale
            'confidence_reason' => 'Boolean check, no false positives in 5000+ sites',
            'confidence_tier'   => 'HIGH', // HIGH, MEDIUM, LOW, EXPERIMENTAL
            
            // Risk assessment for Medium/Low confidence
            'false_positive_risk'    => 'Minimal (boolean)',
            'manual_review_ratio'    => '2%',
            'auto_fix_safety'        => 'Safe - no side effects',
            
            // Test data
            'validation_methods'     => ['unit_test', 'integration_test', 'real_world'],
            'sites_tested'           => 5000,
            'known_false_positives'  => 8,  // Out of sites_tested
        ];
    }
}
```

### 2. Diagnostic Registry Enhancement

Register confidence as discoverable field:

```php
class Diagnostic_Registry {
    // Get diagnostics filtered by confidence tier
    public function get_by_confidence_tier($tier = 'HIGH') {
        $tier_map = [
            'HIGH'         => [90, 100],
            'MEDIUM'       => [70, 89],
            'LOW'          => [50, 69],
            'EXPERIMENTAL' => [0, 49],
        ];
        
        $min_confidence = $tier_map[$tier][0];
        $max_confidence = $tier_map[$tier][1];
        
        return array_filter(
            $this->get_diagnostic_definitions(),
            fn($d) => $d['confidence'] >= $min_confidence 
                   && $d['confidence'] <= $max_confidence
        );
    }
    
    // Get diagnostics for automatic scanning
    public function get_for_automatic_scan($profile = 'core_50') {
        $auto_profiles = [
            'core_50'           => fn($d) => $d['confidence'] >= 90,
            'full_scan'         => fn($d) => $d['confidence'] >= 70,
            'advanced'          => fn($d) => $d['confidence'] >= 50,
            'include_experimental' => fn($d) => true,
        ];
        
        return array_filter(
            $this->get_diagnostic_definitions(),
            $auto_profiles[$profile]
        );
    }
}
```

### 3. Admin UI: Settings Page Filtering

```php
// In Settings → Diagnostics

Scan Profile Selection:
○ Core 50 (Essential, high-confidence)
○ Full Scan (Includes advanced)
○ Advanced (All 230 diagnostics)
○ Custom (Select confidence tier)

[Scan Profile Description]
├─ Shows count: "35 High-Confidence / 78 Medium / 22 Low / 95 Experimental"
├─ Shows last scan time
├─ Filter by Confidence Tier:
│  ☑ High (90-100)     [35 checks]
│  ☐ Medium (70-89)    [78 checks]
│  ☐ Low (50-69)       [22 checks]
│  ☐ Experimental (<50) [95 checks]
└─ Updates scan frequency based on profile
```

### 4. Dashboard Results Display

```
DIAGNOSTIC RESULT FORMAT:

[Category Icon] Diagnostic Name                [Confidence Badge]
├─ Status: ✅ / ⚠️ / ❌
├─ Confidence: 🟢 95% (High) or 🟡 78% (Medium)
├─ False-Positive Risk: Minimal (<2%)
├─ Result: [Finding summary]
├─ Fix: [Auto-fix available] [Learn more]
└─ Review Info: (if Medium/Low) "This check requires review"

Example:
════════════════════════════════════════════════
[🔐] Auth Keys and Salts Set                    [🟢 HIGH 95%]
├─ Status: ❌ FAIL
├─ Confidence: 🟢 95% (High confidence)
├─ Risk: Minimal - boolean check, no false positives
├─ Finding: WordPress security keys not configured
├─ Fix: [Auto-configure] [Learn how]
└─ Review: None needed (high confidence)
════════════════════════════════════════════════

[⚡] WP Options Autoload Within Limits         [🟡 MED 82%]
├─ Status: ⚠️ WARN
├─ Confidence: 🟡 82% (Medium confidence)
├─ Risk: 2-5% false-positives possible
├─ Finding: Autoload data size: 2.3MB (recommended <1MB)
├─ Fix: [Review options] [Learn how]
└─ Review: Please verify manually before fixing
════════════════════════════════════════════════
```

### 5. Auto-Fix Policy

**Rules:**

```php
class Auto_Fix_Engine {
    public function should_auto_fix($diagnostic_slug) {
        $diagnostic = $this->get_diagnostic($diagnostic_slug);
        $confidence = $diagnostic['confidence'];
        $fix_safety = $diagnostic->get_fix_safety_level();
        
        return $confidence >= 90        // Must be High Confidence
               && $fix_safety === 'safe'   // Must be safe
               && $this->user_opted_in(); // User must enable
    }
}
```

**Confidence-Based Behavior:**

| Confidence | Auto-Fix Offered | Risk Shown | User Prompt |
|------------|-----------------|-----------|------------|
| HIGH (90+) | Yes, by default | No | "Fix now?" |
| MEDIUM (70-89) | Yes, with warning | Yes (⚠️) | "Fix? (May need review)" |
| LOW (50-69) | No | Yes (⚠️⚠️) | "Manual fix required" |
| EXPERIMENTAL (<50) | NO | YES (❌) | "Not ready for auto-fix" |

---

## Reporting & Transparency

### Confidence Audit Report

New diagnostic: **Confidence Scoring Validation**

Runs quarterly and reports:

```
CONFIDENCE ACCURACY REPORT
Generated: 2026-Q2
════════════════════════════════════════════════

Overall Accuracy: 94.2%
├─ High Confidence: 98.1% (vs projected 90-100%)
├─ Medium Confidence: 87.3% (vs projected 70-89%)
├─ Low Confidence: 59.1% (vs projected 50-69%)
└─ Experimental: 32.5% (unvalidated tier)

Updates for Next Quarter:
├─ Promote to High: 
│  ├─ "Stale Sessions Cleared" (78→92 after 2K test sites)
│  └─ "Auto-Draft Accumulation" (75→88)
├─ Demote to Low:
│  ├─ "Plugin Compatibility" (72→58, 12% false-positives)
│  └─ "Heartbeat Optimization" (65→51)
└─ Move to Experimental:
   └─ "AI Performance Prediction" (35→25, needs more data)
```

---

## Scoring Table: All 230 Diagnostics

Create `DIAGNOSTIC_CONFIDENCE_MATRIX.md` with full table:

```
| Slug | Name | Category | Confidence | Tier | FP Rate | Sites Tested | Review Required |
|------|------|----------|-----------|------|---------|--------------|-----------------|
| auth-keys-set | Auth Keys Set | Security | 95 | HIGH | <1% | 5000 | No |
| default-admin | No Default Admin | Security | 92 | HIGH | <1% | 4500 | No |
| browser-cache | Browser Caching Headers | Performance | 91 | HIGH | <1% | 8000 | No |
| ... | ... | ... | ... | ... | ... | ... | ... |
```

---

## Migration Path

### For Existing Installations

```
Current State:
├─ All 230 diagnostics run
├─ No confidence distinction
├─ Auto-fix freely offered

After Update:
├─ Core 50 (High Confidence) selected by default
├─ Full 230 available via Settings
├─ Auto-fix only for High Confidence (90+)
├─ Existing preferences preserved
└─ Migration notice: "See Settings → Diagnostics for new options"
```

### Default Behavior

```php
// Old behavior (all diagnostics)
$diagnostics = $diagnostic_registry->discover_diagnostics();

// New behavior (respectful default)
// If user never changed settings:
$diagnostics_for_user = $diagnostic_registry->get_by_confidence_tier('HIGH');
// Shows 35 High-Confidence diagnostics

// If user selects "Full Scan":
$diagnostics_for_user = $diagnostic_registry->get_for_automatic_scan('full_scan');
// Shows all 230 with tier-based filtering
```

---

## Conclusion

**Confidence Scoring** addresses the audit's P0 concern:

> **Before:** "Why are there 230? Which ones matter?"  
> **After:** "35 essential (high-confidence) + 78 advanced + 22 specialized + 95 experimental. You choose."

This framework enables:
1. **Smarter defaults** (Core 50 by default)
2. **User trust** (transparent scoring, quarterly validation)
3. **Reduced noise** (auto-fix only for proven checks)
4. **Scalable curation** (quarterly review process)

**Combined with Readiness (Phase 6) + Core 50 (Phase 7) + Confidence (Phase 8),**  
WPShadow transforms from "overwhelming massive plugin" to "focused + extensible family."
