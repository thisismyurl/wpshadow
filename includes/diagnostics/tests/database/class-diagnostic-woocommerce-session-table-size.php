<?php
/**
 * WooCommerce Session Table Size Diagnostic
 *
 * Checks whether the wp_woocommerce_sessions table exists and, if so, whether
 * its row count has grown to an unhealthy size. A large sessions table
 * indicates that WooCommerce session cleanup is not running correctly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Diagnostic_Woocommerce_Session_Table_Size Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Woocommerce_Session_Table_Size extends Diagnostic_Base {

    /** @var string */
    protected static $slug = 'woocommerce-session-table-size';

    /** @var string */
    protected static $title = 'WooCommerce Session Table Not Bloated';

    /** @var string */
    protected static $description = 'Checks that the wp_woocommerce_sessions table (if present) contains fewer than 10,000 rows. A larger table indicates WooCommerce scheduled cleanup is not running correctly.';

    /** @var string */
    protected static $family = 'database';

    /**
     * Run the diagnostic check.
     *
     * Skips gracefully when WooCommerce is not active or the sessions table does
     * not exist. Counts rows in wp_woocommerce_sessions and returns null when
     * the count is under 10,000. Returns a medium or high severity finding based
     * on how far the count exceeds the threshold.
     *
     * Skips gracefully if WooCommerce is not active or the table does not exist.
     *
     * @since  0.6093.1200
     * @return array|null Finding array when session table is bloated, null when healthy or not applicable.
     */
    public static function check() {
        global $wpdb;

        // Only relevant when WooCommerce is active.
        if ( ! class_exists( 'WooCommerce' ) ) {
            return null;
        }

        $table = $wpdb->prefix . 'woocommerce_sessions';

        // phpcs:disable WordPress.DB.DirectDatabaseQuery
        $exists = (int) $wpdb->get_var( $wpdb->prepare(
            'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s',
            DB_NAME,
            $table
        ) );

        if ( ! $exists ) {
            return null;
        }

        $row_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        // phpcs:enable WordPress.DB.DirectDatabaseQuery

        if ( $row_count < 10000 ) {
            return null;
        }

        $severity     = $row_count >= 50000 ? 'high' : 'medium';
        $threat_level = $row_count >= 50000 ? 60 : 40;

        return array(
            'id'           => self::$slug,
            'title'        => self::$title,
            'description'  => sprintf(
                /* translators: %s: formatted row count */
                __( 'The wp_woocommerce_sessions table contains %s rows. WooCommerce should automatically purge expired session rows via its scheduled cleanup task. This large session table suggests the cleanup cron is not running correctly, causing database bloat that adds latency to every page request.', 'wpshadow' ),
                number_format_i18n( $row_count )
            ),
            'severity'     => $severity,
            'threat_level' => $threat_level,
            'auto_fixable' => false,
            'kb_link'      => 'https://wpshadow.com/kb/woocommerce-session-table-size?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
            'details'      => array(
                'session_row_count' => $row_count,
                'table_name'        => $table,
            ),
        );
    }
}
