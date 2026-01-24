<?php

declare(strict_types=1);
/**
 * Missing Privacy Policy Link Diagnostic
 *
 * Philosophy: SEO trust - privacy policy is trust signal
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for privacy policy page.
 *
 * @verified 2026-01-23 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_Privacy_Policy extends Diagnostic_Base
{
	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string
	{
		return 'privacy-policy-exists';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string
	{
		return __('Privacy Policy page exists', 'wpshadow');
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string
	{
		return __('Ensure your site has a Privacy Policy page for legal compliance (GDPR, CCPA) and user trust.', 'wpshadow');
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string
	{
		return 'compliance';
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int
	{
		return 70;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string
	{
		return 'https://wpshadow.com/kb/create-privacy-policy/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string
	{
		return 'https://wpshadow.com/training/trust-signals/';
	}

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		// Check official WordPress privacy policy setting
		$privacy_page_id = get_option('wp_page_for_privacy_policy');

		if ($privacy_page_id) {
			$privacy_page = get_post($privacy_page_id);
			if ($privacy_page && 'publish' === $privacy_page->post_status) {
				return null; // Privacy policy set and published, no issue
			}
		}

		// Fallback: Search for common privacy policy page names
		$privacy_names = array(
			'Privacy Policy',
			'Privacy',
			'Data Privacy',
			'Privacy Notice',
		);

		foreach ($privacy_names as $name) {
			$page = get_page_by_title($name, OBJECT, 'page');
			if ($page && 'publish' === $page->post_status) {
				return null; // Found privacy page, no issue
			}
		}

		return array(
			'id'          => 'privacy-policy-exists',
			'title'       => 'Privacy Policy Not Found',
			'description' => 'No privacy policy page found. This is required for legal compliance (GDPR, CCPA) and builds user trust. <a href="https://wpshadow.com/kb/create-privacy-policy/" target="_blank">Learn how to create a Privacy Policy</a>',
			'severity'    => 'medium',
			'category'    => 'compliance',
			'kb_link'     => 'https://wpshadow.com/kb/create-privacy-policy/',
			'training_link' => 'https://wpshadow.com/training/trust-signals/',
			'auto_fixable' => false,
			'threat_level' => 70,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Test Purpose:
	 * Verify check() method correctly detects Privacy Policy pages.
	 * Pass criteria: Privacy page exists (official setting or common names)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__seo_missing_privacy_policy(): array
	{
		$result = self::check();

		if (is_null($result)) {
			return array(
				'passed'  => true,
				'message' => '✓ Privacy Policy page found',
			);
		}

		return array(
			'passed'  => false,
			'message' => '✗ Privacy Policy: ' . $result['title'],
		);
	}
}
