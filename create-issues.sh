#!/bin/bash

# Create GitHub issues for converting three tools to use diagnostic system

REPO="thisismyurl/wpshadow"

# Issue 1: Mobile Friendliness
gh issue create \
  --repo "$REPO" \
  --title "Create diagnostic classes for Mobile Friendliness checks" \
  --label "enhancement,diagnostics,mobile,architecture" \
  --body "## Overview
The Mobile Friendliness tool currently uses inline analysis in \`wpshadow_analyze_mobile_html()\` helper function. These checks should be refactored into diagnostic classes to enable reuse across Dashboard, CLI, Workflows, and Guardian.

## Current Implementation
**Location:** \`includes/utils/class-analysis-helpers.php\` - \`wpshadow_analyze_mobile_html()\`  
**AJAX Handler:** \`includes/admin/ajax/Mobile_Check_Handler.php\`  
**Tool Page:** \`includes/views/tools/mobile-friendliness.php\`

## Checks to Convert

### 1. Viewport Meta Tag (\`Diagnostic_Viewport_Meta_Tag\`)
- **Check:** Page has \`<meta name=\"viewport\">\` tag  
- **Severity:** Critical (fail if missing)  
- **Auto-fixable:** No

### 2. Viewport Device Width (\`Diagnostic_Viewport_Device_Width\`)
- **Check:** Viewport includes \`width=device-width\`  
- **Severity:** Medium (warn if missing)  
- **Auto-fixable:** No

### 3. Viewport Initial Scale (\`Diagnostic_Viewport_Initial_Scale\`)
- **Check:** Viewport includes \`initial-scale\` setting  
- **Severity:** Low (warn if missing)  
- **Auto-fixable:** No

### 4. Zoom Disabled (\`Diagnostic_Zoom_Not_Disabled\`)
- **Check:** Zoom NOT disabled via \`user-scalable=no\` or \`maximum-scale=1\`  
- **Severity:** Medium (warn if disabled - accessibility issue)  
- **Auto-fixable:** No

### 5. Readable Font Sizes (\`Diagnostic_Mobile_Font_Size\`)
- **Check:** No font-size declarations under 14px  
- **Severity:** Low (warn if found)  
- **Auto-fixable:** No

### 6. No Fixed Wide Elements (\`Diagnostic_Mobile_Wide_Elements\`)
- **Check:** No tables/elements with fixed widths >= 960px  
- **Severity:** Medium (warn if found)  
- **Auto-fixable:** No

### 7. Tap Targets Sized (\`Diagnostic_Mobile_Tap_Targets\`)
- **Check:** Links/buttons have adequate spacing  
- **Severity:** Medium (warn if issues)  
- **Auto-fixable:** No

## Benefits
✅ Reuse across Dashboard health checks  
✅ CLI can run mobile checks  
✅ Workflows can auto-check mobile friendliness  
✅ Guardian can monitor for viewport changes  
✅ Consistent with 2,179 existing diagnostics  

## Acceptance Criteria
- [ ] 7 diagnostic classes created in \`includes/diagnostics/tests/mobile/\`
- [ ] Registered in \`Diagnostic_Registry\`
- [ ] \`Mobile_Check_Handler\` uses \`Diagnostic_Registry::get_by_family('mobile')\`
- [ ] Helper function removed
- [ ] KB articles created"

# Issue 2: Accessibility
gh issue create \
  --repo "$REPO" \
  --title "Create diagnostic classes for Accessibility Audit checks" \
  --label "enhancement,diagnostics,accessibility,architecture,wcag" \
  --body "## Overview
The Accessibility Audit tool currently uses a private \`analyze_a11y_html()\` method. These checks should be refactored into diagnostic classes to enable reuse across Dashboard, CLI, Workflows, and Guardian.

## Current Implementation
**Location:** \`includes/admin/ajax/A11y_Audit_Handler.php\` - \`analyze_a11y_html()\` private method  
**Tool Page:** \`includes/views/tools/accessibility-audit.php\`

## Checks to Convert

### 1. Image Alt Text (\`Diagnostic_Image_Alt_Text\`)
- **Check:** All \`<img>\` tags have alt attributes  
- **Severity:** Critical - WCAG Level A  
- **Auto-fixable:** No

### 2. Heading Hierarchy (\`Diagnostic_Heading_Hierarchy\`)
- **Check:** Proper H1-H6 structure without gaps  
- **Severity:** Medium - WCAG Level AA  
- **Auto-fixable:** No

### 3. H1 Tag Present (\`Diagnostic_H1_Tag_Present\`)
- **Check:** Page has exactly one H1 tag  
- **Severity:** Medium (SEO impact)  
- **Auto-fixable:** No

### 4. Form Labels & ARIA (\`Diagnostic_Form_Labels_ARIA\`)
- **Check:** Form elements have proper labels or ARIA  
- **Severity:** Critical - WCAG Level A  
- **Auto-fixable:** No

### 5. Language Attribute (\`Diagnostic_HTML_Lang_Attribute\`)
- **Check:** \`<html>\` tag has \`lang\` attribute  
- **Severity:** Critical - WCAG Level A  
- **Auto-fixable:** Yes

### 6. Skip to Content Link (\`Diagnostic_Skip_To_Content_Link\`)
- **Check:** Page has skip navigation link  
- **Severity:** Low - WCAG Level A best practice  
- **Auto-fixable:** No

### 7. Color Contrast (\`Diagnostic_Color_Contrast\`) - NEW
- **Check:** Text meets WCAG AA contrast ratios (4.5:1 normal, 3:1 large)  
- **Severity:** Critical - WCAG Level AA  
- **Auto-fixable:** No

### 8. Focus Indicators (\`Diagnostic_Focus_Indicators\`) - NEW
- **Check:** Interactive elements have visible focus styles  
- **Severity:** Medium - WCAG Level AA  
- **Auto-fixable:** No

## Benefits
✅ Reuse across Dashboard health checks  
✅ CLI can run a11y checks  
✅ WCAG compliance tracking  
✅ Aligns with **CANON pillars** (Accessibility First)  
✅ Guardian monitoring for accessibility regressions  

## Acceptance Criteria
- [ ] 8 diagnostic classes created in \`includes/diagnostics/tests/accessibility/\`
- [ ] Registered in \`Diagnostic_Registry\`
- [ ] \`A11y_Audit_Handler\` uses diagnostics
- [ ] Private method removed
- [ ] 2 new checks implemented (Color Contrast, Focus Indicators)
- [ ] WCAG level tagged on each diagnostic
- [ ] KB articles created"

# Issue 3: Broken Links
gh issue create \
  --repo "$REPO" \
  --title "Create diagnostic classes for Broken Link Checker" \
  --label "enhancement,diagnostics,seo,architecture" \
  --body "## Overview
The Broken Link Checker tool currently uses a private \`check_links_in_html()\` method. This should be refactored into diagnostic classes to enable reuse across Dashboard, CLI, Workflows, and Guardian.

## Current Implementation
**Location:** \`includes/admin/ajax/Check_Broken_Links_Handler.php\` - \`check_links_in_html()\` private method  
**Tool Page:** \`includes/views/tools/broken-link-checker.php\`

## Checks to Convert

### 1. Broken Internal Links (\`Diagnostic_Broken_Internal_Links\`)
- **Check:** All internal links return 200 status  
- **Severity:** Medium (UX and SEO impact)  
- **Auto-fixable:** No  
- **Family:** \`seo\`

### 2. Broken External Links (\`Diagnostic_Broken_External_Links\`)
- **Check:** All external links return valid status  
- **Severity:** Low  
- **Auto-fixable:** No  
- **Family:** \`content-quality\`

### 3. Slow External Resources (\`Diagnostic_Slow_External_Resources\`)
- **Check:** External resources load within threshold  
- **Severity:** Medium (performance impact)  
- **Auto-fixable:** No  
- **Family:** \`performance\`

### 4. Redirect Chains (\`Diagnostic_Redirect_Chains\`)
- **Check:** Links don't have multiple redirects  
- **Severity:** Medium (SEO and performance)  
- **Auto-fixable:** Yes (update to final destination)  
- **Family:** \`seo\`

### 5. Mixed Content (\`Diagnostic_Mixed_Content_Links\`)
- **Check:** HTTPS pages don't link to HTTP resources  
- **Severity:** Critical (security warnings)  
- **Auto-fixable:** Yes (upgrade to HTTPS)  
- **Family:** \`security\`

### 6. Malformed URLs (\`Diagnostic_Malformed_URLs\`)
- **Check:** All URLs are properly formatted  
- **Severity:** Medium  
- **Auto-fixable:** No  
- **Family:** \`content-quality\`

## Performance Optimization Required
Link checking is slow (network requests). Diagnostics must:
- Cache results with 1 hour+ TTL
- Use \`wp_remote_head()\` not \`wp_remote_get()\`
- 5 second timeout per link
- Skip if > 100 links

## Scope
- Homepage links only (not entire site)
- Full site scans use cron/workflows

## Benefits
✅ Reuse across Dashboard health checks  
✅ CLI can check links  
✅ Workflow automation for content updates  
✅ Guardian monitoring  
✅ SEO and UX improvements  

## Acceptance Criteria
- [ ] 6 diagnostic classes created in \`includes/diagnostics/tests/seo/\` and \`content-quality/\`
- [ ] Registered in \`Diagnostic_Registry\`
- [ ] \`Check_Broken_Links_Handler\` uses diagnostics
- [ ] Private method removed
- [ ] Caching implemented
- [ ] Performance < 10 seconds for homepage
- [ ] KB articles created"

echo "✅ All 3 issues created successfully"
