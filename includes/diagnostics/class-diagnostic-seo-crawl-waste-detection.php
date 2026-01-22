<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_Crawl_Waste_Detection {
    public static function check() {
        return ['id' => 'seo-crawl-waste', 'title' => __('Crawl Waste Detection', 'wpshadow'), 'description' => __('Identifies pages Google wastes crawl budget on: session IDs, tracking parameters, duplicate content, printer-friendly versions. Fixing frees budget for new content.', 'wpshadow'), 'severity' => 'high', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/crawl-optimization/', 'training_link' => 'https://wpshadow.com/training/crawl-budget/', 'auto_fixable' => false, 'threat_level' => 8];
    }
}
