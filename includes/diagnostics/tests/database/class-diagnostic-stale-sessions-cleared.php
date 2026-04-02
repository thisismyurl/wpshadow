<?php
/**
 * Stale Sessions Diagnostic
 *
 * Checks that expired WordPress user sessions are not accumulating in
 * wp_usermeta. Each logged-in user stores a serialised session token in
 * wp_usermeta; WordPress should be expiring these automatically, but on busy
 * sites they can pile up and slow user-related queries.
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
 * Diagnostic_Stale_Sessions_Cleared Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Stale_Sessions_Cleared extends Diagnostic_Base {

    /** @var string */
    protected static $slug = 'stale-sessions-cleared';

    /** @var string */
    protected static $title = 'Stale User Sessions Not Accumulating';

    /** @var string */
    protected static $description = 'Checks that the number of user session token rows in wp_usermeta is not disproportionate to the active user count, which would indicate expired sessions are not being cleared.';

    /** @var string */
    protected static $family = 'database';

    /**
     * Run the diagnostic check.
     *
     * @since  0.6093.1200
     * @return array|null Finding array when stale sessions are accumulating, null when healthy.
     */
    public static function check() {
        // TODO: implement.
        return null;
    }
}
