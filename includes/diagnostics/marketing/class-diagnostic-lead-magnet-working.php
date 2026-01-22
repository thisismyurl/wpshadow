<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Lead Magnet Delivery Working?
 * 
 * Target Persona: Digital Marketing Agency
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Lead_Magnet_Working extends Diagnostic_Base {
    protected static $slug = 'lead-magnet-working';
    protected static $title = 'Lead Magnet Delivery Working?';
    protected static $description = 'Tests automated content delivery.';

    public static function check(): ?array {
        // Check for lead magnet/content upgrade plugins
        $leadmagnet_plugins = array(
            'thirstyaffiliates/thirstyaffiliates.php',
            'download-monitor/download-monitor.php',
            'easy-digital-downloads/easy-digital-downloads.php',
        );
        
        foreach ($leadmagnet_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                return null; // Pass - lead magnet handling plugin active
            }
        }
        
        // Lead magnets are advanced marketing, only suggest if email capture present
        $email_plugins = array(
            'mailchimp-for-wp/mailchimp-for-wp.php',
            'optinmonster/optin-monster-wp-api.php',
        );
        
        foreach ($email_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                return array(
                    'id'            => static::$slug,
                    'title'         => static::$title,
                    'description'   => 'Email capture active but no lead magnet delivery system detected.',
                    'color'         => '#ff9800',
                    'bg_color'      => '#fff3e0',
                    'kb_link'       => 'https://wpshadow.com/kb/lead-magnet-working/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=lead-magnet-working',
                    'training_link' => 'https://wpshadow.com/training/lead-magnet-working/',
                    'auto_fixable'  => false,
                    'threat_level'  => 60,
                    'module'        => 'Marketing',
                    'priority'      => 2,
                );
            }
        }
        
        return null;
    }
}
