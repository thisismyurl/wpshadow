<?php declare(strict_types=1);
/**
 * Locale Timezone Formats Diagnostic
 *
 * Philosophy: Display locale-appropriate dates/times
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Locale_Timezone_Formats {
    public static function check() {
        return [
            'id' => 'seo-locale-timezone-formats',
            'title' => 'Locale Timezone & Date Formats',
            'description' => 'Ensure dates and times display in locale-appropriate formats to improve UX and international signals.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/locale-date-formats/',
            'training_link' => 'https://wpshadow.com/training/international-seo/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
