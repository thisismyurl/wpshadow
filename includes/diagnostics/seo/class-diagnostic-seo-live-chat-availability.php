<?php
declare(strict_types=1);
/**
 * Live Chat Availability Diagnostic
 *
 * Philosophy: Live chat improves conversion rates
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Live_Chat_Availability extends Diagnostic_Base {
    public static function check(): ?array {
        if (class_exists('WooCommerce')) {
            return [
                'id' => 'seo-live-chat-availability',
                'title' => 'Live Chat Implementation',
                'description' => 'Add live chat for instant support. Improves conversion and customer satisfaction.',
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/live-chat/',
                'training_link' => 'https://wpshadow.com/training/chat-support/',
                'auto_fixable' => false,
                'threat_level' => 25,
            ];
        }
        return null;
    }

}