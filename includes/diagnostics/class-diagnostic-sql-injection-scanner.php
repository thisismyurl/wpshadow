<?php declare(strict_types=1);
/**
 * SQL Injection Scanner Diagnostic
 *
 * Philosophy: Vulnerability detection - test for SQL injection
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Test common SQL injection vectors.
 */
class Diagnostic_SQL_Injection_Scanner {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		// Test search form with SQL injection payload
		$test_payload = "' OR '1'='1";
		$search_url = add_query_arg( 's', urlencode( $test_payload ), home_url() );
		
		$response = wp_remote_get( $search_url, array( 'timeout' => 10, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) ) {
			return null;
		}
		
		$body = wp_remote_retrieve_body( $response );
		
		// Check for SQL error messages in response
		$sql_errors = array(
			'mysql_fetch',
			'SQL syntax',
			'mysql_num_rows',
			'mysqli_',
			'ORA-',
			'PostgreSQL',
		);
		
		foreach ( $sql_errors as $error ) {
			if ( stripos( $body, $error ) !== false ) {
				return array(
					'id'          => 'sql-injection-scanner',
					'title'       => 'Potential SQL Injection Vulnerability',
					'description' => 'Search form or URL parameters may be vulnerable to SQL injection attacks. SQL error messages are being displayed. Sanitize all user inputs and use prepared statements.',
					'severity'    => 'critical',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/prevent-sql-injection/',
					'training_link' => 'https://wpshadow.com/training/sql-injection-prevention/',
					'auto_fixable' => false,
					'threat_level' => 90,
				);
			}
		}
		
		return null;
	}
}
