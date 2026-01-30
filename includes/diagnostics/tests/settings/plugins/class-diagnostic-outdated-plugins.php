<?php
/**
 * Diagnostic: Outdated Plugins Detection
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
 * Diagnostic_OutdatedPlugins Class
 */
class Diagnostic_OutdatedPlugins extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'outdated-plugins';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Outdated Plugins Detection';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Detect plugins that haven\'t been updated in 30+ days';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'plugins';

    /**
     * Run the diagnostic check
     *
     * @since 1.2601.2148
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Check for plugin updates
        $update_plugins = get_site_transient( 'update_plugins' );
        
        if ( empty( $update_plugins->response ) ) {
            return null; // All plugins are up to date
        }
        
        $outdated_plugins = array();
        $current_time = time();
        
        foreach ( $update_plugins->response as $plugin_file => $plugin_data ) {
            $plugin_info = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file, false, false );
            
            // Check when plugin was last updated on WordPress.org
            $last_updated = $plugin_data->last_updated ?? '';
            
            if ( ! empty( $last_updated ) ) {
                $updated_timestamp = strtotime( $last_updated );
                $days_old = floor( ( $current_time - $updated_timestamp ) / DAY_IN_SECONDS );
                
                if ( $days_old > 30 ) {
                    $outdated_plugins[] = array(
                        'name'       => $plugin_info['Name'],
                        'version'    => $plugin_info['Version'],
                        'new_version' => $plugin_data->new_version ?? '',
                        'days_old'   => $days_old,
                        'file'       => $plugin_file,
                    );
                }
            }
        }
        
        if ( empty( $outdated_plugins ) ) {
            return null; // No outdated plugins
        }
        
        // Calculate threat level based on age
        $max_days_old = max( wp_list_pluck( $outdated_plugins, 'days_old' ) );
        $threat_level = 45; // Default medium
        
        if ( $max_days_old > 90 ) {
            $threat_level = 60; // High if plugins very outdated
        }
        
        return array(
            'id'            => self::$slug,
            'title'         => self::$title,
            'description'   => sprintf(
                /* translators: %d: number of outdated plugins */
                __( '%d plugins have available updates. Outdated plugins may have security vulnerabilities or compatibility issues.', 'wpshadow' ),
                count( $outdated_plugins )
            ),
            'severity'      => $threat_level >= 60 ? 'high' : 'medium',
            'threat_level'  => $threat_level,
            'auto_fixable'  => true,
            'kb_link'       => 'https://wpshadow.com/kb/plugins-outdated-plugins',
            'manual_steps'  => array(
                __( 'Review available plugin updates in Dashboard > Updates', 'wpshadow' ),
                __( 'Back up site before updating plugins', 'wpshadow' ),
                __( 'Update plugins one at a time if possible', 'wpshadow' ),
                __( 'Test site functionality after updates', 'wpshadow' ),
            ),
            'impact'        => array(
                'security'      => __( 'Outdated plugins may have known vulnerabilities', 'wpshadow' ),
                'compatibility' => __( 'May break with new WordPress versions', 'wpshadow' ),
                'support'       => __( 'Unmaintained plugins can cause issues', 'wpshadow' ),
            ),
            'evidence'      => array(
                'outdated_plugins' => $outdated_plugins,
                'max_days_old'     => $max_days_old,
            ),
        );
    }
}
