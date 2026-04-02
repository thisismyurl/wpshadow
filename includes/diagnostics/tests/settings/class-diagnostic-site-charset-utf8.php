<?php
/**
 * Site Charset UTF-8 Diagnostic (Stub)
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
 * Diagnostic_Site_Charset_Utf8 Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Site_Charset_Utf8 extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'site-charset-utf8';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Site Charset UTF-8';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the WordPress blog_charset option is set to UTF-8. Sites migrated from legacy hosting sometimes carry an ISO-8859-1 or other charset that causes character encoding errors in page content, RSS feeds, and REST API responses.';

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
	protected static $severity = 'medium';

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
	protected static $impact = 'A non-UTF-8 charset produces garbled special characters (mojibake) in content, feeds, and API consumers, eroding visitor trust and breaking third-party integrations.';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Read get_option('blog_charset').
	 * - Normalise the value (strtoupper, trim).
	 * - Flag if the value is not 'UTF-8'.
	 * - Return null (healthy) when charset is UTF-8.
	 *
	 * TODO Fix Plan:
	 * - Guide the user to update the charset.
	 * - Use update_option('blog_charset', 'UTF-8') after confirming the
	 *   database collation is also utf8mb4.
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
