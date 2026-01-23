<?php
declare(strict_types=1);
/**
 * Phishing Page Detection Diagnostic
 *
 * Philosophy: Content security - detect phishing kits
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for phishing page indicators.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Phishing_Page_Detection extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Scan post content for phishing patterns
		$results = $wpdb->get_results(
			"SELECT ID, post_title FROM {$wpdb->posts} WHERE post_content LIKE '%<form%action%' AND post_status = 'publish' LIMIT 5"
		);
		
		if ( ! empty( $results ) ) {
			foreach ( $results as $post ) {
				$content = get_post_field( 'post_content', $post->ID );
				
				// Look for suspicious form patterns
				if ( preg_match( '/password|credit.?card|ssn|account.?number/i', $content ) ) {
					return array(
						'id'          => 'phishing-page-detection',
						'title'       => 'Possible Phishing Page Detected',
						'description' => sprintf(
							'Post "%s" contains forms requesting sensitive information (passwords, credit cards). This may be a phishing kit. Review and remove immediately.',
							esc_html( $post->post_title )
						),
						'severity'    => 'critical',
						'category'    => 'security',
						'kb_link'     => 'https://wpshadow.com/kb/remove-phishing-pages/',
						'training_link' => 'https://wpshadow.com/training/phishing-removal/',
						'auto_fixable' => false,
						'threat_level' => 90,
					);
				}
			}
		}
		
		return null;
	}

}