<?php
/**
 * Number Format Localization Treatment
 *
 * Issue #4800: Numbers Hardcoded Without Localization
 * Family: internationalization (Pillar: Culturally Respectful)
 *
 * Checks if numbers use localized formatting.
 * Different cultures use different thousand and decimal separators.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6036.1625
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Number_Format_Localization Class
 *
 * Checks for hardcoded number formats.
 *
 * @since 1.6036.1625
 */
class Treatment_Number_Format_Localization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'number-format-localization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Numbers Hardcoded Without Localization';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if numbers use localized formatting for separators and decimals';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6036.1625
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Number_Format_Localization' );
	}
}
