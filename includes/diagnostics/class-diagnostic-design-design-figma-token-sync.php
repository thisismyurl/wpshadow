<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Figma Token Sync Check
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-figma-token-sync
 * Training: https://wpshadow.com/training/design-figma-token-sync
 */
class Diagnostic_Design_DESIGN_FIGMA_TOKEN_SYNC {
    public static function check() {
        return [
            'id' => 'design-figma-token-sync',
            'title' => __('Figma Token Sync Check', 'wpshadow'),
            'description' => __('Detects mismatches between theme.json tokens and exported tokens.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-figma-token-sync',
            'training_link' => 'https://wpshadow.com/training/design-figma-token-sync',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

