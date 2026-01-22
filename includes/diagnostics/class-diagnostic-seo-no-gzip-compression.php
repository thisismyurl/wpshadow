<?php declare(strict_types=1);
/**
 * No GZIP Compression Diagnostic
 *
 * Philosophy: SEO performance - GZIP reduces bandwidth by 70%
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if GZIP compression is enabled.
 */
class Diagnostic_SEO_No_GZIP_Compression {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url, array( 
			'timeout' => 5,
			'headers' => array( 'Accept-Encoding' => 'gzip' )
		) );
		
		if ( ! is_wp_error( $response ) ) {
			$headers = wp_remote_retrieve_headers( $response );
			$encoding = $headers['content-encoding'] ?? '';
			
			if ( strpos( $encoding, 'gzip' ) === false ) {
				return array(
					'id'          => 'seo-no-gzip-compression',
					'title'       => 'GZIP Compression Not Enabled',
					'description' => 'GZIP compression not detected. GZIP reduces file sizes by 50-70%, improving page speed. Enable via .htaccess or hosting control panel.',
					'severity'    => 'high',
					'category'    => 'seo',
					'kb_link'     => 'https://wpshadow.com/kb/enable-gzip-compression/',
					'training_link' => 'https://wpshadow.com/training/compression/',
					'auto_fixable' => false,
					'threat_level' => 65,
				);
			}
		}
		
		return null;
	}
}
