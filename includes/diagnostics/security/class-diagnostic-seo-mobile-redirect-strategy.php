<?php
declare(strict_types=1);
/**
 * Mobile Redirect Strategy Diagnostic
 *
 * Philosophy: Separate mobile URLs need proper configuration
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Mobile_Redirect_Strategy extends Diagnostic_Base {
    public static function check(): ?array {
        // Check if site uses separate mobile URLs
        $site_url = get_site_url();
        $parsed = parse_url($site_url);
        
        if (!isset($parsed['host'])) {
            return null;
        }
        
        // Check if this is a mobile subdomain (m.example.com)
        if (strpos($parsed['host'], 'm.') === 0 || strpos($parsed['host'], 'mobile.') === 0) {
            // This IS a mobile URL, check for alternate link
            return [
                'id' => 'seo-mobile-redirect-strategy',
                'title' => 'Mobile URL Configuration Review',
                'description' => 'Using separate mobile subdomain. Ensure proper rel=alternate and rel=canonical tags.',
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/mobile-redirects/',
                'training_link' => 'https://wpshadow.com/training/mobile-url-structure/',
                'auto_fixable' => false,
                'threat_level' => 50,
            ];
        }
        
        // Most sites use responsive design now, not separate mobile URLs
        return null;
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Mobile Redirect Strategy
	 * Slug: -seo-mobile-redirect-strategy
	 * File: class-diagnostic-seo-mobile-redirect-strategy.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Mobile Redirect Strategy
	 * Slug: -seo-mobile-redirect-strategy
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
	public static function test_live__seo_mobile_redirect_strategy(): array {
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
