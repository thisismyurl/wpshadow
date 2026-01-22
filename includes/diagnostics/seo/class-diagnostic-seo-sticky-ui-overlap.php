<?php
declare(strict_types=1);
/**
 * Sticky UI Overlap Diagnostic
 *
 * Philosophy: Avoid sticky headers obscuring content
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Sticky_UI_Overlap extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-sticky-ui-overlap',
            'title' => 'Sticky UI Overlap',
            'description' => 'Sticky headers/footers should not obscure content or important CTAs on mobile and desktop.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/sticky-ui-overlap/',
            'training_link' => 'https://wpshadow.com/training/mobile-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
