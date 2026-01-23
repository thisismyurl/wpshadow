<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_Crawl_Waste_Detection extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-crawl-waste', 'title' => __('Crawl Waste Detection', 'wpshadow'), 'description' => __('Identifies pages Google wastes crawl budget on: session IDs, tracking parameters, duplicate content, printer-friendly versions. Fixing frees budget for new content.', 'wpshadow'), 'severity' => 'high', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/crawl-optimization/', 'training_link' => 'https://wpshadow.com/training/crawl-budget/', 'auto_fixable' => false, 'threat_level' => 8];
    }

}