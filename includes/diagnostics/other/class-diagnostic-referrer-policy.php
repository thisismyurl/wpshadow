<?php
declare(strict_types=1);
/**
 * Referrer Policy Diagnostic
 *
 * Philosophy: Privacy protection - control referrer information
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if Referrer Policy is configured.
 */
class Diagnostic_Referrer_Policy extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$response = wp_remote_head(
			home_url(),
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$headers = wp_remote_retrieve_headers( $response );

		if ( empty( $headers['referrer-policy'] ) ) {
			return array(
				'id'            => 'referrer-policy',
				'title'         => 'Referrer Policy Not Set',
				'description'   => 'Your site lacks a Referrer-Policy header, which may leak sensitive URL parameters to third-party sites. Set a restrictive referrer policy like "no-referrer" or "same-origin".',
				'severity'      => 'low',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/set-referrer-policy/',
				'training_link' => 'https://wpshadow.com/training/referrer-policy/',
				'auto_fixable'  => true,
				'threat_level'  => 40,
			);
		}

		return null;
	}
}
