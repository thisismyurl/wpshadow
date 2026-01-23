<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Shadow Scale Adherence
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-shadow-scale-adherence
 * Training: https://wpshadow.com/training/design-shadow-scale-adherence
 */
class Diagnostic_Design_DESIGN_SHADOW_SCALE_ADHERENCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-shadow-scale-adherence',
            'title' => __('Shadow Scale Adherence', 'wpshadow'),
            'description' => __('Flags shadows that are not on the shadow scale.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-shadow-scale-adherence',
            'training_link' => 'https://wpshadow.com/training/design-shadow-scale-adherence',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}