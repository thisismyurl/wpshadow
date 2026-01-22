<?php
declare(strict_types=1);
/**
 * Missing Open Graph Tags Diagnostic
 *
 * Philosophy: SEO social - OG tags control social media appearance
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing Open Graph tags.
 */
class Diagnostic_SEO_Missing_Open_Graph extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$has_og = has_action( 'wp_head' );
		
		if ( ! $has_og ) {
			return array(
				'id'          => 'seo-missing-open-graph',
				'title'       => 'Missing Open Graph Tags',
				'description' => 'No Open Graph (og:) tags detected. OG tags control how content appears when shared on Facebook, LinkedIn, etc. Add OG tags for title, description, image.',
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-open-graph-tags/',
				'training_link' => 'https://wpshadow.com/training/social-media-seo/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
