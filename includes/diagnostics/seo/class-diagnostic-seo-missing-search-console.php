<?php
declare(strict_types=1);
/**
 * Missing Search Console Integration Diagnostic
 *
 * Philosophy: SEO monitoring - GSC shows how Google sees your site
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for Google Search Console verification.
 */
class Diagnostic_SEO_Missing_Search_Console extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check for GSC verification meta tag
		$verification = get_option( 'google-site-verification' );
		
		if ( ! $verification ) {
			return array(
				'id'          => 'seo-missing-search-console',
				'title'       => 'Google Search Console Not Verified',
				'description' => 'Site not verified with Google Search Console. GSC shows search performance, indexing issues, mobile usability. Verify site ownership.',
				'severity'    => 'high',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/verify-google-search-console/',
				'training_link' => 'https://wpshadow.com/training/search-console/',
				'auto_fixable' => false,
				'threat_level' => 65,
			);
		}
		
		return null;
	}
}
