<?php
/**
 * Buttons Links Not Distinguishable Treatment
 *
 * Checks if buttons and links are semantically and visually distinct.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Buttons Links Treatment Class
 *
 * Validates that buttons and links use correct semantics and are visually distinct.
 *
 * @since 0.6093.1200
 */
class Treatment_Buttons_Links_Not_Distinguishable extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'buttons-links-not-distinguishable';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Buttons and Links Not Distinguishable';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if buttons and links use correct semantics';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Buttons_Links_Not_Distinguishable' );
	}
}
