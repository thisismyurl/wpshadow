<?php
declare(strict_types=1);
/**
 * Pagination UX Diagnostic
 *
 * Philosophy: Clear next/prev links for crawlers
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Pagination_UX extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-pagination-ux',
            'title' => 'Pagination UX and Crawlability',
            'description' => 'Ensure pagination has clear next/prev links with crawlable anchor tags.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/pagination-seo/',
            'training_link' => 'https://wpshadow.com/training/site-architecture/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }

}