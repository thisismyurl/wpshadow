<?php
declare(strict_types=1);
/**
 * Customer Photo Reviews Diagnostic
 *
 * Philosophy: Customer photos build authenticity
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Customer_Photo_Reviews extends Diagnostic_Base {
    public static function check(): ?array {
        if (class_exists('WooCommerce')) {
            return [
                'id' => 'seo-customer-photo-reviews',
                'title' => 'Customer Photo Review Integration',
                'description' => 'Allow customers to upload photos with reviews. Visual proof increases trust.',
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/photo-reviews/',
                'training_link' => 'https://wpshadow.com/training/visual-social-proof/',
                'auto_fixable' => false,
                'threat_level' => 25,
            ];
        }
        return null;
    }

}