<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Is Your Site Actually Loading?
 *
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Site_Actually_Loading extends Diagnostic_Base {
	protected static $slug        = 'site-actually-loading';
	protected static $title       = 'Is Your Site Actually Loading?';
	protected static $description = 'Checks if your homepage loads successfully for visitors.';

	public static function check(): ?array {
		$home_url = home_url();
		$response = wp_remote_get( $home_url, array( 'timeout' => 10 ) );

		if ( is_wp_error( $response ) ) {
			return array(
				'id'            => static::$slug,
				'title'         => __( 'Site is not loading', 'wpshadow' ),
				'description'   => sprintf(
					__( 'Your homepage (%1$s) failed to load: %2$s', 'wpshadow' ),
					$home_url,
					$response->get_error_message()
				),
				'severity'      => 'critical',
				'category'      => 'monitoring',
				'kb_link'       => 'https://wpshadow.com/kb/site-actually-loading/',
				'training_link' => 'https://wpshadow.com/training/site-actually-loading/',
				'auto_fixable'  => false,
				'threat_level'  => 100,
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 400 ) {
			return array(
				'id'            => static::$slug,
				'title'         => __( 'Site returns error status', 'wpshadow' ),
				'description'   => sprintf(
					__( 'Your homepage returns HTTP %d instead of 200 OK.', 'wpshadow' ),
					$code
				),
				'severity'      => 'high',
				'category'      => 'monitoring',
				'kb_link'       => 'https://wpshadow.com/kb/site-actually-loading/',
				'training_link' => 'https://wpshadow.com/training/site-actually-loading/',
				'auto_fixable'  => false,
				'threat_level'  => 85,
			);
		}

		return null;
	}

}
