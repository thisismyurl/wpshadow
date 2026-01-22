<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_Visual_Content_Gap extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-visual-content-gap', 'title' => __('Visual Content Gap Analysis', 'wpshadow'), 'description' => __('Compares visual content: images per article, infographics, videos, charts. If competitors have rich visuals and you have none, engagement and ranking gap exists.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/visual-seo/', 'training_link' => 'https://wpshadow.com/training/image-optimization/', 'auto_fixable' => false, 'threat_level' => 5];
    }
}
