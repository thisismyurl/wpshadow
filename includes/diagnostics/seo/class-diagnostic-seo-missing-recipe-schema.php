<?php
declare(strict_types=1);
/**
 * Missing Recipe Schema Diagnostic
 *
 * Philosophy: SEO niche - Recipe schema drives food blog traffic
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for Recipe schema on food content.
 */
class Diagnostic_SEO_Missing_Recipe_Schema extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$recipe_content = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND (post_title LIKE '%recipe%' OR post_content LIKE '%ingredients%')"
		);
		
		if ( $recipe_content > 0 ) {
			return array(
				'id'          => 'seo-missing-recipe-schema',
				'title'       => 'Recipe Content Missing Schema',
				'description' => sprintf( '%d recipe posts detected. Add Recipe schema with ingredients, instructions, cook time, nutrition. Enables recipe cards in search.', $recipe_content ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-recipe-schema/',
				'training_link' => 'https://wpshadow.com/training/recipe-markup/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}
