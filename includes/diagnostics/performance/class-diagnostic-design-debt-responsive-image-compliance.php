<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Responsive Image Compliance
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-responsive-image-compliance
 * Training: https://wpshadow.com/training/design-debt-responsive-image-compliance
 */
class Diagnostic_Design_DEBT_RESPONSIVE_IMAGE_COMPLIANCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-responsive-image-compliance',
            'title' => __('Responsive Image Compliance', 'wpshadow'),
            'description' => __('% of images using srcset/sizes.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-responsive-image-compliance',
            'training_link' => 'https://wpshadow.com/training/design-debt-responsive-image-compliance',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}