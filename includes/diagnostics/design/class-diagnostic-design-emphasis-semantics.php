<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Emphasis Semantics
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-emphasis-semantics
 * Training: https://wpshadow.com/training/design-emphasis-semantics
 */
class Diagnostic_Design_EMPHASIS_SEMANTICS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-emphasis-semantics',
            'title' => __('Emphasis Semantics', 'wpshadow'),
            'description' => __('Checks bold uses <strong>, italics uses <em>.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-emphasis-semantics',
            'training_link' => 'https://wpshadow.com/training/design-emphasis-semantics',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
