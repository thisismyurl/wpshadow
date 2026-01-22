<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_Canonicalization_Efficiency extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-canonicalization', 'title' => __('Canonicalization Efficiency', 'wpshadow'), 'description' => __('Checks canonical tag consistency. Missing canonicals, self-referential canonicals, or canonical chains confuse Google crawl budget allocation. Consolidate URLs properly.', 'wpshadow'), 'severity' => 'high', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/canonical-tags/', 'training_link' => 'https://wpshadow.com/training/url-consolidation/', 'auto_fixable' => false, 'threat_level' => 8];
    }
}
