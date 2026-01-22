<?php
declare(strict_types=1);
/**
 * DNSSEC Validation Diagnostic
 *
 * Philosophy: DNSSEC prevents DNS spoofing
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_DNSSEC_Validation extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-dnssec-validation',
            'title' => 'DNSSEC Implementation',
            'description' => 'Enable DNSSEC to protect against DNS spoofing and cache poisoning attacks.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/dnssec/',
            'training_link' => 'https://wpshadow.com/training/dns-security/',
            'auto_fixable' => false,
            'threat_level' => 30,
        ];
    }
}
