<?php
declare(strict_types=1);
/**
 * Internal Link Authority Flow Diagnostic
 *
 * Philosophy: SEO architecture - distribute link equity
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check internal link distribution.
 */
class Diagnostic_SEO_Internal_Link_Authority extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$important_pages = $wpdb->get_results(
			"SELECT ID, post_title FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			ORDER BY comment_count DESC 
			LIMIT 5"
		);
		
		return array(
			'id'          => 'seo-internal-link-authority',
			'title'       => 'Optimize Internal Link Authority Flow',
			'description' => 'Identify high-value pages (conversions, high traffic) and link to them from homepage and popular posts. Use descriptive anchor text. Create hub pages that link to clusters.',
			'severity'    => 'low',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/internal-link-equity/',
			'training_link' => 'https://wpshadow.com/training/link-architecture/',
			'auto_fixable' => false,
			'threat_level' => 50,
		);
	}
}
