<?php
/**
 * Gitlab Updater Ci Cd Diagnostic
 *
 * Gitlab Updater Ci Cd issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1084.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gitlab Updater Ci Cd Diagnostic Class
 *
 * @since 1.1084.0000
 */
class Diagnostic_GitlabUpdaterCiCd extends Diagnostic_Base {

	protected static $slug = 'gitlab-updater-ci-cd';
	protected static $title = 'Gitlab Updater Ci Cd';
	protected static $description = 'Gitlab Updater Ci Cd issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// Check for GitLab Updater plugin
		$has_gitlab = class_exists( 'Fragen\\GitHub_Updater\\Init' ) ||
		              function_exists( 'github_updater_init' ) ||
		              get_option( 'github_updater', '' ) !== '';
		
		if ( ! $has_gitlab ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: GitLab token security
		$token = get_option( 'github_updater_gitlab_access_token', '' );
		if ( ! empty( $token ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$issues[] = __( 'GitLab token with debug mode (exposure risk)', 'wpshadow' );
		}
		
		// Check 2: Automatic updates
		$auto_update = get_option( 'github_updater_auto_update', 'no' );
		if ( 'yes' === $auto_update ) {
			$issues[] = __( 'Automatic updates enabled (breaking change risk)', 'wpshadow' );
		}
		
		// Check 3: Branch tracking
		$branch = get_option( 'github_updater_branch', 'master' );
		if ( 'master' === $branch || 'main' === $branch ) {
			$issues[] = __( 'Tracking production branch (stability risk)', 'wpshadow' );
		}
		
		// Check 4: CI/CD webhook
		$webhook = get_option( 'github_updater_webhook', '' );
		if ( empty( $webhook ) ) {
			$issues[] = __( 'No webhook configured (delayed updates)', 'wpshadow' );
		}
		
		// Check 5: SSL verification
		$ssl_verify = get_option( 'github_updater_ssl_verify', 'yes' );
		if ( 'no' === $ssl_verify ) {
			$issues[] = __( 'SSL verification disabled (MITM risk)', 'wpshadow' );
		}
		
		// Check 6: Update frequency
		$check_frequency = get_option( 'github_updater_check_frequency', 12 );
		if ( $check_frequency < 6 ) {
			$issues[] = sprintf( __( 'Checking every %d hours (API rate limits)', 'wpshadow' ), $check_frequency );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'GitLab Updater has %d CI/CD issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/gitlab-updater-ci-cd',
		);
	}
}
