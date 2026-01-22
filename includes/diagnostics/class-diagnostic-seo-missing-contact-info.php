<?php declare(strict_types=1);
/**
 * Missing Contact Information Diagnostic
 *
 * Philosophy: SEO trust - contact info builds credibility
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for missing contact information.
 */
class Diagnostic_SEO_Missing_Contact_Info {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wpdb;
		
		$contact_page = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND (post_name = 'contact' OR post_title LIKE '%contact%')"
		);
		
		if ( $contact_page === 0 ) {
			return array(
				'id'          => 'seo-missing-contact-info',
				'title'       => 'No Contact Page',
				'description' => 'No contact page found. Contact information is trust signal for users and search engines. Create contact page with email, phone, address.',
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/create-contact-page/',
				'training_link' => 'https://wpshadow.com/training/trust-signals/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
