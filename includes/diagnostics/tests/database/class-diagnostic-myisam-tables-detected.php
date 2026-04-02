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
     * @since  0.6093.1200
     * @return array|null Finding array when MyISAM tables are found, null when all tables use InnoDB.
     */
    public static function check() {
        // TODO: implement.
        return null;
    }
}
