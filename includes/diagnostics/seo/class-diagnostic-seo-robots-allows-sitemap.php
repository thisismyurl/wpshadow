<?php
declare(strict_types=1);
/**
 * Robots Allows Sitemap Diagnostic
 *
 * Philosophy: Robots.txt should expose sitemaps
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Robots_Allows_Sitemap extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-robots-allows-sitemap',
            'title' => 'Robots.txt Should List Sitemap',
            'description' => 'Ensure robots.txt includes a Sitemap directive and does not block sitemap paths.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/robots-txt-sitemap/',
            'training_link' => 'https://wpshadow.com/training/sitemaps-basics/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }

}