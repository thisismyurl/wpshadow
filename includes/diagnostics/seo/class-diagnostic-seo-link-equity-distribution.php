<?php
declare(strict_types=1);
/**
 * Link Equity Distribution Diagnostic
 *
 * Philosophy: Distribute PageRank strategically
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Link_Equity_Distribution extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-link-equity-distribution',
            'title' => 'Link Equity Flow',
            'description' => 'Optimize internal link distribution to flow equity to important pages.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/link-equity/',
            'training_link' => 'https://wpshadow.com/training/pagerank-sculpting/',
            'auto_fixable' => false,
            'threat_level' => 30,
        ];
    }

}