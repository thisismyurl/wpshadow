<?php
/**
 * NoSQL Injection Not Prevented Treatment
 *
 * Checks NoSQL injection.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_NoSQL_Injection_Not_Prevented Class
 *
 * Performs treatment check for Nosql Injection Not Prevented.
 *
 * @since 1.6033.2033
 */
class Treatment_NoSQL_Injection_Not_Prevented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'nosql-injection-not-prevented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'NoSQL Injection Not Prevented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks NoSQL injection';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_NoSQL_Injection_Not_Prevented' );
	}
}
