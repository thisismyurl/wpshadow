<?php
declare(strict_types=1);
/**
 * AJAX Content Indexability Diagnostic
 *
 * Philosophy: AJAX-loaded content may not be indexed
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_AJAX_Content_Indexability extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-ajax-content-indexability',
            'title' => 'AJAX Content Indexability',
            'description' => 'Verify AJAX-loaded content is indexable. Use History API and ensure content is in initial HTML or properly rendered.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/ajax-seo/',
            'training_link' => 'https://wpshadow.com/training/dynamic-content-seo/',
            'auto_fixable' => false,
            'threat_level' => 50,
        ];
    }
}
