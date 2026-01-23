<?php
declare(strict_types=1);
/**
 * NAP Consistency Diagnostic
 *
 * Philosophy: Consistent business info across site
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_NAP_Consistency extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-nap-consistency',
            'title' => 'NAP Consistency',
            'description' => 'Ensure Name, Address, and Phone (NAP) are consistent across footer, contact page, and schema markup.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/nap-consistency/',
            'training_link' => 'https://wpshadow.com/training/local-seo/',
            'auto_fixable' => false,
            'threat_level' => 35,
        ];
    }

}