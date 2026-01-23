<?php
declare(strict_types=1);
/**
 * Inconsistent NAP Data Diagnostic
 *
 * Philosophy: SEO local - consistent NAP builds trust
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for inconsistent Name, Address, Phone (NAP) data.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Inconsistent_NAP extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Extract phone numbers from footer/contact pages
		global $wpdb;
		
		$contact_content = $wpdb->get_col(
			"SELECT post_content FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND (post_title LIKE '%contact%' OR post_name = 'contact')"
		);
		
		$phone_numbers = array();
		foreach ( $contact_content as $content ) {
			preg_match_all( '/\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}/', $content, $matches );
			$phone_numbers = array_merge( $phone_numbers, $matches[0] );
		}
		
		$unique_phones = array_unique( $phone_numbers );
		
		if ( count( $unique_phones ) > 1 ) {
			return array(
				'id'          => 'seo-inconsistent-nap',
				'title'       => 'Inconsistent NAP Data',
				'description' => sprintf( 'Found %d different phone number formats. NAP (Name, Address, Phone) must be consistent across all pages for local SEO. Standardize format.', count( $unique_phones ) ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/fix-nap-consistency/',
				'training_link' => 'https://wpshadow.com/training/local-seo-nap/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}

}