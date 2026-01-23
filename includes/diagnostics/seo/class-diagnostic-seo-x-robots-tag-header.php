<?php
declare(strict_types=1);
/**
 * X-Robots-Tag Header Diagnostic
 *
 * Philosophy: HTTP headers override meta robots
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_X_Robots_Tag_Header extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-x-robots-tag-header',
            'title' => 'X-Robots-Tag HTTP Header',
            'description' => 'Use X-Robots-Tag header for non-HTML resources (PDFs, images) to control indexation.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/x-robots-tag/',
            'training_link' => 'https://wpshadow.com/training/robots-directives/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }

}