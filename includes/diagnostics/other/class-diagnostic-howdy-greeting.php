<?php
declare(strict_types=1);
/**
 * Diagnostic: Howdy Greeting Detection
 *
 * Detects if the "Howdy" admin greeting is displayed in the top menu.
 * Provides an option to remove it for a cleaner admin interface.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Howdy_Greeting
 *
 * Detects the "Howdy" greeting in WordPress admin top menu.
 */
class Diagnostic_Howdy_Greeting extends Diagnostic_Base {

	protected static $slug        = 'howdy-greeting-visible';
	protected static $title       = 'Admin Greeting Visible';
	protected static $description = 'Detects if the "Howdy" greeting is displayed in the admin top menu.';

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding array or null if no issues detected.
	 */
	public static function check(): ?array {
		// Check if "Howdy" greeting is enabled (by default it is)
		$howdy_hidden = get_option( 'wpshadow_hide_howdy_greeting', false );

		if ( $howdy_hidden ) {
			// Already configured to hide, no finding
			return null;
		}

		// "Howdy" is displayed by default - this is just informational
		$finding = array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => self::build_description(),
			'category'     => 'admin-ux',
			'severity'     => 'info',
			'threat_level' => 1,
			'auto_fixable' => true,
			'timestamp'    => current_time( 'mysql' ),
		);

		return $finding;
	}

	/**
	 * Build the finding description with recommendations.
	 *
	 * @return string HTML description.
	 */
	private static function build_description(): string {
		$description  = __( 'The "Howdy" admin greeting is currently displayed in the top menu bar.', 'wpshadow' );
		$description .= '<br><br><strong>' . __( 'Options:', 'wpshadow' ) . '</strong><ul>';
		$description .= '<li>' . __( 'Keep it for a friendly admin experience', 'wpshadow' ) . '</li>';
		$description .= '<li>' . __( 'Remove it for a cleaner, more professional admin interface', 'wpshadow' ) . '</li>';
		$description .= '<li>' . __( 'This is automatically hidden when comments are disabled site-wide', 'wpshadow' ) . '</li>';
		$description .= '</ul>';

		return $description;
	}
}
