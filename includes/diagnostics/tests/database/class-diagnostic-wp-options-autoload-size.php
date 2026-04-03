<?php
/**
 * Autoloaded Options Total Size Diagnostic
 *
 * Checks that the total byte size of autoloaded wp_options rows does not
 * exceed a safe threshold. Excessive autoload data delays every page load
 * because WordPress fetches it all in a single query on boot.
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
 * Diagnostic_Wp_Options_Autoload_Size Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Wp_Options_Autoload_Size extends Diagnostic_Base {

    /** @var string */
    protected static $slug = 'wp-options-autoload-size';

    /** @var string */
    protected static $title = 'Autoloaded Options Total Size Under Limit';

    /** @var string */
    protected static $description = 'Checks that the total byte size of all autoloaded wp_options rows is under 800 KB. Large autoload payloads slow every page load.';

    /** @var string */
    protected static $family = 'database';

/**
 * Confidence level of this diagnostic.
 *
 * @var string
 */
protected static $confidence = 'standard';

    /**
     * Run the diagnostic check.
     *
     * Queries wp_options to sum the byte length of all autoloaded option values.
     * Returns null when the total is under 800 KB. Returns a medium finding for
     * 800 KB–2 MB and a high finding above 2 MB, including the top offenders.
     *
     * @since  0.6093.1200
     * @return array|null Finding array when autoload payload is too large, null when healthy.
     */
    public static function check() {
        global $wpdb;

        // phpcs:disable WordPress.DB.DirectDatabaseQuery
        $total_bytes = (int) $wpdb->get_var(
            "SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload = 'yes'"
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery

        $threshold_warn = 800 * 1024;   // 800 KB.
        $threshold_high = 2 * 1024 * 1024; // 2 MB.

        if ( $total_bytes < $threshold_warn ) {
            return null;
        }

        $severity     = $total_bytes >= $threshold_high ? 'high' : 'medium';
        $threat_level = $total_bytes >= $threshold_high ? 60 : 40;
        $size_label   = round( $total_bytes / 1024, 1 ) . ' KB';

        // phpcs:disable WordPress.DB.DirectDatabaseQuery
        $top_options = $wpdb->get_results(
            "SELECT option_name, LENGTH(option_value) AS bytes
             FROM {$wpdb->options}
             WHERE autoload = 'yes'
             ORDER BY bytes DESC
             LIMIT 10"
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery

        $top_list = array_map( static function ( $row ) {
            return array(
                'option_name' => $row->option_name,
                'bytes'       => (int) $row->bytes,
            );
        }, $top_options ?: array() );

        return array(
            'id'           => self::$slug,
            'title'        => self::$title,
            'description'  => sprintf(
                /* translators: %s: total autoload size with unit */
                __( 'The total size of autoloaded WordPress options is %s. WordPress loads all autoloaded options on every page request in a single query. A large autoload payload increases memory usage and time-to-first-byte on every page view.', 'wpshadow' ),
                $size_label
            ),
            'severity'     => $severity,
            'threat_level' => $threat_level,
            'kb_link'      => 'https://wpshadow.com/kb/wp-options-autoload-size?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
            'details'      => array(
                'total_bytes'   => $total_bytes,
                'total_size_kb' => round( $total_bytes / 1024, 1 ),
                'top_options'   => $top_list,
            ),
        );
    }
}
