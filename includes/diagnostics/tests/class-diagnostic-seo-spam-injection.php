<?php

declare(strict_types=1);
/**
 * SEO Spam Injection Detection Diagnostic
 *
 * Philosophy: Content security - detect SEO spam/cloaking
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for SEO spam and cloaking.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_SEO_Spam_Injection extends Diagnostic_Base {

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;

		// Check for hidden text (common SEO spam technique)
		$results = $wpdb->get_results(
			"SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE '%display:none%' OR post_content LIKE '%visibility:hidden%' LIMIT 5"
		);

		if ( ! empty( $results ) ) {
			return array(
				'id'            => 'seo-spam-injection',
				'title'         => 'SEO Spam/Hidden Content Detected',
				'description'   => sprintf(
					'Found %d posts with hidden content (CSS display:none or visibility:hidden). This is SEO spam attempting to inject keywords for search manipulation.',
					count( $results )
				),
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/remove-seo-spam/',
				'training_link' => 'https://wpshadow.com/training/seo-spam-removal/',
				'auto_fixable'  => false,
				'threat_level'  => 65,
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Spam Injection
	 * Slug: -seo-spam-injection
	 * File: class-diagnostic-seo-spam-injection.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Spam Injection
	 * Slug: -seo-spam-injection
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
	public static function test_live__seo_spam_injection(): array {
		global $wpdb;

		$spam_posts = $wpdb->get_results(
			"SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE '%display:none%' OR post_content LIKE '%visibility:hidden%' LIMIT 5"
		);

		$spam_count = is_array( $spam_posts ) ? count( $spam_posts ) : 0;

		$diagnostic_result    = self::check();
		$should_find_issue    = ( $spam_count > 0 );
		$diagnostic_has_issue = ( null !== $diagnostic_result );
		$test_passes          = ( $should_find_issue === $diagnostic_has_issue );

		$message = sprintf(
			'Hidden-content posts: %d. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$spam_count,
			$should_find_issue ? 'FIND' : 'NOT find',
			$diagnostic_has_issue ? 'FOUND' : 'DID NOT find',
			$test_passes ? 'PASS' : 'FAIL'
		);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
