<?php
declare(strict_types=1);
/**
 * Event Schema Completeness Diagnostic
 *
 * Philosophy: Complete event markup for rich results
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Event_Schema_Completeness extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-event-schema-completeness',
            'title' => 'Event Schema Completeness',
            'description' => 'Ensure Event structured data includes startDate, location, and offers for rich result eligibility.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/event-schema/',
            'training_link' => 'https://wpshadow.com/training/schema-serp-features/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }

}