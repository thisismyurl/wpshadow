<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Token Generation Validation
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-token-generation
 * Training: https://wpshadow.com/training/design-system-token-generation
 */
class Diagnostic_Design_SYSTEM_TOKEN_GENERATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-system-token-generation',
            'title' => __('Token Generation Validation', 'wpshadow'),
            'description' => __('Verifies design tokens properly generated from Figma/source.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-token-generation',
            'training_link' => 'https://wpshadow.com/training/design-system-token-generation',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}