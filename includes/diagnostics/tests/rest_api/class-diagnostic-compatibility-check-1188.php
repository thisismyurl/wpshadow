<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compatibility Check 1188 Diagnostic
 *
 * Checks for compatibility check 1188.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */
class Diagnostic_CompatibilityCheck1188 extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'compatibility-check-1188';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Compatibility Check 1188';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Compatibility Check 1188';

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
		// TODO: Implement detection logic for compatibility-check-1188
		return null;
	}
}
