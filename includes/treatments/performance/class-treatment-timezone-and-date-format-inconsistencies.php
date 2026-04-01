<?php
/**
 * Timezone and Date Format Inconsistencies Treatment
 *
 * Tests for timezone and date format consistency.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Timezone and Date Format Inconsistencies Treatment Class
 *
 * Tests for timezone and date format consistency.
 *
 * @since 0.6093.1200
 */
class Treatment_Timezone_And_Date_Format_Inconsistencies extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'timezone-and-date-format-inconsistencies';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Timezone and Date Format Inconsistencies';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for timezone and date format consistency';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Timezone_And_Date_Format_Inconsistencies' );
	}
}
