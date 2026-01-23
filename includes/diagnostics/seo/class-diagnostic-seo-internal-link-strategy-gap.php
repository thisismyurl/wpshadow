<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_Internal_Link_Strategy_Gap extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-internal-link-gap', 'title' => __('Internal Link Strategy Gap', 'wpshadow'), 'description' => __('Compares internal linking sophistication. If competitors link contextually to 20+ related articles and you link to 3, link equity distribution gap exists.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/internal-linking/', 'training_link' => 'https://wpshadow.com/training/link-architecture/', 'auto_fixable' => false, 'threat_level' => 6];
    }

}