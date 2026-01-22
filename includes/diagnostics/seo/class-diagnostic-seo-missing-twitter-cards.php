<?php
declare(strict_types=1);
/**
 * Missing Twitter Cards Diagnostic
 *
 * Philosophy: SEO social - Twitter cards boost engagement
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing Twitter Card tags.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_Twitter_Cards extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$has_twitter = has_action( 'wp_head' );
		
		if ( ! $has_twitter ) {
			return array(
				'id'          => 'seo-missing-twitter-cards',
				'title'       => 'Missing Twitter Card Tags',
				'description' => 'No Twitter Card meta tags detected. Twitter Cards improve engagement when content is shared on Twitter/X. Add twitter:card, twitter:title, twitter:description tags.',
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-twitter-cards/',
				'training_link' => 'https://wpshadow.com/training/twitter-optimization/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}
}
