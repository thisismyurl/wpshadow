<?php
declare(strict_types=1);
/**
 * Sitewide Link Patterns Diagnostic
 *
 * Philosophy: Avoid sitewide footer/sidebar links
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Sitewide_Link_Patterns extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-sitewide-link-patterns',
            'title' => 'Sitewide Link Usage',
            'description' => 'Limit sitewide links (footer, sidebar). They dilute value and look manipulative.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/sitewide-links/',
            'training_link' => 'https://wpshadow.com/training/link-architecture/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }

}