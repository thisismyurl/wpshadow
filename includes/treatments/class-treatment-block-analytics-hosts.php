<?php
/**
 * Treatment: Block Common Analytics Hosts
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Treatment_Block_Analytics_Hosts implements Treatment_Interface {
    /**
     * Finding ID this treatment addresses
     *
     * @return string
     */
    public static function get_finding_id() {
        return 'block_analytics_hosts';
    }

    /**
     * Check capability to apply treatment
     *
     * @return bool
     */
    public static function can_apply() {
        if ( is_multisite() && is_network_admin() ) {
            return current_user_can( 'manage_network_options' );
        }
        return current_user_can( 'manage_options' );
    }

    /**
     * Apply the treatment
     *
     * @return array
     */
    public static function apply() {
        if ( ! self::can_apply() ) {
            return array( 'success' => false, 'message' => __( 'Insufficient permissions to apply treatment.', 'wpshadow' ) );
        }

        $default_hosts = self::get_default_hosts();

        update_option( 'wpshadow_block_analytics_hosts', true );

        $existing = get_option( 'wpshadow_analytics_hosts', array() );
        if ( empty( $existing ) || ! is_array( $existing ) ) {
            update_option( 'wpshadow_analytics_hosts', $default_hosts );
        }

        return array(
            'success' => true,
            'message' => __( 'Blocking common analytics hosts has been enabled.', 'wpshadow' ),
        );
    }

    /**
     * Undo the treatment
     *
     * @return array
     */
    public static function undo() {
        if ( ! self::can_apply() ) {
            return array( 'success' => false, 'message' => __( 'Insufficient permissions to undo treatment.', 'wpshadow' ) );
        }

        delete_option( 'wpshadow_block_analytics_hosts' );
        // Leave host list intact for convenience if re-enabled later.

        return array(
            'success' => true,
            'message' => __( 'Blocking common analytics hosts has been disabled.', 'wpshadow' ),
        );
    }

    /**
     * Default analytics/marketing host list
     *
     * @return array
     */
    private static function get_default_hosts() {
        $hosts = array(
            'www.googletagmanager.com',
            'www.google-analytics.com',
            'www.googleadservices.com',
            'stats.g.doubleclick.net',
            'connect.facebook.net',
            'static.hotjar.com',
            'script.hotjar.com',
            'cdn.segment.com',
            'cdn.fullstory.com',
            'data.fullstory.com',
            'bat.bing.com',
            'js.hs-analytics.net',
            'tag.snapchat.com',
            'analytics.twitter.com',
            'cdn.mxpnl.com',
            'cdn.amplitude.com',
            'api.amplitude.com',
            'cdn.pendo.io',
            'cdn.optimizely.com',
            'plausible.io',
            'cdn.usefathom.com',
            'assets.adobedtm.com',
            'cdn.heapanalytics.com',
        );

        /**
         * Filter default analytics hosts used when enabling the treatment.
         *
         * @param array $hosts Default hostnames.
         */
        return apply_filters( 'wpshadow_default_analytics_hosts', $hosts );
    }
}
