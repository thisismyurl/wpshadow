<?php
declare(strict_types=1);
/**
 * Return Policy Clarity Diagnostic
 *
 * Philosophy: Clear returns reduce anxiety
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Return_Policy_Clarity extends Diagnostic_Base {
    public static function check(): ?array {
        if (class_exists('WooCommerce')) {
            return [
                'id' => 'seo-return-policy-clarity',
                'title' => 'Return Policy Visibility',
                'description' => 'Display clear return policy prominently. Link from product pages and footer.',
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/return-policy/',
                'training_link' => 'https://wpshadow.com/training/trust-building/',
                'auto_fixable' => false,
                'threat_level' => 35,
            ];
        }
        return null;
    }
}
