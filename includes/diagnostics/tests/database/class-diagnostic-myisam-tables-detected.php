<?php
/**
 * No MyISAM Tables Diagnostic
 *
 * Checks that no WordPress database tables are still using the MyISAM storage
 * engine. MyISAM lacks transactions, foreign-key support, and crash recovery;
 * all tables should use InnoDB.
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
 * Diagnostic_Myisam_Tables_Detected Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Myisam_Tables_Detected extends Diagnostic_Base {

    /** @var string */
    protected static $slug = 'myisam-tables-detected';

    /** @var string */
    protected static $title = 'No MyISAM Tables in Use';

    /** @var string */
    protected static $description = 'Checks that all WordPress database tables use the InnoDB storage engine. MyISAM is outdated, lacks transactions, and does not support crash recovery.';

    /** @var string */
    protected static $family = 'database';

    /**
     * Run the diagnostic check.
     *
     * Queries INFORMATION_SCHEMA to find tables in the current database using
     * the MyISAM storage engine. Returns null when no MyISAM tables exist.
     * Returns a medium-severity finding listing the table names when any are
     * found.
     *
     * @since  0.6093.1200
     * @return array|null Finding array when MyISAM tables are found, null when all tables use InnoDB.
     */
    public static function check() {
        global $wpdb;

        // phpcs:disable WordPress.DB.DirectDatabaseQuery
        $myisam_tables = $wpdb->get_col( $wpdb->prepare(
            "SELECT TABLE_NAME
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = %s
               AND ENGINE = 'MyISAM'
             ORDER BY TABLE_NAME",
            DB_NAME
        ) );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery

        if ( empty( $myisam_tables ) ) {
            return null;
        }

        $count      = count( $myisam_tables );
        $table_list = implode( ', ', $myisam_tables );

        return array(
            'id'           => self::$slug,
            'title'        => self::$title,
            'description'  => sprintf(
                /* translators: 1: number of tables, 2: comma-separated table names */
                _n(
                    '%1$d database table is still using the MyISAM storage engine (%2$s). MyISAM lacks transactions, foreign-key support, and crash recovery. Converting to InnoDB improves reliability and replication performance.',
                    '%1$d database tables are still using the MyISAM storage engine (%2$s). MyISAM lacks transactions, foreign-key support, and crash recovery. Converting to InnoDB improves reliability and replication performance.',
                    $count,
                    'wpshadow'
                ),
                $count,
                $table_list
            ),
            'severity'     => 'medium',
            'threat_level' => 35,
            'auto_fixable' => false,
            'kb_link'      => 'https://wpshadow.com/kb/myisam-tables-detected?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
            'details'      => array(
                'myisam_tables' => $myisam_tables,
                'table_count'   => $count,
            ),
        );
    }
}
