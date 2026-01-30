<?php
/**
 * Gitlab Updater Deploy Keys Diagnostic
 *
 * Gitlab Updater Deploy Keys issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1085.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gitlab Updater Deploy Keys Diagnostic Class
 *
 * @since 1.1085.0000
 */
class Diagnostic_GitlabUpdaterDeployKeys extends Diagnostic_Base {

	protected static $slug = 'gitlab-updater-deploy-keys';
	protected static $title = 'Gitlab Updater Deploy Keys';
	protected static $description = 'Gitlab Updater Deploy Keys issue detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'Fragen\\GitHub_Updater\\Base' ) && ! class_exists( 'Git_Updater' ) ) {
			return null;
		}

		$issues = array();

		// Check for GitLab settings
		$gitlab_settings = get_site_option( 'github_updater', array() );
		if ( empty( $gitlab_settings ) ) {
			$gitlab_settings = get_option( 'github_updater', array() );
		}

		// Check for GitLab access tokens stored in database
		global $wpdb;
		$gitlab_tokens = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value != ''",
				'%gitlab_access_token%'
			)
		);

		if ( $gitlab_tokens > 0 && ! defined( 'GITLAB_ACCESS_TOKEN' ) ) {
			$issues[] = 'GitLab access tokens stored in database (use constants)';
		}

		// Check for deploy keys in database
		$deploy_keys = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value != ''",
				'%gitlab_deploy_key%'
			)
		);

		if ( $deploy_keys > 0 ) {
			$issues[] = 'deploy keys stored in database (security risk)';
		}

		// Check for private key permissions
		$key_dir = WP_CONTENT_DIR . '/uploads/gitlab-keys/';
		if ( is_dir( $key_dir ) ) {
			$perms = fileperms( $key_dir );
			if ( ( $perms & 0044 ) > 0 ) {
				$issues[] = 'GitLab key directory has world-readable permissions';
			}
		}

		// Check for hardcoded credentials in wp-config.php
		if ( defined( 'GITLAB_ACCESS_TOKEN' ) ) {
			$token = GITLAB_ACCESS_TOKEN;
			if ( strlen( $token ) < 20 ) {
				$issues[] = 'GitLab access token appears invalid (too short)';
			}
		}

		// Check for webhook secret configuration
		$webhook_secret = get_option( 'github_updater_gitlab_webhook', '' );
		if ( empty( $webhook_secret ) && defined( 'GITLAB_ACCESS_TOKEN' ) ) {
			$issues[] = 'webhook secret not configured for deployment triggers';
		}

		// Check for SSL verification settings
		$ssl_verify = get_option( 'github_updater_gitlab_ssl_verify', '1' );
		if ( '0' === $ssl_verify ) {
			$issues[] = 'SSL verification disabled for GitLab connections';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 90, 65 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'GitLab Updater deploy key security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/gitlab-updater-deploy-keys',
			);
		}

		return null;
	}
}
