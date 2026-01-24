# Diagnostic Files Reorganization - Final Report
**Date:** January 24, 2026  
**Status:** ✅ COMPLETE

## Final Directory Structure

| Folder | Files | Status | Description |
|--------|-------|--------|-------------|
| **help** | 0 | ✅ Empty | Previously held unclear/stub diagnostics |
| **todo** | 281 | 🔨 Ready | Testable diagnostics (WordPress checks + HTML assessments) |
| **helpx2** | 149 | ⚠️ Complex | Non-testable diagnostics (need external data/expertise) |
| **tests** | 208 | ✅ Complete | Already implemented and verified |
| **verified** | - | - | (Reserved for finalized diagnostics) |

**Total Diagnostics: 838 files**

---

## TODO Folder (281 Files) - Ready for Implementation

### Category 1: WordPress State Checks (~103 files)

These test WordPress configuration and database state.

**Examples:**
- `core-auto-updates-enabled` - Check WP_AUTO_UPDATE_CORE constant
- `core-backup-tested` - Verify recent backup existence
- `plugin-activated` - Check if specific plugins are active
- `admin-email` - Validate admin email configuration
- `ssl-enabled` - Check HTTPS enforcement

**Implementation Pattern:**
```php
public static function check(): ?array {
    $option_value = get_option('key');
    if ($option_value !== 'expected') {
        return array(/* finding array */);
    }
    return null; // Pass
}
```

**Tools to Use:**
- `get_option()`, `get_site_option()`
- `is_plugin_active()`
- `defined()` for PHP constants
- `get_users()`, `get_posts()` for queries

---

### Category 2: HTML/Accessibility Assessments (~116 files)

These validate HTML structure and accessibility features. Guardian provides HTML content.

**Examples:**
- `wcag-alt-text` - Verify images have alt text
- `wcag-heading-hierarchy` - Check proper heading nesting
- `wcag-color-contrast` - Validate color contrast ratios
- `wcag-aria-labels` - Check ARIA attributes
- `wcag-keyboard-navigation` - Verify keyboard access

**Implementation Pattern:**
```php
public static function check(): ?array {
    $html = get_html_from_guardian(); // Provided by Guardian
    
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    
    // Parse and validate
    $issues = validate_accessibility($dom);
    
    return empty($issues) ? null : ['issues' => $issues];
}
```

**Tools to Use:**
- `DOMDocument` for HTML parsing
- `DOMXPath` for element queries
- `WPShadow\Core\Accessibility_Checker`
- `WPShadow\Core\Color_Contrast`
- `WPShadow\Core\Html_Analyzer`

---

### Category 3: Mixed/Other (~62 files)

Various WordPress health checks and performance metrics.

---

## HELPX2 Folder (149 Files) - Complex, Non-Testable

These require domain expertise, external data, or complex analysis:

- **AI/ML Readiness** (~35 files)
  - Sentiment analysis readiness
  - Content quality scoring
  - Recommendation engine capability
  
- **Market Analysis** (~28 files)
  - Competitor benchmarking
  - Market positioning
  - Industry comparisons
  
- **Trend Analysis** (~21 files)
  - Historical pattern analysis
  - Correlation studies
  - Sustainability metrics
  
- **User Engagement** (~25 files)
  - Satisfaction metrics
  - Behavioral analysis
  - Retention predictions
  
- **Other** (~40 files)

**Status:** These require product/strategy team decision on approach.

---

## Tests Folder (208 Files) - Already Implemented

These diagnostics have been fully implemented and tested.

---

## Implementation Workflow

### For WordPress State Checks

1. **Open TODO file** (e.g., `class-diagnostic-core-auto-updates-enabled.php`)
2. **Review the implementation guidance comments**
3. **Identify what to check:**
   - What WordPress option/setting/constant?
   - What value indicates pass vs fail?
4. **Implement `check()` method:**
   ```php
   public static function check(): ?array {
       // Query WordPress state
       // Evaluate against criteria
       // Return null (pass) or array (fail)
   }
   ```
5. **Create unit test:**
   - Mock WordPress state
   - Call `check()`
   - Assert result
6. **Create integration test** on real WordPress instance
7. **Move to `tests/` folder** when complete

---

### For HTML/Accessibility Assessments

1. **Open TODO file** (e.g., `class-diagnostic-wcag-alt-text.php`)
2. **Review the HTML assessment guidance**
3. **Understand what Guardian will provide:**
   - HTML content of pages to inspect
   - Sample page structures
4. **Implement `check()` method:**
   ```php
   public static function check(): ?array {
       // Receive HTML from Guardian
       // Parse with DOMDocument
       // Validate against standard
       // Return findings or null
   }
   ```
5. **Create test with sample HTML:**
   - Create HTML with known issues
   - Verify detection
   - Create HTML with no issues
   - Verify passes
6. **Move to `tests/` folder** when complete

---

## Key Files with Implementation Guidance

Each TODO file now includes:

### WordPress State Checks
- **WORDPRESS STATE CHECK - READY FOR IMPLEMENTATION** section
- Lists common WordPress functions to use
- Shows example patterns
- Identifies likely options/settings to check

### HTML Assessments
- **HTML ASSESSMENT TEST - CURL-BASED IMPLEMENTATION** section
- Explains how Guardian provides HTML
- Shows DOMDocument usage
- Lists helper classes available
- Provides implementation steps

---

## Priority Order

Start with these for fastest wins:

### High Priority (Quick wins)
1. `core-auto-updates-enabled` - 15 min
2. `core-backup-tested` - 20 min
3. `admin-email` - 20 min
4. `ssl-enabled` - 20 min
5. `https-everywhere` - 20 min

### Medium Priority
6. Plugin activation checks
7. User role/capability checks
8. Basic WCAG checks (alt text, headings)

### Later Priority
9. Complex correlation checks
10. Advanced accessibility checks

---

## Statistics

```
Initial State:
- help: 256 files (stubs)
- todo: 0 files
- helpx2: 174 files
- tests: 208 files

Processing Steps:
1. Initial categorization: +103 todo, +14 helpx2 from help
2. Reclassify HTML assessments: +116 todo from helpx2
3. Move remaining stubs: +62 todo from help

Final State:
- help: 0 files ✓
- todo: 281 files (ready)
- helpx2: 149 files (strategic)
- tests: 208 files (done)

Total: 838 diagnostic files
```

---

## Next Steps

1. **Pick first TODO file** from high-priority list
2. **Read implementation guidance** comments
3. **Implement check() method**
4. **Create test cases**
5. **Move to tests/ folder**
6. **Repeat** for remaining files

**Estimated timeline:**
- Simple WordPress checks: 30 min each
- HTML assessments: 45 min each
- All 281 files: ~4-5 weeks (with team)

---

## Files with Clear Implementation Paths

All TODO files now include:
- ✅ Clear question/goal
- ✅ Category and slug
- ✅ Implementation strategy
- ✅ Examples and patterns
- ✅ WordPress functions to use
- ✅ Test approach

**Ready to start coding!**

