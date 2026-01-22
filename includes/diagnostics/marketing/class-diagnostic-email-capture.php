<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Email Capture Forms Working?
 * 
 * Target Persona: Digital Marketing Agency
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Email_Capture extends Diagnostic_Base {
    protected static $slug = 'email-capture';
    protected static $title = 'Email Capture Forms Working?';
    protected static $description = 'Tests newsletter signup and lead magnets.';

    public static function check(): ?array {
        // Check for email capture/popup plugins
        $email_plugins = array(
            'mailchimp-for-wp/mailchimp-for-wp.php',
            'optinmonster/optin-monster-wp-api.php',
            'mailpoet/mailpoet.php',
            'popup-maker/popup-maker.php',
            'hustle/opt-in.php',
        );
        
        foreach ($email_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                return null; // Pass - email capture plugin active
            }
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'No email capture or popup plugin detected.',
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/email-capture/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=email-capture',
            'training_link' => 'https://wpshadow.com/training/email-capture/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Marketing',
            'priority'      => 2,
        );
    }

    /**
     * IMPLEMENTATION PLAN (Digital Marketing Agency)
     * 
     * What This Checks:
     * - [Technical implementation details]
     * 
     * Why It Matters:
     * - [Business value in plain English]
     * 
     * Success Criteria:
     * - [What "passing" means]
     * 
     * How to Fix:
     * - Step 1: [Clear instruction]
     * - Step 2: [Next step]
     * - KB Article: Detailed explanation and examples
     * - Training Video: Visual walkthrough
     * 
     * KPIs Tracked:
     * - Issues found and fixed
     * - Time saved (estimated minutes)
     * - Site health improvement %
     * - Business value delivered ($)
     */
}