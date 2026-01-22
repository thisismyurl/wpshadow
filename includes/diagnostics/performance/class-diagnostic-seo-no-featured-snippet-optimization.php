<?php
declare(strict_types=1);
/**
 * No Featured Snippet Optimization Diagnostic
 *
 * Philosophy: SEO position zero - featured snippets drive traffic
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for featured snippet optimization.
 */
class Diagnostic_SEO_No_Featured_Snippet_Optimization extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$posts = $wpdb->get_results(
			"SELECT post_content FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			LIMIT 10"
		);
		
		$has_optimization = false;
		foreach ( $posts as $post ) {
			// Check for list formats, tables, or definition paragraphs
			if ( preg_match( '/<ol>|<ul>|<table>|<h2>What is|<h2>How to/i', $post->post_content ) ) {
				$has_optimization = true;
				break;
			}
		}
		
		if ( ! $has_optimization ) {
			return array(
				'id'          => 'seo-no-featured-snippet',
				'title'       => 'No Featured Snippet Optimization',
				'description' => 'Content not optimized for featured snippets. Use: numbered/bulleted lists, definition paragraphs, tables, "What is..." headers. Featured snippets appear above position #1.',
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/win-featured-snippets/',
				'training_link' => 'https://wpshadow.com/training/position-zero/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
