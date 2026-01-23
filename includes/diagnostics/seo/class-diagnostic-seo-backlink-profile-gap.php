<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_Backlink_Profile_Gap extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-backlink-gap', 'title' => __('Backlink Profile Gap Analysis', 'wpshadow'), 'description' => __('Compares your backlink profile quality, quantity, and sources against top 3 competitors. Identifies missing link opportunities and authority deficits.', 'wpshadow'), 'severity' => 'high', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/link-building/', 'training_link' => 'https://wpshadow.com/training/link-strategy/', 'auto_fixable' => false, 'threat_level' => 9];
    }

}