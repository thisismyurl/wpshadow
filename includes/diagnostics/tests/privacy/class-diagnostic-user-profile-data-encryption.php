<?php
/**
 * User Profile Data Encryption Diagnostic
 *
 * Checks for sensitive user profile data stored without encryption.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Profile Data Encryption Diagnostic
 *
 * Detects sensitive user meta stored in plain text.
 *
 * @since 1.2601.2240
 */
class Diagnostic_User_Profile_Data_Encryption extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-profile-data-encryption';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Profile Data Encryption';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for sensitive user profile data stored without encryption';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$sensitive_keys = array(
			'passport',
			'ssn',
			'social_security',
			'dob',
			'date_of_birth',
			'credit_card',
			'bank',
			'driver_license',
		);

		$issues = array();
		$findings = array();

		foreach ( $sensitive_keys as $key ) {
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT meta_key, meta_value FROM {$wpdb->usermeta} WHERE meta_key LIKE %s LIMIT 5",
					'%' . $wpdb->esc_like( $key ) . '%'
				)
			);

			foreach ( $rows as $row ) {
				$value = (string) $row->meta_value;
				if ( strlen( $value ) > 0 && strlen( $value ) < 40 && false === strpos( $value, '$' ) ) {
					$findings[] = $row->meta_key;
				}
			}
		}

		if ( ! empty( $findings ) ) {
			$issues[] = __( 'Sensitive user profile fields may be stored in plain text', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Sensitive user data should be encrypted at rest', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-profile-data-encryption',
				'details'      => array(
					'issues'         => $issues,
					'sensitive_keys' => array_values( array_unique( $findings ) ),
				),
			);
		}

		return null;
	}
}
