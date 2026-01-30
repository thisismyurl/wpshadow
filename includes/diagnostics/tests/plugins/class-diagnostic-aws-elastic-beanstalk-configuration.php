<?php
/**
 * Aws Elastic Beanstalk Configuration Diagnostic
 *
 * Aws Elastic Beanstalk Configuration needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1009.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Aws Elastic Beanstalk Configuration Diagnostic Class
 *
 * @since 1.1009.0000
 */
class Diagnostic_AwsElasticBeanstalkConfiguration extends Diagnostic_Base {

	protected static $slug = 'aws-elastic-beanstalk-configuration';
	protected static $title = 'Aws Elastic Beanstalk Configuration';
	protected static $description = 'Aws Elastic Beanstalk Configuration needs attention';
	protected static $family = 'functionality';

	public static function check() {
		// Check for AWS Elastic Beanstalk environment
		$is_eb = isset( $_SERVER['RDS_HOSTNAME'] ) ||
		         defined( 'AWS_EB_ENV' ) ||
		         file_exists( '/var/app/current/.ebextensions' );

		if ( ! $is_eb ) {
			return null;
		}

		$issues = array();

		// Check 1: Environment variables
		if ( ! defined( 'DB_HOST' ) || strpos( DB_HOST, 'rds.amazonaws.com' ) === false ) {
			$issues[] = __( 'Not using RDS (single point of failure)', 'wpshadow' );
		}

		// Check 2: Session handler
		$session_handler = ini_get( 'session.save_handler' );
		if ( 'files' === $session_handler ) {
			$issues[] = __( 'File-based sessions (not scalable)', 'wpshadow' );
		}

		// Check 3: Object cache
		if ( ! defined( 'WP_CACHE' ) || ! WP_CACHE ) {
			$issues[] = __( 'No object cache (poor multi-instance performance)', 'wpshadow' );
		}

		// Check 4: Upload directory
		$upload_dir = wp_upload_dir();
		if ( strpos( $upload_dir['basedir'], '/var/app/current' ) !== false ) {
			$issues[] = __( 'Uploads in app directory (lost on deployment)', 'wpshadow' );
		}

		// Check 5: Debug mode
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$issues[] = __( 'Debug mode enabled (exposes errors)', 'wpshadow' );
		}

		// Check 6: HTTPS enforcement
		if ( ! defined( 'FORCE_SSL_ADMIN' ) || ! FORCE_SSL_ADMIN ) {
			$issues[] = __( 'HTTPS not enforced (security risk)', 'wpshadow' );
		}

		// Check 7: Health check endpoint
		$health_check = get_option( 'eb_health_check_path', '' );
		if ( empty( $health_check ) ) {
			$issues[] = __( 'No health check endpoint (poor monitoring)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 50;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 64;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 57;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'AWS Elastic Beanstalk has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/aws-elastic-beanstalk-configuration',
		);
	}
}
