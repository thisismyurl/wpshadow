<?php

declare(strict_types=1);
/**
 * XML Sitemap Availability Diagnostic
 *
 * Philosophy: SEO basics to build trust; guides to Pro/Guardian SEO insights.
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if XML sitemap is accessible.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_XML_Sitemap extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		$sitemap_url = home_url('/sitemap.xml');
		$response = wp_remote_head($sitemap_url, array('timeout' => 8, 'sslverify' => false));

		if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) >= 400) {
			return array(
				'id'          => 'xml-sitemap',
				'title'       => 'XML Sitemap Not Found',
				'description' => 'Search engines rely on your XML sitemap to discover content. Ensure /sitemap.xml is available or provided by your SEO plugin.',
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/create-xml-sitemap/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=xml-sitemap',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: XML Sitemap
	 * Slug: -xml-sitemap
	 * File: class-diagnostic-xml-sitemap.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: XML Sitemap
	 * Slug: -xml-sitemap
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
	public static function test_live__xml_sitemap(): array
	{
		$sitemap_url = home_url('/sitemap.xml');
		$response   = wp_remote_head($sitemap_url, array('timeout' => 8, 'sslverify' => false));
		$code       = is_wp_error($response) ? 0 : (int) wp_remote_retrieve_response_code($response);
		$expected_issue = (is_wp_error($response) || $code >= 400);

		$diagnostic_result    = self::check();
		$diagnostic_has_issue = (null !== $diagnostic_result);
		$test_passes          = ($expected_issue === $diagnostic_has_issue);

		$message = sprintf(
			'Sitemap HEAD status: %s. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			is_wp_error($response) ? 'ERROR' : $code,
			$expected_issue ? 'FIND' : 'NOT find',
			$diagnostic_has_issue ? 'FOUND' : 'DID NOT find',
			$test_passes ? 'PASS' : 'FAIL'
		);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
