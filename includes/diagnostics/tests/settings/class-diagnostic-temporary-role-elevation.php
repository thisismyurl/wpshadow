<?php
/**
 * Temporary Role Elevation Diagnostic
 *
 * Checks for users with elevated privileges that should be temporary,
 * such as admin accounts created for support or development purposes.
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
 * Temporary Role Elevation Diagnostic Class
 *
 * Identifies potentially temporary elevated accounts.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Temporary_Role_Elevation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'temporary-role-elevation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Temporary Role Elevation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for elevated accounts that may be temporary';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$suspicious_accounts = array();

		// Keywords that suggest temporary accounts.
		$temp_keywords = array( 'temp', 'test', 'demo', 'support', 'dev', 'staging', 'trial' );

		// Get all administrator and editor accounts.
		$elevated_users = get_users(
			array(
				'role__in' => array( 'administrator', 'editor' ),
				'fields'   => array( 'ID', 'user_login', 'user_email', 'user_registered' ),
			)
		);

		foreach ( $elevated_users as $user ) {
			$flags = array();

			// Check username for temp keywords.
			foreach ( $temp_keywords as $keyword ) {
				if ( false !== stripos( $user->user_login, $keyword ) ) {
					$flags[] = sprintf(
						/* translators: %s: keyword found in username */
						__( 'Username contains "%s"', 'wpshadow' ),
						$keyword
					);
				}
			}

			// Check email for temp keywords.
			foreach ( $temp_keywords as $keyword ) {
				if ( false !== stripos( $user->user_email, $keyword ) ) {
					$flags[] = sprintf(
						/* translators: %s: keyword found in email */
						__( 'Email contains "%s"', 'wpshadow' ),
						$keyword
					);
				}
			}

			// Check for recent creation (within last 30 days).
			$registered_timestamp = strtotime( $user->user_registered );
			$days_since_creation  = ( time() - $registered_timestamp ) / DAY_IN_SECONDS;

			if ( $days_since_creation < 30 ) {
				$flags[] = sprintf(
					/* translators: %d: number of days since creation */
					__( 'Created %d days ago', 'wpshadow' ),
					absint( $days_since_creation )
				);
			}

			// Check for expiration date in user meta.
			$expiration_date = get_user_meta( $user->ID, 'account_expiration', true );
			if ( ! empty( $expiration_date ) ) {
				$flags[] = sprintf(
					/* translators: %s: expiration date */
					__( 'Has expiration date: %s', 'wpshadow' ),
					$expiration_date
				);
			}

			// Check for temporary access meta.
			$temp_access = get_user_meta( $user->ID, 'temporary_access', true );
			if ( ! empty( $temp_access ) ) {
				$flags[] = __( 'Marked as temporary access', 'wpshadow' );
			}

			if ( ! empty( $flags ) ) {
				$suspicious_accounts[] = array(
					'user_id'        => $user->ID,
					'user_login'     => $user->user_login,
					'user_email'     => $user->user_email,
					'user_registered' => $user->user_registered,
					'flags'          => $flags,
				);
			}
		}

		if ( ! empty( $suspicious_accounts ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of accounts with elevation concerns */
					__( 'Found %d elevated accounts that may be temporary or should be reviewed.', 'wpshadow' ),
					count( $suspicious_accounts )
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'details'      => array(
					'accounts'       => $suspicious_accounts,
					'recommendation' => __( 'Review these accounts and downgrade or remove them if they are no longer needed.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
