<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: VRT Template Baselines
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-template-baselines
 * Training: https://wpshadow.com/training/design-vrt-template-baselines
 */
class Diagnostic_Design_DESIGN_VRT_TEMPLATE_BASELINES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-vrt-template-baselines',
            'title' => __('VRT Template Baselines', 'wpshadow'),
            'description' => __('Maintains per-template baseline screenshots for diffs.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-template-baselines',
            'training_link' => 'https://wpshadow.com/training/design-vrt-template-baselines',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}