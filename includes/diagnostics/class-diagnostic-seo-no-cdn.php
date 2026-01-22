<?php declare(strict_types=1);
/**
 * No CDN Implementation Diagnostic
 *
 * Philosophy: SEO performance - CDN improves global page speed
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if CDN is implemented.
 */
class Diagnostic_SEO_No_CDN {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wpdb;
		
		$cdn_domains = array( 'cloudflare', 'cloudfront', 'fastly', 'bunnycdn', 'stackpath' );
		$has_cdn = false;
		
		foreach ( $cdn_domains as $domain ) {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} 
					WHERE post_content LIKE %s",
					'%' . $wpdb->esc_like( $domain ) . '%'
				)
			);
			if ( $count > 0 ) {
				$has_cdn = true;
				break;
			}
		}
		
		if ( ! $has_cdn ) {
			return array(
				'id'          => 'seo-no-cdn',
				'title'       => 'No CDN Implementation',
				'description' => 'No CDN detected. CDNs serve assets from servers closer to visitors, reducing latency. Consider Cloudflare (free) or BunnyCDN.',
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/implement-cdn/',
				'training_link' => 'https://wpshadow.com/training/cdn-setup/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
