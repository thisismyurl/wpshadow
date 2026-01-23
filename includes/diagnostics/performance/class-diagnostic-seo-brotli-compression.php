<?php
declare(strict_types=1);
/**
 * Brotli Compression Diagnostic
 *
 * Philosophy: Brotli compresses better than gzip
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Brotli_Compression extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-brotli-compression',
            'title' => 'Brotli Compression Support',
            'description' => 'Enable Brotli compression for 15-20% better compression than gzip. Requires server support.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/brotli-compression/',
            'training_link' => 'https://wpshadow.com/training/compression-optimization/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }

}