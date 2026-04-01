<?php
/**
 * Security Headers Treatment
 *
 * Analyzes security headers configuration and best practices.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Headers Treatment
 *
 * Evaluates HTTP security headers implementation.
 *
 * @since 0.6093.1200
 */
class Treatment_Security_Headers extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'security-headers';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Security Headers';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes security headers configuration and best practices';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Security_Headers' );
	}
}
