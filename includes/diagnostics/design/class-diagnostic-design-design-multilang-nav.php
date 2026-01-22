<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Multilang Navigation Resilience
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-multilang-nav
 * Training: https://wpshadow.com/training/design-multilang-nav
 */
class Diagnostic_Design_DESIGN_MULTILANG_NAV extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-multilang-nav',
            'title' => __('Multilang Navigation Resilience', 'wpshadow'),
            'description' => __('Checks nav/header/footer with long labels across languages.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-multilang-nav',
            'training_link' => 'https://wpshadow.com/training/design-multilang-nav',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
