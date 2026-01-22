<?php
declare(strict_types=1);
/**
 * Environment File Exposure Diagnostic
 *
 * Philosophy: Credential protection - hide .env files
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if .env files are web-accessible.
 */
class Diagnostic_ENV_File_Exposure extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Test if .env is accessible
		$env_url  = trailingslashit( home_url() ) . '.env';
		$response = wp_remote_get(
			$env_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );

		// If .env is accessible and contains environment variables
		if ( $status_code === 200 &&
			( strpos( $body, 'DB_PASSWORD' ) !== false ||
				strpos( $body, 'API_KEY' ) !== false ||
				strpos( $body, '=' ) !== false ) ) {

			return array(
				'id'            => 'env-file-exposure',
				'title'         => 'Environment File Publicly Accessible',
				'description'   => 'Your .env file is accessible via web browser, exposing database credentials, API keys, and other secrets. Block access to .env files immediately via .htaccess or server configuration.',
				'severity'      => 'critical',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/protect-env-files/',
				'training_link' => 'https://wpshadow.com/training/environment-security/',
				'auto_fixable'  => true,
				'threat_level'  => 80,
			);
		}

		return null;
	}
}
