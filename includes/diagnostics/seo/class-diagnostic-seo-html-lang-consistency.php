<?php
declare(strict_types=1);
/**
 * HTML lang Consistency Diagnostic
 *
 * Philosophy: Align site locale with declared HTML language
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_HTML_Lang_Consistency extends Diagnostic_Base {
    /**
     * Advisory: ensure <html lang> matches site locale.
     *
     * @return array|null
     */
    public static function check(): ?array {
        $locale = get_locale();
        return [
            'id' => 'seo-html-lang-consistency',
            'title' => 'HTML lang Consistency',
            'description' => sprintf('Ensure the <html lang> attribute matches the site locale (%s) across all templates.', esc_html($locale)),
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/html-lang-attribute/',
            'training_link' => 'https://wpshadow.com/training/international-seo/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
