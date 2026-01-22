<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_Orphaned_High_Value_Pages extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-orphaned-high-value', 'title' => __('Orphaned High-Value Page Detection', 'wpshadow'), 'description' => __('Finds high-authority pages (internal PR, backlinks, keywords) that are orphaned (no internal links). These waste crawl budget and link equity—accessibility fix improves rankings.', 'wpshadow'), 'severity' => 'high', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/link-equity/', 'training_link' => 'https://wpshadow.com/training/internal-architecture/', 'auto_fixable' => false, 'threat_level' => 8];
    }
}
