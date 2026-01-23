<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Template Fallback Chain
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-template-fallback-chain
 * Training: https://wpshadow.com/training/design-template-fallback-chain
 */
class Diagnostic_Design_TEMPLATE_FALLBACK_CHAIN extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-template-fallback-chain',
            'title' => __('Template Fallback Chain', 'wpshadow'),
            'description' => __('Checks template fallback order correct (index.php catch-all).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-template-fallback-chain',
            'training_link' => 'https://wpshadow.com/training/design-template-fallback-chain',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}