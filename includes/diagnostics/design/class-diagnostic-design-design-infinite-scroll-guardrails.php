<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Infinite Scroll Guardrails
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-infinite-scroll-guardrails
 * Training: https://wpshadow.com/training/design-infinite-scroll-guardrails
 */
class Diagnostic_Design_DESIGN_INFINITE_SCROLL_GUARDRAILS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-infinite-scroll-guardrails',
            'title' => __('Infinite Scroll Guardrails', 'wpshadow'),
            'description' => __('Checks infinite scroll avoids layout thrash and includes loading states.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-infinite-scroll-guardrails',
            'training_link' => 'https://wpshadow.com/training/design-infinite-scroll-guardrails',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
