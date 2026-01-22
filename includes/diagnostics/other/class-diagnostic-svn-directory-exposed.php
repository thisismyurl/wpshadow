<?php
declare(strict_types=1);
/**
 * Exposed .svn Directory Diagnostic
 *
 * Philosophy: Source control security - protect Subversion data
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if .svn directory is web-accessible.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SVN_Directory_Exposed extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Test if .svn/entries is accessible
		$svn_entries_url = trailingslashit( home_url() ) . '.svn/entries';
		$response        = wp_remote_get(
			$svn_entries_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$status = wp_remote_retrieve_response_code( $response );
		$body   = wp_remote_retrieve_body( $response );

		// Check if .svn directory is accessible
		if ( $status === 200 && ! empty( $body ) ) {
			return array(
				'id'            => 'svn-directory-exposed',
				'title'         => '.svn Directory Publicly Accessible',
				'description'   => 'Your .svn directory is accessible via web browser, exposing Subversion repository data including source code, file history, and potential credentials. Block .svn access via .htaccess immediately.',
				'severity'      => 'critical',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/protect-svn-directory/',
				'training_link' => 'https://wpshadow.com/training/source-control-security/',
				'auto_fixable'  => true,
				'threat_level'  => 85,
			);
		}

		return null;
	}
}
