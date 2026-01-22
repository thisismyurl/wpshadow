<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Woo Template Alignment
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-woo-template-alignment
 * Training: https://wpshadow.com/training/design-woo-template-alignment
 */
class Diagnostic_Design_DESIGN_WOO_TEMPLATE_ALIGNMENT {
    public static function check() {
        return [
            'id' => 'design-woo-template-alignment',
            'title' => __('Woo Template Alignment', 'wpshadow'),
            'description' => __('Checks WooCommerce templates align with theme parts.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-woo-template-alignment',
            'training_link' => 'https://wpshadow.com/training/design-woo-template-alignment',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

