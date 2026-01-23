<?php
declare(strict_types=1);
/**
 * Speed Index Diagnostic
 *
 * Philosophy: Visual completeness perception matters
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Speed_Index extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-speed-index',
            'title' => 'Speed Index Score',
            'description' => 'Speed Index should be under 3.4s. Optimize above-the-fold content to load first.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/speed-index/',
            'training_link' => 'https://wpshadow.com/training/perceived-performance/',
            'auto_fixable' => false,
            'threat_level' => 50,
        ];
    }

}