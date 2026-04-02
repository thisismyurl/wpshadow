<?php
/**
 * Media Year Month Folders Enabled Diagnostic (Stub)
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
 * Diagnostic_Media_Year_Month_Folders_Enabled Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Media_Year_Month_Folders_Enabled extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'media-year-month-folders-enabled';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Media Year Month Folders Enabled';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether WordPress is organising media uploads into year/month subdirectories. When this is disabled, every uploaded file lands in a single flat uploads/ folder, which creates filesystem performance issues and makes manual file management impractical as the library grows.';

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
	protected static $time_to_fix_minutes = 5;

	/**
	 * Business impact statement.
	 *
	 * @var string
	 */
	protected static $impact = 'A flat uploads directory slows filesystem operations and makes it nearly impossible for the site owner to manage, audit, or clean up media files manually.';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Read get_option('uploads_use_yearmonth_folders').
	 * - Flag if the value is falsy (0, '', false).
	 * - Return null (healthy) when the value is truthy (1, '1', true).
	 *
	 * TODO Fix Plan:
	 * - Guide the user to Settings > Media > Organise my uploads into
	 *   month- and year-based folders.
	 * - Use update_option('uploads_use_yearmonth_folders', 1) via treatment.
	 * - Existing files in the flat folder are not moved — note this in the
	 *   remediation guidance.
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
