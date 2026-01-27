<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Verify Check 1180 Diagnostic
 *
 * Checks for verify check 1180.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */
class Diagnostic_VerifyCheck1180 extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'verify-check-1180';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Verify Check 1180';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verify Check 1180';

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
		// TODO: Implement detection logic for verify-check-1180
		return null;
	}
}
