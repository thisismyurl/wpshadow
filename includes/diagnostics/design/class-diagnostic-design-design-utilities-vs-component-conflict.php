<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Utilities vs Component Conflict
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-utilities-vs-component-conflict
 * Training: https://wpshadow.com/training/design-utilities-vs-component-conflict
 */
class Diagnostic_Design_DESIGN_UTILITIES_VS_COMPONENT_CONFLICT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-utilities-vs-component-conflict',
            'title' => __('Utilities vs Component Conflict', 'wpshadow'),
            'description' => __('Detects utility classes overriding component styles.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-utilities-vs-component-conflict',
            'training_link' => 'https://wpshadow.com/training/design-utilities-vs-component-conflict',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}