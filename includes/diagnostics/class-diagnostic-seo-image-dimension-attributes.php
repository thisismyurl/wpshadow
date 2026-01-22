<?php declare(strict_types=1);
/**
 * Image Dimension Attributes Diagnostic
 *
 * Philosophy: Provide width/height to avoid layout shift
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Image_Dimension_Attributes {
    public static function check() {
        return [
            'id' => 'seo-image-dimension-attributes',
            'title' => 'Image Dimension Attributes',
            'description' => 'Ensure images include explicit width and height to prevent layout shift (CLS).',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/image-dimensions/',
            'training_link' => 'https://wpshadow.com/training/core-web-vitals/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
