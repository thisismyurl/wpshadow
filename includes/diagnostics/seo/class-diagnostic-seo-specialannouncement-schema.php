<?php
declare(strict_types=1);
/**
 * SpecialAnnouncement Schema Diagnostic
 *
 * Philosophy: Announcement schema for timely info
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_SpecialAnnouncement_Schema extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-specialannouncement-schema',
            'title' => 'SpecialAnnouncement Schema Markup',
            'description' => 'Add SpecialAnnouncement schema for urgent notices, closures, or policy changes.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/announcement-schema/',
            'training_link' => 'https://wpshadow.com/training/timely-content/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
