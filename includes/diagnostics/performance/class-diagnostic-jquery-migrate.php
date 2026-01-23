<?php
declare(strict_types=1);
/**
 * jQuery Migrate Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Detect if jQuery Migrate is enqueued on the frontend.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Jquery_Migrate extends Diagnostic_Base {
    /**
     * Run the diagnostic check.
     *
     * @return array|null Finding data or null if no issue.
     */
    public static function check(): ?array {
        if ( is_admin() ) {
            return null;
        }

        $disabled = (bool) get_option( 'wpshadow_disable_jquery_migrate', false );
        if ( $disabled ) {
            return null;
        }

        $present = self::is_jquery_migrate_present();
        if ( ! $present ) {
            return null;
        }

        return array(
            'id'           => 'jquery-migrate-enabled',
            'title'        => 'jQuery Migrate Loaded',
            'description'  => 'Legacy jQuery Migrate is enqueued on the frontend. Disabling it can reduce JS size and parse time. Only keep it if a theme or plugin requires it.',
            'color'        => '#ff9800',
            'bg_color'     => '#fff3e0',
            'kb_link'      => 'https://wpshadow.com/kb/jquery-migrate/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=jquery-migrate',
            'auto_fixable' => true,
            'threat_level' => 25,
        );
    }

    private static function is_jquery_migrate_present() {
        global $wp_scripts;
        if ( ! isset( $wp_scripts ) ) {
            return false;
        }

        // If registered or queued, consider it present.
        $registered = isset( $wp_scripts->registered['jquery-migrate'] );
        $queued     = is_object( $wp_scripts ) && is_array( $wp_scripts->queue ?? null ) && in_array( 'jquery-migrate', $wp_scripts->queue, true );
        return ( $registered || $queued );
    }

}