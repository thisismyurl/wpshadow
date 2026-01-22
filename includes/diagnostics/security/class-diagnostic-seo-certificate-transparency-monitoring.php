<?php
declare(strict_types=1);
/**
 * Certificate Transparency Monitoring Diagnostic
 *
 * Philosophy: Monitor SSL certificate issuance
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Certificate_Transparency_Monitoring extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-certificate-transparency-monitoring',
            'title' => 'Certificate Transparency Monitoring',
            'description' => 'Monitor Certificate Transparency logs for unauthorized SSL certificates for your domain.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/certificate-transparency/',
            'training_link' => 'https://wpshadow.com/training/ssl-monitoring/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
