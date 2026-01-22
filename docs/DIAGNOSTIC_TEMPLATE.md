# WPShadow Diagnostic Template & Authoring Guide

This guide provides a production-ready template and checklist for creating new diagnostics that fully align with WPShadow’s philosophy, security, and architecture.

## Philosophy Requirements (Must-Haves)
- Helpful neighbor: plain-English name + description (no jargon)
- Free forever locally: no paywalls, no upsells
- Educational: include a KB link and training link
- Show value: track KPIs (time saved, issues found)
- Privacy-first: no data collection beyond what’s needed

## Technical Requirements (Must-Haves)
- `declare(strict_types=1);`
- Namespace: `WPShadow\\Diagnostics`
- Class filename: `class-diagnostic-{slug}.php`
- Extend: `WPShadow\\Core\\Diagnostic_Base`
- Type-hinted public API where applicable
- No `eval()`, no raw SQL; use WordPress APIs

## Minimal Diagnostic Lifecycle
- `get_slug()`: unique slug (e.g., `seo-missing-h1`)
- `get_name()`: user-friendly name
- `get_description()`: plain-English + KB link
- `get_category()`: e.g., `seo`, `performance`, `security`, `content`, `database`
- `run()`: perform checks, return findings in the base class format
- Optional: `get_severity()`, `get_estimated_time_saved_minutes()`

## KPI Tracking
Use `KPI_Tracker` to record value:
```php
use WPShadow\\Core\\KPI_Tracker;
KPI_Tracker::record_diagnostic_run('seo-missing-h1', true);
// When producing a concrete finding resolution suggestion:
KPI_Tracker::record_finding_resolved('seo-missing-h1', 'medium');
```

## Example Template (Copy-Paste)
```php
<?php
declare(strict_types=1);

namespace WPShadow\\Diagnostics;

use WPShadow\\Core\\Diagnostic_Base;
use WPShadow\\Core\\KPI_Tracker;

/**
 * Diagnostic: SEO - Missing H1 Tag
 *
 * Philosophy: Shows value (#9) by finding content issues quickly.
 * KB Link: https://wpshadow.com/kb/seo-missing-h1
 * Training: https://wpshadow.com/training/seo-missing-h1
 */
class Diagnostic_SEO_Missing_H1 extends Diagnostic_Base {
    /**
     * Machine slug
     */
    public static function get_slug(): string {
        return 'seo-missing-h1';
    }

    /**
     * Human-readable name
     */
    public static function get_name(): string {
        return __('SEO: Missing H1 Tag', 'wpshadow');
    }

    /**
     * Plain-English description with KB link
     */
    public static function get_description(): string {
        return sprintf(
            __('Checks posts/pages for missing H1 tags. <a href="%s" target="_blank">Learn why this matters</a>.', 'wpshadow'),
            'https://wpshadow.com/kb/seo-missing-h1'
        );
    }

    /**
     * Category (for filtering/UX)
     */
    public static function get_category(): string {
        return 'seo';
    }

    /**
     * Run the diagnostic and return findings
     *
     * @return array Findings in base class format
     */
    public static function run(): array {
        // Example sketch (replace with actual logic using WP_Query):
        // - Find N posts missing H1
        // - Create findings array with identifiers and remediation suggestions
        $findings = [];

        // Track KPI (philosophy #9)
        KPI_Tracker::record_diagnostic_run(self::get_slug(), true);

        return $findings;
    }
}
```

## Authoring Checklist
- Name: Clear, non-technical title
- Description: Includes KB link + rationale
- Category: One of known categories
- Impact: Make sure slug exists in impact map or describe factors
- Privacy: No external calls unless necessary (document why)
- Performance: Prefer cached lookups / batch queries

## Impact & Guardian Planning
- Estimate cost using `Performance_Impact_Classifier`
- For heavy checks (HTTP, content-wide scans), plan as `scheduled`
- For light checks (options, 1-2 queries), plan as `anytime`

## Common Anti-Patterns (Avoid)
- Running heavy content scans during admin page load
- Adding inline SQL instead of using WP APIs
- Omitting KB/training links
- No KPI tracking calls

## New Diagnostic Scaffolding
Use the scaffolding tool to generate boilerplate from this template:
```
php tools/new-diagnostic.php --slug="seo-missing-h1" --name="SEO: Missing H1 Tag" --category=seo
```
