<?php
declare(strict_types=1);
/**
 * Content Expiration/Stale Content Detection Diagnostic
 *
 * Philosophy: SEO & security - identify outdated/stale content
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for stale content that should be updated or removed.
 */
class Diagnostic_Stale_Content_Detection extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Find posts not updated in 2+ years
		$stale_posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title, post_modified FROM {$wpdb->posts} 
				WHERE post_status = 'publish' 
				AND post_modified < DATE_SUB(NOW(), INTERVAL 2 YEAR)
				AND post_type IN ('post', 'page') LIMIT 10"
			)
		);
		
		if ( ! empty( $stale_posts ) ) {
			return array(
				'id'          => 'stale-content-detection',
				'title'       => 'Stale Content Not Updated in 2+ Years',
				'description' => sprintf(
					'Found %d old posts/pages not updated since 2020. Stale content harms SEO and user experience. Either update or add "last updated" dates.',
					count( $stale_posts )
				),
				'severity'    => 'low',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/manage-content-freshness/',
				'training_link' => 'https://wpshadow.com/training/content-strategy/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}
}
