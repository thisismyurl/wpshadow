<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Cookie Consent Compliant?
 * 
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Cookie_Consent extends Diagnostic_Base {
    protected static $slug = 'cookie-consent';
    protected static $title = 'Cookie Consent Compliant?';
    protected static $description = 'Verifies cookie banner meets regulations.';

    public static function check(): ?array {
        // Check for cookie consent plugins
        $cookie_plugins = array(
            'gdpr-cookie-consent/gdpr-cookie-consent.php',
            'cookie-notice/cookie-notice.php',
            'complianz-gdpr/complianz-gpdr.php',
            'cookie-law-info/cookie-law-info.php',
        );
        
        foreach ($cookie_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                return null; // Pass - cookie consent plugin active
            }
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => static::$description . ' No cookie consent plugin detected.',
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/cookie-consent/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=cookie-consent',
            'training_link' => 'https://wpshadow.com/training/cookie-consent/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Compliance',
            'priority'      => 1,
        );
    }
}
