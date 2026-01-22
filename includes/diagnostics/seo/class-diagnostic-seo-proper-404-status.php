<?php
declare(strict_types=1);
/**
 * Proper 404 Status Diagnostic
 *
 * Philosophy: Ensure error pages return correct HTTP status
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Proper_404_Status extends Diagnostic_Base {
    /**
     * Advisory: confirm custom 404 page returns HTTP 404, not 200.
     *
     * @return array|null
     */
    public static function check(): ?array {
        return [
            'id' => 'seo-proper-404-status',
            'title' => '404 Pages Should Return HTTP 404',
            'description' => 'Ensure the 404 page template returns HTTP status 404. A 200 response for missing pages causes soft 404 issues.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/404-http-status/',
            'training_link' => 'https://wpshadow.com/training/http-status-seo/',
            'auto_fixable' => false,
            'threat_level' => 45,
        ];
    }
}
