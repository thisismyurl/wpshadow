<?php
declare(strict_types=1);
/**
 * Out of Stock Handling Diagnostic
 *
 * Philosophy: Proper availability markup and UX
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Out_Of_Stock_Handling extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-out-of-stock-handling',
            'title' => 'Out-of-Stock Product Handling',
            'description' => 'Ensure out-of-stock products use proper availability markup and consider 410 or noindex for permanently discontinued items.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/out-of-stock-seo/',
            'training_link' => 'https://wpshadow.com/training/ecommerce-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }

}