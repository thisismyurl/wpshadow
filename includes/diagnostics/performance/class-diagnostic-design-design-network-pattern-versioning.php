<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Network Pattern Versioning
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-network-pattern-versioning
 * Training: https://wpshadow.com/training/design-network-pattern-versioning
 */
class Diagnostic_Design_DESIGN_NETWORK_PATTERN_VERSIONING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-network-pattern-versioning',
            'title' => __('Network Pattern Versioning', 'wpshadow'),
            'description' => __('Checks pattern versions are synchronized across sites.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-network-pattern-versioning',
            'training_link' => 'https://wpshadow.com/training/design-network-pattern-versioning',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}