<?php declare(strict_types=1);
/**
 * Exposed .git Directory Diagnostic
 *
 * Philosophy: Source control security - protect repository data
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if .git directory is web-accessible.
 */
class Diagnostic_Git_Directory_Exposed {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		// Test if .git/config is accessible
		$git_config_url = trailingslashit( home_url() ) . '.git/config';
		$response = wp_remote_get( $git_config_url, array( 'timeout' => 5, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) ) {
			return null;
		}
		
		$status = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		
		// Check if .git directory is accessible
		if ( $status === 200 && 
		     ( strpos( $body, '[core]' ) !== false || 
		       strpos( $body, '[remote' ) !== false ) ) {
			
			return array(
				'id'          => 'git-directory-exposed',
				'title'       => '.git Directory Publicly Accessible',
				'description' => 'Your .git directory is accessible via web browser, exposing complete source code history including deleted files, credentials in old commits, and development secrets. Block .git access via .htaccess immediately.',
				'severity'    => 'critical',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/protect-git-directory/',
				'training_link' => 'https://wpshadow.com/training/source-control-security/',
				'auto_fixable' => true,
				'threat_level' => 90,
			);
		}
		
		return null;
	}
}
