<?php
/**
 * Flywheel Local Compatibility Diagnostic
 *
 * Flywheel Local Compatibility needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1003.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flywheel Local Compatibility Diagnostic Class
 *
 * @since 1.1003.0000
 */
class Diagnostic_FlywheelLocalCompatibility extends Diagnostic_Base {

	protected static $slug = 'flywheel-local-compatibility';
	protected static $title = 'Flywheel Local Compatibility';
	protected static $description = 'Flywheel Local Compatibility needs attention';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Flywheel Local environment
		$is_local = defined( 'LOCAL_SITE_ID' ) ||
		            isset( $_SERVER['SERVER_SOFTWARE'] ) && strpos( $_SERVER['SERVER_SOFTWARE'], 'Local' ) !== false ||
		            strpos( ABSPATH, 'Local Sites' ) !== false;

		if ( ! $is_local ) {
			return null;
		}

		$issues = array();

		// Check 1: Database prefix
		global $wpdb;
		if ( $wpdb->prefix === 'wp_' ) {
			$issues[] = __( 'Using default wp_ prefix (security risk)', 'wpshadow' );
		}

		// Check 2: Production URLs in DB
		$site_url = get_option( 'siteurl' );
		if ( strpos( $site_url, '.local' ) === false && strpos( $site_url, 'localhost' ) === false ) {
			$issues[] = __( 'Production URL in local DB (migration issues)', 'wpshadow' );
		}

		// Check 3: Debug mode
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			$issues[] = __( 'Debug mode disabled (missed local errors)', 'wpshadow' );
		}

		// Check 4: Local SSL
		if ( is_ssl() && strpos( $site_url, 'https' ) !== false ) {
			$cert_path = $_SERVER['DOCUMENT_ROOT'] . '/../conf/ssl.crt';
			if ( file_exists( $cert_path ) ) {
				$cert_age = time() - filemtime( $cert_path );
				if ( $cert_age > ( 90 * DAY_IN_SECONDS ) ) {
					$issues[] = __( 'SSL certificate expired (browser warnings)', 'wpshadow' );
				}
			}
		}

		// Check 5: Mail catcher
		$smtp_host = ini_get( 'SMTP' );
		if ( empty( $smtp_host ) || $smtp_host === 'localhost' ) {
			$issues[] = __( 'No mail catcher configured (emails lost)', 'wpshadow' );
		}

		// Check 6: Version control
		$has_git = file_exists( ABSPATH . '.git' );
		if ( ! $has_git ) {
			$issues[] = __( 'No Git repository (no version control)', 'wpshadow' );
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
				__( 'Flywheel Local has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/flywheel-local-compatibility',
		);
	}
}
