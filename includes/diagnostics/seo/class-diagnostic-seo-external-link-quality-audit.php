<?php
declare(strict_types=1);
/**
 * External Link Quality Audit Diagnostic
 *
 * Philosophy: Link to quality sources only
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_External_Link_Quality_Audit extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-external-link-quality-audit',
            'title' => 'External Link Quality',
            'description' => 'Audit outbound links. Link only to authoritative, relevant sources.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/external-links/',
            'training_link' => 'https://wpshadow.com/training/link-building/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }
}
