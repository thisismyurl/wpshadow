<?php
declare(strict_types=1);
/**
 * Link Placement Analysis Diagnostic
 *
 * Philosophy: Above-fold links carry more weight
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Link_Placement_Analysis extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-link-placement-analysis',
            'title' => 'Link Placement Strategy',
            'description' => 'Place important internal links higher in content. Position affects link value.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/link-placement/',
            'training_link' => 'https://wpshadow.com/training/internal-linking/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }

}