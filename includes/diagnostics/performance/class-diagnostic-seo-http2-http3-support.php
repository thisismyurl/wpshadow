<?php
declare(strict_types=1);
/**
 * HTTP2/HTTP3 Support Diagnostic
 *
 * Philosophy: Modern protocols improve multiplexing
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_HTTP2_HTTP3_Support extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-http2-http3-support',
            'title' => 'HTTP/2 or HTTP/3 Protocol',
            'description' => 'Use HTTP/2 or HTTP/3 for multiplexing and faster connections. Verify server support.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/http2-http3/',
            'training_link' => 'https://wpshadow.com/training/modern-protocols/',
            'auto_fixable' => false,
            'threat_level' => 40,
        ];
    }
}
