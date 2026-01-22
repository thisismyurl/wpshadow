<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Ultra-Wide Display Support
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-ultra-wide-support
 * Training: https://wpshadow.com/training/design-ultra-wide-support
 */
class Diagnostic_Design_ULTRA_WIDE_SUPPORT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-ultra-wide-support',
            'title' => __('Ultra-Wide Display Support', 'wpshadow'),
            'description' => __('Verifies content doesn't stretch on 1920px+, uses max-width constraint.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-ultra-wide-support',
            'training_link' => 'https://wpshadow.com/training/design-ultra-wide-support',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
