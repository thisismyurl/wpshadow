<?php
/**
 * Bitbucket Updater Authentication Diagnostic
 *
 * Bitbucket Updater Authentication issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1081.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bitbucket Updater Authentication Diagnostic Class
 *
 * @since 1.1081.0000
 */
class Diagnostic_BitbucketUpdaterAuthentication extends Diagnostic_Base {

	protected static $slug = 'bitbucket-updater-authentication';
	protected static $title = 'Bitbucket Updater Authentication';
	protected static $description = 'Bitbucket Updater Authentication issue detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Bitbucket_Updater' ) && ! defined( 'BITBUCKET_UPDATER_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Bitbucket credentials stored securely
		$bb_username = get_option( 'bitbucket_username', '' );
		$bb_password = get_option( 'bitbucket_password', '' );
		if ( ! empty( $bb_password ) ) {
			$issues[] = 'Bitbucket credentials stored in database (use constants)';
		}

		// Check 2: App password vs account password
		if ( ! empty( $bb_password ) && strlen( $bb_password ) < 20 ) {
			$issues[] = 'using account password instead of app password (security risk)';
		}

		// Check 3: Authentication method
		$auth_method = get_option( 'bitbucket_auth_method', 'password' );
		if ( 'password' === $auth_method && ! defined( 'BITBUCKET_APP_PASSWORD' ) ) {
			$issues[] = 'basic authentication detected (use OAuth or app passwords)';
		}

		// Check 4: SSL verification enabled
		$verify_ssl = get_option( 'bitbucket_verify_ssl', '1' );
		if ( '0' === $verify_ssl ) {
			$issues[] = 'SSL verification disabled (man-in-the-middle risk)';
		}

		// Check 5: Repository access permissions
		global $wpdb;
		$repo_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE 'bitbucket_repo_%'"
		);
		if ( $repo_count > 10 ) {
			$issues[] = "many repositories configured ({$repo_count} repos, review permissions)";
		}

		// Check 6: Last authentication check
		$last_check = get_option( 'bitbucket_last_auth_check', 0 );
		if ( ! empty( $last_check ) ) {
			$days_old = round( ( time() - $last_check ) / DAY_IN_SECONDS );
			if ( $days_old > 30 ) {
				$issues[] = "authentication not verified in {$days_old} days";
			}
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 70 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Bitbucket updater security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/bitbucket-updater-authentication',
			);
		}

		return null;
	}
}
