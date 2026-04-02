<?php
/**
 * Tables Without Primary Key Diagnostic
 *
 * Checks that every table in the WordPress database has a primary key defined.
 * Tables without primary keys cause performance problems in MySQL/MariaDB
 * replication and can slow certain query patterns significantly.
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
 * Diagnostic_Tables_Without_Primary_Key Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Tables_Without_Primary_Key extends Diagnostic_Base {

    /** @var string */
    protected static $slug = 'tables-without-primary-key';

    /** @var string */
    protected static $title = 'All Tables Have a Primary Key';

    /** @var string */
    protected static $description = 'Checks that every WordPress database table has a primary key. Tables missing a primary key degrade replication performance and can slow certain queries.';

    /** @var string */
    protected static $family = 'database';

    /**
     * Run the diagnostic check.
     *
     * @since  0.6093.1200
     * @return array|null Finding array when tables without a primary key are found, null when healthy.
     */
    public static function check() {
        // TODO: implement.
        return null;
    }
}
