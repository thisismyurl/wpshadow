<?php
/**
 * Orphaned User Meta Diagnostic
 *
 * Checks for rows in wp_usermeta whose user_id does not correspond to any
 * existing user in wp_users. Orphaned user meta accumulates when user accounts
 * are deleted without proper cleanup hooks running.
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
 * Diagnostic_Orphaned_User_Meta Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Orphaned_User_Meta extends Diagnostic_Base {

    /** @var string */
    protected static $slug = 'orphaned-user-meta';

    /** @var string */
    protected static $title = 'No Orphaned User Meta';

    /** @var string */
    protected static $description = 'Checks that every row in wp_usermeta has a corresponding user in wp_users. Orphaned rows are created when users are deleted without cleanup hooks and waste database space.';

    /** @var string */
    protected static $family = 'database';

    /**
     * Run the diagnostic check.
     *
     * Runs a LEFT JOIN query to count wp_usermeta rows whose user_id has no
     * corresponding wp_users record. Returns null when the count is zero.
     * Returns a low or medium severity finding based on the orphaned row count.
     *
     * @since  0.6093.1200
     * @return array|null Finding array when orphaned user meta rows are found, null when healthy.
     */
    public static function check() {
        global $wpdb;

        // phpcs:disable WordPress.DB.DirectDatabaseQuery
        $orphaned_count = (int) $wpdb->get_var(
            "SELECT COUNT(*)
             FROM {$wpdb->usermeta} um
             LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID
             WHERE u.ID IS NULL"
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery

        if ( $orphaned_count === 0 ) {
            return null;
        }

        $severity     = $orphaned_count >= 500 ? 'medium' : 'low';
        $threat_level = $orphaned_count >= 500 ? 30 : 10;

        return array(
            'id'           => self::$slug,
            'title'        => self::$title,
            'description'  => sprintf(
                /* translators: %d: number of orphaned rows */
                _n(
                    '%d orphaned row was found in wp_usermeta — it has no corresponding user in wp_users. These rows accumulate when user accounts are deleted without proper cleanup hooks and waste database space.',
                    '%d orphaned rows were found in wp_usermeta — they have no corresponding users in wp_users. These rows accumulate when user accounts are deleted without proper cleanup hooks and waste database space.',
                    $orphaned_count,
                    'wpshadow'
                ),
                $orphaned_count
            ),
            'severity'     => $severity,
            'threat_level' => $threat_level,
            'kb_link'      => 'https://wpshadow.com/kb/orphaned-user-meta?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
            'details'      => array(
                'orphaned_rows' => $orphaned_count,
            ),
        );
    }
}
