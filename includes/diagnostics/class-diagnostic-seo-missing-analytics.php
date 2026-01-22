<?php declare(strict_types=1);
/**
 * Missing Analytics Implementation Diagnostic
 *
 * Philosophy: SEO measurement - can't improve what you don't measure
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for Google Analytics implementation.
 */
class Diagnostic_SEO_Missing_Analytics {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wpdb;
		
		$has_ga4 = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_content LIKE '%gtag(%' 
			OR post_content LIKE '%G-%" . "'
		);
		
		if ( $has_ga4 === 0 && ! has_action( 'wp_head', 'gtag' ) ) {
			return array(
				'id'          => 'seo-missing-analytics',
				'title'       => 'Google Analytics Not Detected',
				'description' => 'No Google Analytics 4 implementation found. Analytics tracks traffic, behavior, conversions. Install GA4 to measure SEO performance.',
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/install-google-analytics/',
				'training_link' => 'https://wpshadow.com/training/analytics-setup/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
