<?php
declare(strict_types=1);
/**
 * Offer Richness Schema Diagnostic
 *
 * Philosophy: Rich offer data improves shopping
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Offer_Richness_Schema extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-offer-richness-schema',
            'title' => 'Offer Schema Completeness',
            'description' => 'Enhance Offer schema: availability, validThrough, seller, shippingDetails.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/offer-schema/',
            'training_link' => 'https://wpshadow.com/training/product-markup/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }
}
