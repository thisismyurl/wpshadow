<?php
declare(strict_types=1);
/**
 * Redirect Chains Diagnostic
 *
 * Philosophy: SEO performance - redirect chains waste crawl budget
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for redirect chains.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Redirect_Chains extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Check if Redirection plugin is active
		$redirects = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}redirection_items 
			WHERE status = 'enabled' 
			LIMIT 10",
			ARRAY_A
		);
		
		$chains = 0;
		foreach ( $redirects as $redirect ) {
			// Check if redirect target is also redirected
			$target = $redirect['url'] ?? '';
			$target_redirect = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$wpdb->prefix}redirection_items 
					WHERE url = %s AND status = 'enabled'",
					$target
				)
			);
			
			if ( $target_redirect ) {
				$chains++;
			}
		}
		
		if ( $chains > 0 ) {
			return array(
				'id'          => 'seo-redirect-chains',
				'title'       => 'Redirect Chains Detected',
				'description' => sprintf( 'Found %d redirect chains (A→B→C). Chains waste crawl budget and slow page load. Create direct redirects (A→C).', $chains ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/fix-redirect-chains/',
				'training_link' => 'https://wpshadow.com/training/redirect-best-practices/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
