<?php
declare(strict_types=1);
/**
 * DNS Lookup Time Diagnostic
 *
 * Philosophy: Fast DNS resolution is first step
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_DNS_Lookup_Time extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-dns-lookup-time',
            'title' => 'DNS Lookup Performance',
            'description' => 'DNS lookups should complete under 20ms. Consider fast DNS providers like Cloudflare or Route53.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/dns-optimization/',
            'training_link' => 'https://wpshadow.com/training/infrastructure-seo/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }

}