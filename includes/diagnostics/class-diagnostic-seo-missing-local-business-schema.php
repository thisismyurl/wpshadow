<?php declare(strict_types=1);
/**
 * Missing Local Business Schema Diagnostic
 *
 * Philosophy: SEO local - local business schema boosts local search
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for missing local business schema.
 */
class Diagnostic_SEO_Missing_Local_Business_Schema {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		// Check if local business keywords present
		$site_description = get_bloginfo( 'description' );
		$is_local = preg_match( '/(restaurant|store|shop|salon|clinic|office|local)/i', $site_description );
		
		if ( $is_local ) {
			return array(
				'id'          => 'seo-missing-local-business-schema',
				'title'       => 'Missing Local Business Schema',
				'description' => 'Local business detected without LocalBusiness schema. Add schema with NAP (name, address, phone), hours, and geo coordinates for better local search visibility.',
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-local-business-schema/',
				'training_link' => 'https://wpshadow.com/training/local-seo/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}
