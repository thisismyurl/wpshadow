<?php
declare(strict_types=1);
/**
 * LCP Image Lazyload Diagnostic
 *
 * Philosophy: Avoid lazy-loading the largest contentful paint image
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_LCP_Image_Lazyload extends Diagnostic_Base {
    /**
     * Advisory: ensure critical above-the-fold images are not lazy-loaded.
     *
     * @return array|null
     */
    public static function check(): ?array {
        return [
            'id' => 'seo-lcp-image-lazyload',
            'title' => 'Avoid Lazy-Load on LCP Image',
            'description' => 'Ensure the largest above-the-fold image is not lazy-loaded and has explicit width/height to stabilize layout.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/lcp-image-best-practices/',
            'training_link' => 'https://wpshadow.com/training/core-web-vitals/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
