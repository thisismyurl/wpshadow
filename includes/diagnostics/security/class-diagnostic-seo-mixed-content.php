<?php
declare(strict_types=1);
/**
 * Mixed Content Diagnostic
 *
 * Philosophy: Avoid HTTP assets on HTTPS pages
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Mixed_Content extends Diagnostic_Base {
    public static function check(): ?array {
        // Only check if site uses HTTPS
        if (!is_ssl()) {
            return null;
        }
        
        // Check homepage for mixed content
        $response = wp_remote_get(home_url(), array('timeout' => 10));
        
        if (is_wp_error($response)) {
            return null;
        }
        
        $body = wp_remote_retrieve_body($response);
        
        // Look for http:// in src/href attributes
        if (preg_match_all('/(?:src|href)=["\']http:\/\/[^"\']+["\']/', $body, $matches)) {
            $count = count($matches[0]);
            
            return [
                'id' => 'seo-mixed-content',
                'title' => 'Mixed Content Detected',
                'description' => sprintf('Found %d HTTP resource(s) on HTTPS page. Fix to avoid browser warnings.', $count),
                'severity' => 'medium',
                'category' => 'security',
                'kb_link' => 'https://wpshadow.com/kb/mixed-content-fix/',
                'training_link' => 'https://wpshadow.com/training/https-best-practices/',
                'auto_fixable' => true,
                'threat_level' => 50,
            ];
        }
        
        return null;
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Mixed Content
	 * Slug: -seo-mixed-content
	 * File: class-diagnostic-seo-mixed-content.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Mixed Content
	 * Slug: -seo-mixed-content
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
	public static function test_live__seo_mixed_content(): array {
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
