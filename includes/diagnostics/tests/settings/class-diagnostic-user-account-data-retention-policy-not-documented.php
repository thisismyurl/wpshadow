<?php
/**
 * User Account Data Retention Policy Not Documented Diagnostic
 *
 * Checks if data retention policy is documented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Account Data Retention Policy Not Documented Diagnostic Class
 *
 * Detects missing data retention policy.
 *
 * @since 0.6093.1200
 */
class Diagnostic_User_Account_Data_Retention_Policy_Not_Documented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-account-data-retention-policy-not-documented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Account Data Retention Policy Not Documented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if data retention policy is documented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if privacy policy mentions data retention
		$privacy_page = get_option( 'wp_page_for_privacy_policy' );

		if ( empty( $privacy_page ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'User data retention policy is not documented. Create a clear data retention policy explaining how long user data is kept.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/user-account-data-retention-policy-not-documented?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
