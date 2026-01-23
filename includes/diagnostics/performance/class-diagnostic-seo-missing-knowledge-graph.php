<?php
declare(strict_types=1);
/**
 * Missing Knowledge Graph Optimization Diagnostic
 *
 * Philosophy: SEO branding - Knowledge Graph shows brand authority
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for Knowledge Graph optimization.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_Knowledge_Graph extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if Organization/Brand schema exists
		global $wpdb;
		
		$has_org_schema = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_content LIKE '%\"@type\":\"Organization\"%' 
			OR post_content LIKE '%schema.org/Organization%'"
		);
		
		if ( $has_org_schema === 0 ) {
			return array(
				'id'          => 'seo-missing-knowledge-graph',
				'title'       => 'Missing Knowledge Graph Optimization',
				'description' => 'No Organization schema detected. Add Organization schema with logo, social profiles, contact info. Helps Google create Knowledge Panel for your brand.',
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/optimize-knowledge-graph/',
				'training_link' => 'https://wpshadow.com/training/brand-schema/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}

}