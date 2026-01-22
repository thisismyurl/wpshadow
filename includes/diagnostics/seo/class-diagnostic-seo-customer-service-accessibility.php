<?php
declare(strict_types=1);
/**
 * Customer Service Accessibility Diagnostic
 *
 * Philosophy: Accessible support improves conversion
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Customer_Service_Accessibility extends Diagnostic_Base {
    public static function check(): ?array {
        if (class_exists('WooCommerce')) {
            return [
                'id' => 'seo-customer-service-accessibility',
                'title' => 'Customer Service Contact Options',
                'description' => 'Provide multiple contact options: phone, email, chat. Display prominently.',
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/customer-service/',
                'training_link' => 'https://wpshadow.com/training/support-channels/',
                'auto_fixable' => false,
                'threat_level' => 30,
            ];
        }
        return null;
    }
}
