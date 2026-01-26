<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scan Check 1181 Diagnostic
 *
 * Checks for scan check 1181.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */
class Diagnostic_ScanCheck1181 extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'scan-check-1181';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Scan Check 1181';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Scan Check 1181';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'rest_api';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.2601.2148
	 * @return array|null
	 */
	public static function check() {
		// TODO: Implement detection logic for scan-check-1181
		return null;
	}
}
