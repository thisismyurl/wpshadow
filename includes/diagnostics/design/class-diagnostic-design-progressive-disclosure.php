<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Progressive Disclosure Implementation
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-progressive-disclosure
 * Training: https://wpshadow.com/training/design-progressive-disclosure
 */
class Diagnostic_Design_PROGRESSIVE_DISCLOSURE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-progressive-disclosure',
            'title' => __('Progressive Disclosure Implementation', 'wpshadow'),
            'description' => __('Verifies advanced options hidden by default.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-progressive-disclosure',
            'training_link' => 'https://wpshadow.com/training/design-progressive-disclosure',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}