<?php
declare(strict_types=1);
/**
 * CAA Records Configuration Diagnostic
 *
 * Philosophy: CAA prevents unauthorized certificates
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_CAA_Records_Configuration extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-caa-records-configuration',
            'title' => 'CAA DNS Records',
            'description' => 'Configure CAA records to specify which CAs can issue certificates for your domain.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/caa-records/',
            'training_link' => 'https://wpshadow.com/training/certificate-authority/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }

}