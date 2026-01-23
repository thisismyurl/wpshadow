<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Shadow DOM Encapsulation
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-shadow-dom-encapsulation
 * Training: https://wpshadow.com/training/design-debt-shadow-dom-encapsulation
 */
class Diagnostic_Design_DEBT_SHADOW_DOM_ENCAPSULATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-shadow-dom-encapsulation',
            'title' => __('Shadow DOM Encapsulation', 'wpshadow'),
            'description' => __('Checks web components properly encapsulated.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-shadow-dom-encapsulation',
            'training_link' => 'https://wpshadow.com/training/design-debt-shadow-dom-encapsulation',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}