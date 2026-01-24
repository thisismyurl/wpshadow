<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Search_Indexing extends Diagnostic_Base {

	protected static $slug = 'search-indexing';
	protected static $title = 'Search Engine Indexing';
	protected static $description = 'Checks if search engines are blocked from indexing the site.';

	public static function check(): ?array {
		$blog_public = get_option( 'blog_public' );

		if ( '1' === $blog_public || 1 === $blog_public ) {
			return null;
		}

		return array(
			'id'   => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Search engines are blocked from indexing this site! The "Discourage search engines" setting is enabled. This is often accidentally left on after development and prevents the site from appearing in Google. Your site is invisible to search engines.', 'wpshadow' ),
			'category'     => 'seo',
			'severity'     => 'critical',
			'threat_level' => 98,
			'auto_fixable' => true,
			'timestamp'    => current_time( 'mysql' ),
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Search Engine Indexing
	 * Slug: search-indexing
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if search engines are blocked from indexing the site.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_search_indexing(): array {
		$blog_public = get_option( 'blog_public' );
		$expected_issue = ( '1' !== $blog_public && 1 !== $blog_public );
		$diagnostic_result = self::check();
		$diagnostic_has_issue = ( null !== $diagnostic_result );
		$test_passes = ( $expected_issue === $diagnostic_has_issue );

		$message = sprintf(
			'Discourage search engines flag: %s. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$expected_issue ? 'ENABLED' : 'DISABLED',
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
