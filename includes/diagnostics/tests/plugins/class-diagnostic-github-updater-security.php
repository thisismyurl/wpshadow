<?php
/**
 * Github Updater Security Diagnostic
 *
 * Github Updater Security issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1077.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Github Updater Security Diagnostic Class
 *
 * @since 1.1077.0000
 */
class Diagnostic_GithubUpdaterSecurity extends Diagnostic_Base {

	protected static $slug = 'github-updater-security';
	protected static $title = 'Github Updater Security';
	protected static $description = 'Github Updater Security issue detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Fragen\\GitHub_Updater\\Base' ) && ! class_exists( 'GitHub_Updater' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify GitHub access tokens are not stored in plain text
		$github_tokens = get_site_option( 'github_updater', array() );
		if ( ! empty( $github_tokens ) && is_array( $github_tokens ) ) {
			foreach ( $github_tokens as $key => $value ) {
				if ( strpos( $key, 'token' ) !== false && ! empty( $value ) ) {
					$issues[] = 'GitHub tokens stored in options (should use authentication)';
					break;
				}
			}
		}

		// Check 2: Verify SSL verification is enabled
		$ssl_verify = get_site_option( 'github_updater_ssl_verify', true );
		if ( ! $ssl_verify ) {
			$issues[] = 'SSL verification disabled for GitHub Updater';
		}

		// Check 3: Check for rate limiting configuration
		$rate_limit = get_site_option( 'github_updater_rate_limit', false );
		if ( ! $rate_limit ) {
			$issues[] = 'GitHub API rate limiting not configured';
		}

		// Check 4: Verify update checks are authenticated
		$use_auth = get_site_option( 'github_updater_use_auth', false );
		if ( ! $use_auth ) {
			$issues[] = 'Unauthenticated API requests may hit rate limits';
		}

		// Check 5: Check for webhook security tokens
		$webhook_secret = get_site_option( 'github_updater_webhook_secret', '' );
		if ( empty( $webhook_secret ) ) {
			$issues[] = 'Webhook secret not configured';
		}

		// Check 6: Verify private repository access is properly secured
		$private_repos = get_site_option( 'github_updater_private_repos', array() );
		if ( ! empty( $private_repos ) && empty( $use_auth ) ) {
			$issues[] = 'Private repositories configured without authentication';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 70;
			$threat_multiplier = 5;
			$max_threat = 95;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d GitHub Updater security issue(s): %s',
					$issue_count,
					impode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/github-updater-security',
			);
		}

		return null;
	}
}
