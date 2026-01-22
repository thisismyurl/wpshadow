<?php
declare(strict_types=1);
/**
 * Footer Utility Links Diagnostic
 *
 * Philosophy: Crawlable, not excessive
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Footer_Utility_Links extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-footer-utility-links',
            'title' => 'Footer Utility Links',
            'description' => 'Ensure footer links are crawlable and not excessive; focus on important pages and avoid link farms.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/footer-links/',
            'training_link' => 'https://wpshadow.com/training/site-architecture/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
