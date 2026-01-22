<?php declare(strict_types=1);
/**
 * Tag Pages Indexation Diagnostic
 *
 * Philosophy: SEO indexation - too many tag pages dilute authority
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for tag page indexation issues.
 */
class Diagnostic_SEO_Tag_Pages_Indexation {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$tags = get_tags( array( 'hide_empty' => false ) );
		
		if ( count( $tags ) > 50 ) {
			return array(
				'id'          => 'seo-tag-pages-indexation',
				'title'       => 'Too Many Tag Pages',
				'description' => sprintf( '%d tag pages. Excessive tags create thin content and dilute authority. Consolidate to 20-30 meaningful tags. Consider noindexing low-traffic tag pages.', count( $tags ) ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/optimize-tag-pages/',
				'training_link' => 'https://wpshadow.com/training/tag-strategy/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
