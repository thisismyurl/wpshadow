<?php declare(strict_types=1);
/**
 * Offer Richness Schema Diagnostic
 *
 * Philosophy: Rich offer data improves shopping
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Offer_Richness_Schema {
    public static function check() {
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
