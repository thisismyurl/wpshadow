<?php
/**
 * Database Credentials Security in wp-config.php Diagnostic
 *
 * Validates database credentials use strong security practices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Credentials Security in wp-config.php Class
 *
 * Tests database credentials security.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Database_Credentials_Security_In_Wp_Config_Php extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-credentials-security-in-wp-config-php';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Credentials Security in wp-config.php';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates database credentials use strong security practices';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$db_check = self::check_database_credentials();
		
		if ( $db_check['has_weaknesses'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $db_check['weaknesses'] ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-credentials-security-in-wp-config-php',
				'meta'         => array(
					'db_user'       => $db_check['db_user_redacted'],
					'db_name'       => $db_check['db_name_redacted'],
					'password_safe' => $db_check['password_safe'],
				),
			);
		}

		return null;
	}

	/**
	 * Check database credentials security.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_database_credentials() {
		$check = array(
			'has_weaknesses'     => false,
			'weaknesses'         => array(),
			'db_user_redacted'   => '',
			'db_name_redacted'   => '',
			'password_safe'      => false,
		);

		// Get database credentials (defined in wp-config.php).
		$db_user = defined( 'DB_USER' ) ? DB_USER : '';
		$db_pass = defined( 'DB_PASSWORD' ) ? DB_PASSWORD : '';
		$db_name = defined( 'DB_NAME' ) ? DB_NAME : '';

		// Redact for meta.
		$check['db_user_redacted'] = substr( $db_user, 0, 3 ) . '***';
		$check['db_name_redacted'] = substr( $db_name, 0, 3 ) . '***';

		// Check for root user.
		if ( 'root' === strtolower( $db_user ) ) {
			$check['has_weaknesses'] = true;
			$check['weaknesses'][] = __( 'Database user is "root" (excessive privileges, major security risk)', 'wpshadow' );
		}

		// Check for common default usernames.
		$common_users = array( 'wordpress', 'wp_user', 'admin', 'wpuser', 'wp' );
		
		if ( in_array( strtolower( $db_user ), $common_users, true ) ) {
			$check['has_weaknesses'] = true;
			$check['weaknesses'][] = sprintf(
				/* translators: %s: database username */
				__( 'Database user "%s" is common default (predictable for brute force)', 'wpshadow' ),
				$db_user
			);
		}

		// Check password strength.
		if ( empty( $db_pass ) ) {
			$check['has_weaknesses'] = true;
			$check['weaknesses'][] = __( 'Database password is EMPTY (no authentication)', 'wpshadow' );
		} elseif ( strlen( $db_pass ) < 12 ) {
			$check['has_weaknesses'] = true;
			$check['weaknesses'][] = sprintf(
				/* translators: %d: password length */
				__( 'Database password is weak (%d characters, recommend 16+)', 'wpshadow' ),
				strlen( $db_pass )
			);
		} else {
			$check['password_safe'] = true;
		}

		// Check for common weak passwords.
		$weak_passwords = array( 'password', 'root', '123456', 'admin', 'wordpress' );
		
		if ( in_array( strtolower( $db_pass ), $weak_passwords, true ) ) {
			$check['has_weaknesses'] = true;
			$check['weaknesses'][] = __( 'Database password is common weak password (easily guessed)', 'wpshadow' );
			$check['password_safe'] = false;
		}

		// Check for generic database name.
		$generic_names = array( 'wordpress', 'wp', 'database', 'test' );
		
		if ( in_array( strtolower( $db_name ), $generic_names, true ) ) {
			$check['has_weaknesses'] = true;
			$check['weaknesses'][] = __( 'Database name is generic (predictable for attackers)', 'wpshadow' );
		}

		return $check;
	}
}
