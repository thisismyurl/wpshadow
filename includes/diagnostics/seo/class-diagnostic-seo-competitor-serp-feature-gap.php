<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_Competitor_SERP_Feature_Gap extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-competitor-serp-gap', 'title' => __('Competitor SERP Feature Coverage Gap', 'wpshadow'), 'description' => __('Compares your SERP feature coverage against top 10 competitors. Identifies which rich results you\'re missing (FAQ, Reviews, How-To, Events, Video, Jobs).', 'wpshadow'), 'severity' => 'high', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/serp-features/', 'training_link' => 'https://wpshadow.com/training/rich-results/', 'auto_fixable' => false, 'threat_level' => 8];
    }

}