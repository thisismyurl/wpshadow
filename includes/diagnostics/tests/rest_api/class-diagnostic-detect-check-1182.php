<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detect Check 1182 Diagnostic
 *
 * Checks for detect check 1182.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */
class Diagnostic_DetectCheck1182 extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'detect-check-1182';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Detect Check 1182';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detect Check 1182';

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
		// TODO: Implement detection logic for detect-check-1182
		return null;
	}
}
