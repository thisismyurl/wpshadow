<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_Media_Coverage_Gap extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-media-gap', 'title' => __('Media Coverage Gap', 'wpshadow'), 'description' => __('Analyzes competitors\' media mentions, brand citations, and press coverage. If competitors are mentioned in authoritative publications and you aren\'t, E-E-A-T gap exists.', 'wpshadow'), 'severity' => 'high', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/pr-strategy/', 'training_link' => 'https://wpshadow.com/training/media-relations/', 'auto_fixable' => false, 'threat_level' => 7];
    }

}