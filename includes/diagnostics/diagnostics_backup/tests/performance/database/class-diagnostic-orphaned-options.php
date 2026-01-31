<?php
/**
 * Diagnostic: Orphaned Database Options
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
 * Diagnostic_OrphanedOptions Class
 */
class Diagnostic_OrphanedOptions extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'orphaned-options';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Orphaned Database Options';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Detect leftover options from deleted plugins that bloat the database';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'database';

    /**
     * Run the diagnostic check
     *
     * @since 1.2601.2148
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        global $wpdb;
        
        // Common plugin prefixes that leave options behind
        $known_prefixes = array(
            'jetpack_',
            'wp_smush_',
            'woocommerce_',
            'yoast_',
            'wordfence_',
            'akismet_',
            'elementor_',
            'wp_mail_smtp_',
            'wp_super_cache_',
            'w3tc_',
            'rank_math_',
            'updraft_',
        );
        
        $orphaned_options = array();
        
        foreach ( $known_prefixes as $prefix ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $count = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
                    $wpdb->esc_like( $prefix ) . '%'
                )
            );
            
            if ( $count > 0 ) {
                // Extract plugin name from prefix
                $plugin_name = rtrim( $prefix, '_' );
                
                // Check if plugin is active (simple heuristic)
                $plugin_slug = str_replace( '_', '-', $plugin_name );
                
                // Get all active plugins
                $active_plugins = get_option( 'active_plugins', array() );
                $plugin_active = false;
                
                foreach ( $active_plugins as $plugin ) {
                    if ( false !== strpos( $plugin, $plugin_slug ) ) {
                        $plugin_active = true;
                        break;
                    }
                }
                
                if ( ! $plugin_active ) {
                    $orphaned_options[] = array(
                        'prefix' => $prefix,
                        'plugin' => $plugin_name,
                        'count'  => (int) $count,
                    );
                }
            }
        }
        
        // Also check for transients older than 30 days
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $expired_transients = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->options}
                WHERE option_name LIKE %s
                AND option_value < %d",
                '_transient_timeout_%',
                time() - ( 30 * DAY_IN_SECONDS )
            )
        );
        
        if ( $expired_transients > 0 ) {
            $orphaned_options[] = array(
                'prefix' => '_transient_',
                'plugin' => 'Expired Transients',
                'count'  => (int) $expired_transients,
            );
        }
        
        if ( empty( $orphaned_options ) ) {
            return null; // Database is clean
        }
        
        $total_count = array_sum( wp_list_pluck( $orphaned_options, 'count' ) );
        
        return array(
            'id'            => self::$slug,
            'title'         => self::$title,
            'description'   => sprintf(
                /* translators: %d: number of orphaned options */
                __( 'Found %d orphaned database options from deleted or inactive plugins. These waste space and slow down option queries.', 'wpshadow' ),
                $total_count
            ),
            'severity'      => 'low',
            'threat_level'  => 20,
            'auto_fixable'  => true,
            'kb_link'       => 'https://wpshadow.com/kb/database-orphaned-options',
            'manual_steps'  => array(
                __( 'Back up database before deleting options', 'wpshadow' ),
                __( 'Use a plugin like WP-Optimize to clean options', 'wpshadow' ),
                __( 'Manually delete options with known prefixes', 'wpshadow' ),
            ),
            'impact'        => array(
                'performance' => __( 'Slightly slower option queries', 'wpshadow' ),
                'storage'     => __( 'Wasted database space', 'wpshadow' ),
            ),
            'evidence'      => array(
                'orphaned_options' => $orphaned_options,
                'total_count'      => $total_count,
            ),
        );
    }
}
