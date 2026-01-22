<?php declare(strict_types=1);
/**
 * Missing Privacy Policy Link Diagnostic
 *
 * Philosophy: SEO trust - privacy policy is trust signal
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for privacy policy page and link.
 */
class Diagnostic_SEO_Missing_Privacy_Policy {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$privacy_page = get_option( 'wp_page_for_privacy_policy' );
		
		if ( ! $privacy_page ) {
			return array(
				'id'          => 'seo-missing-privacy-policy',
				'title'       => 'No Privacy Policy Page',
				'description' => 'No privacy policy page set. Privacy policy is trust signal and legal requirement. Create privacy policy page and link from footer.',
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/create-privacy-policy/',
				'training_link' => 'https://wpshadow.com/training/trust-signals/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
