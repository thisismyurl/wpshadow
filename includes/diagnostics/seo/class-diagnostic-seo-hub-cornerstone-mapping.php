<?php
declare(strict_types=1);
/**
 * Hub Cornerstone Mapping Diagnostic
 *
 * Philosophy: Cluster pages link to pillar content
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Hub_Cornerstone_Mapping extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-hub-cornerstone-mapping',
            'title' => 'Hub/Cornerstone Content Mapping',
            'description' => 'Ensure cluster pages link back to pillar/cornerstone content to reinforce topical authority.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/content-clusters/',
            'training_link' => 'https://wpshadow.com/training/content-strategy/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
