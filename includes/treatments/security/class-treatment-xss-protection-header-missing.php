<?php
/**
 * XSS Protection Header Missing Treatment
 *
 * Checks XSS protection.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_XSS_Protection_Header_Missing Class
 *
 * Performs treatment check for Xss Protection Header Missing.
 *
 * @since 0.6093.1200
 */
class Treatment_XSS_Protection_Header_Missing extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'xss-protection-header-missing';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'XSS Protection Header Missing';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks XSS protection';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_XSS_Protection_Header_Missing' );
	}
}
