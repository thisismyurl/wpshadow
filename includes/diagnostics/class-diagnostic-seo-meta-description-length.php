<?php declare(strict_types=1);
/**
 * Meta Description Length Diagnostic
 *
 * Philosophy: Encourage descriptions within recommended range
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Meta_Description_Length {
    public static function check() {
        return [
            'id' => 'seo-meta-description-length',
            'title' => 'Meta Description Length',
            'description' => 'Encourage meta descriptions in the recommended range (approx. 120–160 characters) to improve CTR.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/meta-description-length/',
            'training_link' => 'https://wpshadow.com/training/onpage-seo/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
