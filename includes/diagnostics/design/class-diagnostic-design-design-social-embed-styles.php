<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Social Embed Styles
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-social-embed-styles
 * Training: https://wpshadow.com/training/design-social-embed-styles
 */
class Diagnostic_Design_DESIGN_SOCIAL_EMBED_STYLES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-social-embed-styles',
            'title' => __('Social Embed Styles', 'wpshadow'),
            'description' => __('Checks embedded posts are styled consistently.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-social-embed-styles',
            'training_link' => 'https://wpshadow.com/training/design-social-embed-styles',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
