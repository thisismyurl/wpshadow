<?php
/**
 * Diagnostic: WordPress Cron Status
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
 * Diagnostic_WordpressCronEnabled Class
 */
class Diagnostic_WordpressCronEnabled extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'wordpress-cron-enabled';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'WordPress Cron Status';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Detect if WordPress cron is enabled and functioning properly';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'cron';

    /**
     * Run the diagnostic check
     *
     * @since 1.2601.2148
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Check if WP Cron is disabled
        $cron_disabled = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;

        if ( ! $cron_disabled ) {
            // Cron is enabled, check if it's running
            $last_cron = get_option( 'wpshadow_last_cron_check', 0 );
            $current_time = time();
            
            // Check if cron has run recently (within last hour)
            if ( $last_cron > 0 && ( $current_time - $last_cron ) < 3600 ) {
                return null; // Cron is enabled and running
            }
            
            // Set a marker to check on next run
            update_option( 'wpshadow_last_cron_check', $current_time, false );
        }

        if ( $cron_disabled ) {
            // Check if system cron is likely configured
            $cron_events = _get_cron_array();
            $has_events = ! empty( $cron_events );

            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'WordPress Cron is disabled (DISABLE_WP_CRON = true). Scheduled tasks like post publishing, plugin updates, and backups will not run unless you have configured a system cron job.', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 45,
                'auto_fixable'  => true,
                'kb_link'       => 'https://wpshadow.com/kb/cron-wordpress-cron-enabled',
                'manual_steps'  => array(
                    __( 'If you want to use WordPress cron: Remove DISABLE_WP_CRON from wp-config.php', 'wpshadow' ),
                    __( 'If you want to use system cron: Set up a cron job to call wp-cron.php every 5-15 minutes', 'wpshadow' ),
                    __( 'Example cron command: */15 * * * * wget -q -O - ' . site_url( '/wp-cron.php?doing_wp_cron' ), 'wpshadow' ),
                ),
                'impact'        => array(
                    'functionality' => __( 'Scheduled posts will not publish automatically', 'wpshadow' ),
                    'maintenance'   => __( 'Automatic updates and backups will not run', 'wpshadow' ),
                    'performance'   => __( 'Plugin scheduled tasks will not execute', 'wpshadow' ),
                ),
                'evidence'      => array(
                    'cron_disabled'   => $cron_disabled,
                    'has_cron_events' => $has_events,
                    'cron_count'      => $has_events ? count( $cron_events ) : 0,
                ),
            );
        }

        return null;
    }
}
