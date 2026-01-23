<?php
declare(strict_types=1);
/**
 * Title Truncation Risk Diagnostic
 *
 * Philosophy: Optimize titles for SERP display width
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Title_Truncation_Risk extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-title-truncation-risk',
            'title' => 'Title Truncation Risk',
            'description' => 'Check titles for potential SERP truncation risk and optimize for pixel width, not just character count.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/title-length-seo/',
            'training_link' => 'https://wpshadow.com/training/onpage-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }

}