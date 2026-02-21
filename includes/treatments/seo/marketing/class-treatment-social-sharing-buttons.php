<?php
/**
 * Social Sharing Buttons Treatment
 *
 * Detects when social sharing buttons are missing or non-functional.
 *
 * @package    WPShadow
 * @subpackage Treatments\Marketing
 * @since      1.6035.2307
 */

declare(strict_types=1);

namespace WPShadow\Treatments\Marketing;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social Sharing Buttons Treatment Class
 *
 * Checks if social sharing functionality is available and properly configured.
 *
 * @since 1.6035.2307
 */
class Treatment_Social_Sharing_Buttons extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-sharing-buttons';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Social Sharing Buttons Missing or Non-Functional';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when social sharing buttons are absent or improperly configured';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the treatment check
	 *
	 * @since  1.6035.2307
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Marketing\Diagnostic_Social_Sharing_Buttons' );
	}
}
