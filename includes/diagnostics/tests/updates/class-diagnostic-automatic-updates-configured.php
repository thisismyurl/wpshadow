<?php
/**
 * Diagnostic: Automatic Updates Configured
 *
 * @since 1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Diagnostic_AutomaticUpdatesConfigured Class
 */
class Diagnostic_AutomaticUpdatesConfigured extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'automatic-updates-configured';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Automatic Updates Configured';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Detect if WordPress automatic updates are enabled for core, plugins, and themes';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'updates';

    /**
     * Run the diagnostic check
     *
     * @since 1.2601.2148
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        $issues = array();
        
        // Check core updates
        $core_updates_disabled = defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED;
        $core_updates_minor_only = defined( 'WP_AUTO_UPDATE_CORE' ) && 'minor' === WP_AUTO_UPDATE_CORE;
        $core_updates_disabled_completely = defined( 'WP_AUTO_UPDATE_CORE' ) && false === WP_AUTO_UPDATE_CORE;
        
        if ( $core_updates_disabled || $core_updates_disabled_completely ) {
            $issues[] = __( 'WordPress core automatic updates are completely disabled', 'wpshadow' );
        } elseif ( $core_updates_minor_only ) {
            $issues[] = __( 'Only minor core updates are automatic (major updates require manual intervention)', 'wpshadow' );
        }
        
        // Check plugin updates
        $plugin_updates_enabled = apply_filters( 'auto_update_plugin', false, (object) array() );
        
        if ( ! $plugin_updates_enabled ) {
            $issues[] = __( 'Plugin automatic updates are disabled', 'wpshadow' );
        }
        
        // Check theme updates
        $theme_updates_enabled = apply_filters( 'auto_update_theme', false, (object) array() );
        
        if ( ! $theme_updates_enabled ) {
            $issues[] = __( 'Theme automatic updates are disabled', 'wpshadow' );
        }
        
        // If everything is enabled, no issue
        if ( empty( $issues ) ) {
            return null;
        }
        
        $threat_level = 50; // Default medium
        
        // Higher threat if core updates are completely disabled
        if ( $core_updates_disabled || $core_updates_disabled_completely ) {
            $threat_level = 60;
        }
        
        return array(
            'id'            => self::$slug,
            'title'         => self::$title,
            'description'   => sprintf(
                /* translators: %s: list of disabled updates */
                __( 'Automatic updates configuration issues: %s. Keeping WordPress updated is critical for security.', 'wpshadow' ),
                implode( '; ', $issues )
            ),
            'severity'      => $threat_level >= 60 ? 'high' : 'medium',
            'threat_level'  => $threat_level,
            'auto_fixable'  => true,
            'kb_link'       => 'https://wpshadow.com/kb/updates-automatic-updates-configured',
            'manual_steps'  => array(
                __( 'Enable automatic updates in Dashboard > Updates', 'wpshadow' ),
                __( 'Or add to wp-config.php: define(\'WP_AUTO_UPDATE_CORE\', true);', 'wpshadow' ),
                __( 'Consider using a backup solution before enabling', 'wpshadow' ),
                __( 'Test updates on staging site first if possible', 'wpshadow' ),
            ),
            'impact'        => array(
                'security'    => __( 'Security vulnerabilities will not be patched automatically', 'wpshadow' ),
                'maintenance' => __( 'Manual intervention required for all updates', 'wpshadow' ),
                'risk'        => __( 'Site remains vulnerable until updates are manually applied', 'wpshadow' ),
            ),
            'evidence'      => array(
                'core_updates_disabled'             => $core_updates_disabled,
                'core_updates_disabled_completely'  => $core_updates_disabled_completely,
                'core_updates_minor_only'           => $core_updates_minor_only,
                'plugin_updates_enabled'            => $plugin_updates_enabled,
                'theme_updates_enabled'             => $theme_updates_enabled,
                'issues'                            => $issues,
            ),
        );
    }
}
