<?php
/**
 * Google Cloud Sql Proxy Diagnostic
 *
 * Google Cloud Sql Proxy needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1014.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Cloud Sql Proxy Diagnostic Class
 *
 * @since 1.1014.0000
 */
class Diagnostic_GoogleCloudSqlProxy extends Diagnostic_Base {

	protected static $slug = 'google-cloud-sql-proxy';
	protected static $title = 'Google Cloud Sql Proxy';
	protected static $description = 'Google Cloud Sql Proxy needs attention';
	protected static $family = 'functionality';

	public static function check() {
		// Check if using Cloud SQL Proxy
		$db_host = defined( 'DB_HOST' ) ? DB_HOST : '';
		$using_proxy = strpos( $db_host, '127.0.0.1' ) !== false ||
		               strpos( $db_host, 'cloudsql' ) !== false ||
		               get_option( 'gcp_cloud_sql_proxy', false );
		
		if ( ! $using_proxy ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Proxy socket location
		if ( strpos( $db_host, '/cloudsql/' ) === false && strpos( $db_host, '127.0.0.1' ) !== false ) {
			$issues[] = __( 'Using TCP instead of Unix socket (performance loss)', 'wpshadow' );
		}
		
		// Check 2: Connection pooling
		$persistent_conn = defined( 'DB_PERSISTENT' ) && DB_PERSISTENT;
		if ( ! $persistent_conn ) {
			$issues[] = __( 'Persistent connections disabled (connection overhead)', 'wpshadow' );
		}
		
		// Check 3: Proxy credentials in code
		$creds_in_config = defined( 'GOOGLE_APPLICATION_CREDENTIALS' );
		if ( $creds_in_config ) {
			$issues[] = __( 'Service account credentials in wp-config.php (security risk)', 'wpshadow' );
		}
		
		// Check 4: SSL enforcement
		$require_ssl = get_option( 'gcp_require_ssl', false );
		if ( ! $require_ssl ) {
			$issues[] = __( 'SSL not enforced for database connections', 'wpshadow' );
		}
		
		// Check 5: Connection timeout
		$timeout = get_option( 'gcp_connection_timeout', 30 );
		if ( $timeout > 10 ) {
			$issues[] = sprintf( __( 'Connection timeout: %ds (slow failure detection)', 'wpshadow' ), $timeout );
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
				/* translators: %s: list of Cloud SQL Proxy issues */
				__( 'Google Cloud SQL Proxy has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/google-cloud-sql-proxy',
		);
	}
}
