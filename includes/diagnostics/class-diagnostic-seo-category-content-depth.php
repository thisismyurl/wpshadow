<?php declare(strict_types=1);
/**
 * Category Content Depth Diagnostic
 *
 * Philosophy: Add intro copy above product grids
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Category_Content_Depth {
    public static function check() {
        return [
            'id' => 'seo-category-content-depth',
            'title' => 'Category Content Depth',
            'description' => 'Add meaningful introductory copy above product grids to improve category page SEO and context.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/category-content/',
            'training_link' => 'https://wpshadow.com/training/ecommerce-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
