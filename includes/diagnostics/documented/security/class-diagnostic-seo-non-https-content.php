<?php
declare(strict_types=1);
/**
 * Non-HTTPS Content Diagnostic
 *
 * Philosophy: SEO security - HTTPS is ranking factor
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for mixed content (HTTP resources on HTTPS pages).
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Non_HTTPS_Content extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( ! is_ssl() ) {
			return array(
				'id'          => 'seo-non-https-content',
				'title'       => 'Site Not Using HTTPS',
				'description' => 'Site not using HTTPS. Google favors secure sites in rankings. Install SSL certificate and force HTTPS.',
				'severity'    => 'high',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/enable-https/',
				'training_link' => 'https://wpshadow.com/training/ssl-setup/',
				'auto_fixable' => false,
				'threat_level' => 70,
			);
		}
		
		// Check for mixed content
		global $wpdb;
		$mixed = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_content LIKE '%http://%'"
		);
		
		if ( $mixed > 0 ) {
			return array(
				'id'          => 'seo-mixed-content',
				'title'       => 'Mixed Content Detected',
				'description' => sprintf( '%d pages contain HTTP resources on HTTPS site. Mixed content triggers browser warnings. Update all HTTP URLs to HTTPS.', $mixed ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/fix-mixed-content/',
				'training_link' => 'https://wpshadow.com/training/https-migration/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Non HTTPS Content
	 * Slug: -seo-non-https-content
	 * File: class-diagnostic-seo-non-https-content.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Non HTTPS Content
	 * Slug: -seo-non-https-content
	 * 
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__seo_non_https_content(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented',
		);
	}

}
