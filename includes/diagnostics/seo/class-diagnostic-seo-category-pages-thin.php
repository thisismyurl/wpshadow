<?php
declare(strict_types=1);
/**
 * Category Pages Thin Content Diagnostic
 *
 * Philosophy: SEO taxonomy - category pages should have unique content
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for thin category page content.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Category_Pages_Thin extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$categories = get_categories( array( 'hide_empty' => true, 'number' => 10 ) );
		
		$thin = 0;
		foreach ( $categories as $category ) {
			if ( empty( $category->description ) || strlen( $category->description ) < 100 ) {
				$thin++;
			}
		}
		
		if ( $thin > 0 ) {
			return array(
				'id'          => 'seo-category-pages-thin',
				'title'       => 'Category Pages Lack Content',
				'description' => sprintf( '%d category pages have thin/no descriptions. Add 200-300 words to each category page explaining the topic. Category pages can rank for broader keywords.', $thin ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/optimize-category-pages/',
				'training_link' => 'https://wpshadow.com/training/taxonomy-seo/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
