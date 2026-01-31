<?php
/**
 * User Registration Data Collection Not Transparent Diagnostic
 *
 * Checks if user data collection is transparent.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Registration Data Collection Not Transparent Diagnostic Class
 *
 * Detects missing user data collection transparency.
 *
 * @since 1.2601.2310
 */
class Diagnostic_User_Registration_Data_Collection_Not_Transparent extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-registration-data-collection-not-transparent';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Registration Data Collection Not Transparent';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if registration data collection is transparent';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if user registration is enabled
		if ( ! get_option( 'users_can_register' ) ) {
			return null; // No registration, no issue
		}

		// Check for registration form customization with privacy notices
		$privacy_policy_page = get_option( 'wp_page_for_privacy_policy', 0 );

		if ( ! $privacy_policy_page ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'User registration is enabled but privacy policy not set. Users must be informed about data collection during registration.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/user-registration-data-collection-not-transparent',
			);
		}

		// Check if registration checkbox exists for consent
		if ( ! has_filter( 'register_form', 'privacy_policy_checkbox' ) && ! has_filter( 'woocommerce_register_form', 'privacy_policy_checkbox' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'User registration does not include privacy consent checkbox. Users must explicitly consent to data collection and privacy policy.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/user-registration-data-collection-not-transparent',
			);
		}

		return null;
	}
}
