<?php
declare(strict_types=1);
/**
 * Keyword Cannibalization Diagnostic
 *
 * Philosophy: SEO focus - multiple pages competing hurts both
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for keyword cannibalization (multiple pages targeting same keyword).
 */
class Diagnostic_SEO_Keyword_Cannibalization extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Check for duplicate titles (indicator of cannibalization)
		$duplicates = $wpdb->get_results(
			"SELECT post_title, COUNT(*) as count 
			FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			GROUP BY post_title 
			HAVING count > 1"
		);
		
		if ( ! empty( $duplicates ) ) {
			return array(
				'id'          => 'seo-keyword-cannibalization',
				'title'       => 'Keyword Cannibalization Detected',
				'description' => sprintf( '%d duplicate titles found (possible keyword cannibalization). Multiple pages targeting same keyword compete against each other. Consolidate or differentiate content.', count( $duplicates ) ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/fix-keyword-cannibalization/',
				'training_link' => 'https://wpshadow.com/training/keyword-strategy/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}
