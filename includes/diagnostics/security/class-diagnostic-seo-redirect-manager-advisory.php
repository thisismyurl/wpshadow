<?php
declare(strict_types=1);
/**
 * Redirect Manager Advisory Diagnostic
 *
 * Philosophy: Clean URL change management
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Redirect_Manager_Advisory extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-redirect-manager-advisory',
            'title' => 'Redirect Manager Setup',
            'description' => 'Use a redirect manager to track URL changes and maintain clean 301 redirects without chains.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/redirect-management/',
            'training_link' => 'https://wpshadow.com/training/redirects-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
