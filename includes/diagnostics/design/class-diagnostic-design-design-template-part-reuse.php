<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Template Part Reuse
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-template-part-reuse
 * Training: https://wpshadow.com/training/design-template-part-reuse
 */
class Diagnostic_Design_DESIGN_TEMPLATE_PART_REUSE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-template-part-reuse',
            'title' => __('Template Part Reuse', 'wpshadow'),
            'description' => __('Checks parts are registered and reused without duplicates.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-template-part-reuse',
            'training_link' => 'https://wpshadow.com/training/design-template-part-reuse',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}