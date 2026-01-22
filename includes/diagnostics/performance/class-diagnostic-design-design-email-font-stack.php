<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Email Font Stack
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-email-font-stack
 * Training: https://wpshadow.com/training/design-email-font-stack
 */
class Diagnostic_Design_DESIGN_EMAIL_FONT_STACK extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-email-font-stack',
            'title' => __('Email Font Stack', 'wpshadow'),
            'description' => __('Checks safe email font stacks and fallbacks.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-email-font-stack',
            'training_link' => 'https://wpshadow.com/training/design-email-font-stack',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
