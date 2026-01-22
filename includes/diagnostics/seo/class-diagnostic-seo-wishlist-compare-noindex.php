<?php
declare(strict_types=1);
/**
 * Wishlist Compare Pages Noindex Diagnostic
 *
 * Philosophy: Noindex utility pages
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Wishlist_Compare_Noindex extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-wishlist-compare-noindex',
            'title' => 'Noindex Wishlist/Compare Pages',
            'description' => 'Set utility pages like wishlist and compare to noindex to focus crawl budget on valuable content.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/utility-pages-noindex/',
            'training_link' => 'https://wpshadow.com/training/ecommerce-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
