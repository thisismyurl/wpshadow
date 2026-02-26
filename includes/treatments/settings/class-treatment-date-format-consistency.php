<?php
/**
 * Date Format Consistency
 *
 * Checks if site date format is consistent and optimal.
 *
 * @package    WPShadow
 * @subpackage Treatments\Configuration
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Date_Format_Consistency Class
 *
 * Validates date format consistency across site.
 *
 * @since 1.6030.2148
 */
class Treatment_Date_Format_Consistency extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'date-format-consistency';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Date Format Consistency';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates date format consistency and readability';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'configuration';

	/**
	 * Run the treatment check.
	 *
	 * Tests date format configuration.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Date_Format_Consistency' );
	}
}
