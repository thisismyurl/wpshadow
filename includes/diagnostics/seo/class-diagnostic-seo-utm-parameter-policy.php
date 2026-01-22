<?php
declare(strict_types=1);
/**
 * UTM Parameter Policy Diagnostic
 *
 * Philosophy: Consistent tracking parameter handling
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_UTM_Parameter_Policy extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-utm-parameter-policy',
            'title' => 'UTM Parameter Policy',
            'description' => 'Establish clear policy for UTM parameter handling (strip, retain, canonicalize) to avoid duplicate indexation.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/utm-parameters/',
            'training_link' => 'https://wpshadow.com/training/tracking-parameters/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
