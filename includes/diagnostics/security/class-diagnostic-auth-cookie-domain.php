<?php
declare(strict_types=1);
/**
 * Auth Cookie Domain Scope Diagnostic
 *
 * Philosophy: Cookie security - minimize cookie scope
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check authentication cookie domain configuration.
 */
class Diagnostic_Auth_Cookie_Domain extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( ! defined( 'COOKIE_DOMAIN' ) ) {
			return null; // Using default (exact domain)
		}
		
		$cookie_domain = COOKIE_DOMAIN;
		
		// Check if wildcard subdomain is used
		if ( strpos( $cookie_domain, '.' ) === 0 ) {
			// Wildcard like .example.com
			return array(
				'id'          => 'auth-cookie-domain',
				'title'       => 'Overly Broad Cookie Domain',
				'description' => sprintf(
					'COOKIE_DOMAIN is set to "%s" (wildcard subdomain). Authentication cookies will be sent to ALL subdomains, potentially exposing sessions to compromised subdomains. Use exact domain instead.',
					$cookie_domain
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/restrict-cookie-domain/',
				'training_link' => 'https://wpshadow.com/training/cookie-security/',
				'auto_fixable' => false,
				'threat_level' => 70,
			);
		}
		
		return null;
	}
}
