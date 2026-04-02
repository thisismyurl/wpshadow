<?php
/**
 * Trash Auto Empty Configured Diagnostic (Stub)
 *
 * TODO stub mapped to the settings gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Trash_Auto_Empty_Configured Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Trash_Auto_Empty_Configured extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'trash-auto-empty-configured';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Trash Auto Empty Configured';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether WordPress is set to automatically empty the trash. When EMPTY_TRASH_DAYS is 0, deleted posts and attachments accumulate in the database indefinitely, bloating the wp_posts table.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Severity of the finding.
	 *
	 * @var string
	 */
	protected static $severity = 'low';

	/**
	 * Estimated minutes to resolve.
	 *
	 * @var int
	 */
	protected static $time_to_fix_minutes = 10;

	/**
	 * Business impact statement.
	 *
	 * @var string
	 */
	protected static $impact = 'A trash that is never emptied silently grows the database, slowing queries and inflating backup sizes without any benefit to the site owner.';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Read the EMPTY_TRASH_DAYS constant (default 30 in WordPress core).
	 * - Flag if EMPTY_TRASH_DAYS === 0 (auto-empty disabled).
	 * - Flag if EMPTY_TRASH_DAYS > 90 (unusually long retention that allows
	 *   significant accumulation).
	 * - Return null (healthy) when value is between 1 and 90.
	 *
	 * TODO Fix Plan:
	 * - Guide the user to add define('EMPTY_TRASH_DAYS', 30) in wp-config.php.
	 * - Provide wp-config.php write helper via treatment if permissions allow.
	 * - Do not modify WordPress core files.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// TODO: Implement testable logic.
		return null;
	}
}
