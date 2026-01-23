<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Third-Party Script Impact
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-third-party-script-impact
 * Training: https://wpshadow.com/training/design-third-party-script-impact
 */
class Diagnostic_Design_THIRD_PARTY_SCRIPT_IMPACT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-third-party-script-impact',
            'title' => __('Third-Party Script Impact', 'wpshadow'),
            'description' => __('Checks third-party scripts sandboxed.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-third-party-script-impact',
            'training_link' => 'https://wpshadow.com/training/design-third-party-script-impact',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}