<?php
/**
 * Date Format Localization Treatment
 *
 * Issue #4797: Dates Hardcoded in US Format
 * Family: internationalization (Pillar: Culturally Respectful)
 *
 * Checks if dates use WordPress localization instead of hardcoded US format.
 * Users worldwide expect dates in their local format.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6036.1615
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Date_Format_Localization Class
 *
 * Checks for hardcoded date formats.
 *
 * @since 1.6036.1615
 */
class Treatment_Date_Format_Localization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'date-format-localization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Dates Hardcoded in US Format';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if dates use localized formatting instead of hardcoded formats';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6036.1615
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Date_Format_Localization' );
	}
}
