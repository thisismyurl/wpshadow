<?php
declare(strict_types=1);
/**
 * Intrusive Interstitials Diagnostic
 *
 * Philosophy: Avoid intrusive popups blocking content
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Interstitials_Intrusive extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-interstitials-intrusive',
            'title' => 'Avoid Intrusive Interstitials',
            'description' => 'Ensure popups/interstitials are not intrusive and do not block content, particularly on mobile.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/intrusive-interstitials/',
            'training_link' => 'https://wpshadow.com/training/mobile-seo/',
            'auto_fixable' => false,
            'threat_level' => 40,
        ];
    }
}
