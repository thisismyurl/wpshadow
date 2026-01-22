<?php
declare(strict_types=1);
/**
 * URL Parameter Proliferation Diagnostic
 *
 * Philosophy: Prevent crawl traps due to params
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_URL_Parameter_Proliferation extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-url-parameter-proliferation',
            'title' => 'URL Parameter Proliferation',
            'description' => 'Limit indexation of deep parameter combinations (filters, sort, tracking) to conserve crawl budget and avoid duplicates.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/url-parameters-seo/',
            'training_link' => 'https://wpshadow.com/training/crawl-budget/',
            'auto_fixable' => false,
            'threat_level' => 45,
        ];
    }
}
