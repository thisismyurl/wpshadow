<?php declare(strict_types=1);
/**
 * Open Basedir Restriction Diagnostic
 *
 * Philosophy: Shared hosting security - isolate users
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if open_basedir is configured.
 */
class Diagnostic_Open_Basedir {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$open_basedir = ini_get( 'open_basedir' );
		
		// Check if on shared hosting (heuristic)
		$is_shared = false;
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : '';
		
		if ( strpos( $server_software, 'cPanel' ) !== false || 
		     strpos( ABSPATH, '/home/' ) === 0 ) {
			$is_shared = true;
		}
		
		if ( $is_shared && empty( $open_basedir ) ) {
			return array(
				'id'          => 'open-basedir',
				'title'       => 'open_basedir Not Configured on Shared Hosting',
				'description' => 'You appear to be on shared hosting without open_basedir restriction. This allows your PHP scripts to read other users\' files on the same server. Contact your host to enable open_basedir or move to isolated hosting.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/configure-open-basedir/',
				'training_link' => 'https://wpshadow.com/training/hosting-security/',
				'auto_fixable' => false,
				'threat_level' => 80,
			);
		}
		
		return null;
	}
}
