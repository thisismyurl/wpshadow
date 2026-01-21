<?php
/**
 * SSL Enforcement Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

/**
 * Treatment for enabling HTTPS URLs and forcing admin SSL.
 */
class Treatment_SSL extends Treatment_Base {
	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @return string
	 */
	public static function get_finding_id() {
		return 'ssl-missing';
	}
	
	/**
	 * Check if this treatment can be applied.
	 *
	 * @return bool True if treatment can run.
	 */
	public static function can_apply() {
		$home    = get_option( 'home' );
		$https_home = preg_replace( '#^http://#i', 'https://', $home );
		
		if ( empty( $https_home ) || stripos( $home, 'http://' ) !== 0 ) {
			return false; // Already https or invalid base.
		}
		
		return self::is_https_reachable( $https_home );
	}
	
	/**
	 * Apply the treatment/fix.
	 *
	 * @return array Result with 'success' bool and 'message' string.
	 */
	public static function apply() {
		$home       = get_option( 'home' );
		$siteurl    = get_option( 'siteurl' );
		$https_home = preg_replace( '#^http://#i', 'https://', $home );
		$https_site = preg_replace( '#^http://#i', 'https://', $siteurl );
		
		if ( empty( $https_home ) || empty( $https_site ) ) {
			return array(
				'success' => false,
				'message' => 'Could not determine site URLs to update.',
			);
		}
		
		if ( ! self::is_https_reachable( $https_home ) ) {
			return array(
				'success' => false,
				'message' => 'HTTPS is not reachable for this site. Configure SSL first, then retry.',
			);
		}
		
		update_option( 'wpshadow_prev_home', $home );
		update_option( 'wpshadow_prev_siteurl', $siteurl );
		
		update_option( 'home', $https_home );
		update_option( 'siteurl', $https_site );
		update_option( 'force_ssl_admin', 1 );
		
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		
		return array(
			'success' => true,
			'message' => 'Site URLs switched to HTTPS and admin SSL enforced.',
		);
	}
	
	/**
	 * Undo the treatment (best effort by restoring previous URLs).
	 *
	 * @return array Result with 'success' bool and 'message' string.
	 */
	public static function undo() {
		$prev_home    = get_option( 'wpshadow_prev_home' );
		$prev_siteurl = get_option( 'wpshadow_prev_siteurl' );
		
		if ( empty( $prev_home ) || empty( $prev_siteurl ) ) {
			return array(
				'success' => false,
				'message' => 'No previous URLs stored to restore.',
			);
		}
		
		update_option( 'home', $prev_home );
		update_option( 'siteurl', $prev_siteurl );
		delete_option( 'wpshadow_prev_home' );
		delete_option( 'wpshadow_prev_siteurl' );
		
		return array(
			'success' => true,
			'message' => 'HTTPS changes reverted to previous URLs.',
		);
	}
	
	/**
	 * Check whether HTTPS is reachable for the site.
	 *
	 * @param string $url HTTPS URL to test.
	 * @return bool
	 */
	private static function is_https_reachable( $url ) {
		$response = wp_remote_head( $url, array( 'timeout' => 5, 'sslverify' => false ) );
		if ( is_wp_error( $response ) ) {
			return false;
		}
		
		$code = wp_remote_retrieve_response_code( $response );
		return ( $code >= 200 && $code < 400 );
	}
}
