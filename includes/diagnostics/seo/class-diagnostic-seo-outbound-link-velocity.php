<?php
declare(strict_types=1);
/**
 * Outbound Link Velocity Diagnostic
 *
 * Philosophy: Natural outbound link growth
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Outbound_Link_Velocity extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-outbound-link-velocity',
            'title' => 'Outbound Link Growth Pattern',
            'description' => 'Monitor outbound link velocity. Sudden spikes may indicate hacking or content spam.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/link-velocity/',
            'training_link' => 'https://wpshadow.com/training/link-patterns/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
