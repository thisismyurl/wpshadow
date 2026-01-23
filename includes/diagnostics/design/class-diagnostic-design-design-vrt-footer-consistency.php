<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: VRT Footer Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-footer-consistency
 * Training: https://wpshadow.com/training/design-vrt-footer-consistency
 */
class Diagnostic_Design_DESIGN_VRT_FOOTER_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-vrt-footer-consistency',
            'title' => __('VRT Footer Consistency', 'wpshadow'),
            'description' => __('Maintains footer baselines for columns, spacing, and link styles.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-footer-consistency',
            'training_link' => 'https://wpshadow.com/training/design-vrt-footer-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}