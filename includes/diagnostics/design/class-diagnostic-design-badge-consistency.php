<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Badge Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-badge-consistency
 * Training: https://wpshadow.com/training/design-badge-consistency
 */
class Diagnostic_Design_BADGE_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-badge-consistency',
            'title' => __('Badge Consistency', 'wpshadow'),
            'description' => __('Validates badges consistent sizing/colors.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-badge-consistency',
            'training_link' => 'https://wpshadow.com/training/design-badge-consistency',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}