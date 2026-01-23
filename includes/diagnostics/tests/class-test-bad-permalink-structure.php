<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Bad Permalink Structure
 *
 * Detects when permalink structure is set to default (/?p=123) instead of SEO-friendly URLs.
 * Poor permalink structure hurts SEO and site usability.
 *
 * @since 1.2.0
 */
class Test_Bad_Permalink_Structure extends Diagnostic_Base
{

	/**
	 * Check for bad permalink structure
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array
	{
		$permalink_structure = get_option('permalink_structure', '');

		// Check if using default (empty) or only post ID
		$is_default = empty($permalink_structure) || $permalink_structure === '/?p=%post_id%';

		if (! $is_default) {
			return null; // Using good SEO-friendly structure
		}

		// Build recommendations
		$recommendations = self::get_permalink_recommendations();

		return [
			'threat_level'    => 50,
			'threat_color'    => 'yellow',
			'passed'          => false,
			'issue'           => 'Using default or poor permalink structure (bad for SEO)',
			'metadata'        => [
				'current_structure' => $permalink_structure ?: '(default)',
				'sample_url'        => self::get_sample_url(),
				'recommendations'   => $recommendations,
			],
			'kb_link'         => 'https://wpshadow.com/kb/seo-friendly-permalinks/',
			'training_link'   => 'https://wpshadow.com/training/wordpress-permalinks-seo/',
		];
	}

	/**
	 * Guardian Sub-Test: Current permalink structure
	 *
	 * @return array Test result
	 */
	public static function test_permalink_structure(): array
	{
		$structure = get_option('permalink_structure', '');
		$is_default = empty($structure) || $structure === '/?p=%post_id%';

		return [
			'test_name'        => 'Permalink Structure',
			'current_structure' => $structure ?: '(default - /?p=%post_id%)',
			'passed'           => ! $is_default,
			'structure_type'   => self::classify_permalink_structure($structure),
			'description'      => $is_default ? 'Using default permalink structure' : 'Using SEO-friendly permalink structure',
		];
	}

	/**
	 * Guardian Sub-Test: SEO impact assessment
	 *
	 * @return array Test result
	 */
	public static function test_seo_impact(): array
	{
		$structure = get_option('permalink_structure', '');
		$is_default = empty($structure) || $structure === '/?p=%post_id%';

		// Analyze SEO quality
		if ($is_default) {
			$seo_score = 30;
			$issues = [
				'Non-descriptive URLs for search engines',
				'Poor user experience in URLs',
				'No category/tag information in URL',
			];
		} else {
			$seo_score = 85;
			$issues = [];
		}

		return [
			'test_name'       => 'SEO Impact',
			'seo_score'       => $seo_score,
			'passed'          => $seo_score >= 80,
			'issues'          => $issues,
			'recommendation'  => 'Use /%postname%/ or /%category%/%postname%/ structure',
			'description'     => sprintf('SEO score: %d/100', $seo_score),
		];
	}

	/**
	 * Guardian Sub-Test: Permalink options recommendations
	 *
	 * @return array Test result
	 */
	public static function test_permalink_recommendations(): array
	{
		$recommendations = self::get_permalink_recommendations();

		return [
			'test_name'         => 'Permalink Structure Recommendations',
			'recommendations'   => $recommendations,
			'description'       => sprintf('Available SEO-friendly permalink options: %d', count($recommendations)),
		];
	}

	/**
	 * Guardian Sub-Test: Sample URL comparison
	 *
	 * @return array Test result
	 */
	public static function test_sample_urls(): array
	{
		$current_sample = self::get_sample_url();
		$recommended_sample = self::get_sample_url_recommended();

		return [
			'test_name'             => 'Sample URL Comparison',
			'current_url_structure' => $current_sample,
			'recommended_url'       => $recommended_sample,
			'description'           => 'Current vs recommended URL structure for posts',
		];
	}

	/**
	 * Get permalink structure recommendations
	 *
	 * @return array List of recommended structures
	 */
	private static function get_permalink_recommendations(): array
	{
		return [
			[
				'structure' => '/%postname%/',
				'example'   => '/my-awesome-post/',
				'pros'      => ['Simple', 'SEO-friendly', 'Readable'],
				'cons'      => ['No category info'],
			],
			[
				'structure' => '/%category%/%postname%/',
				'example'   => '/blog/my-awesome-post/',
				'pros'      => ['Category context', 'Organized', 'SEO-friendly'],
				'cons'      => ['Category changes break URLs'],
			],
			[
				'structure' => '/%year%/%monthnum%/%postname%/',
				'example'   => '/2026/01/my-awesome-post/',
				'pros'      => ['Date context', 'Archive-friendly', 'SEO-friendly'],
				'cons'      => ['Longer URLs'],
			],
		];
	}

	/**
	 * Get sample URL with current structure
	 *
	 * @return string Sample URL
	 */
	private static function get_sample_url(): string
	{
		$structure = get_option('permalink_structure', '');
		$home = home_url();

		if (empty($structure)) {
			return $home . '/?p=123';
		}

		return $home . str_replace('%postname%', 'sample-post', $structure);
	}

	/**
	 * Get recommended sample URL
	 *
	 * @return string Recommended URL structure example
	 */
	private static function get_sample_url_recommended(): string
	{
		$home = home_url();
		return $home . '/sample-post/';
	}

	/**
	 * Classify permalink structure type
	 *
	 * @param string $structure Permalink structure
	 * @return string Structure type
	 */
	private static function classify_permalink_structure(string $structure): string
	{
		if (empty($structure) || $structure === '/?p=%post_id%') {
			return 'default';
		}

		if (strpos($structure, '%postname%') === false) {
			return 'custom-but-not-optimal';
		}

		if (strpos($structure, '%category%') !== false) {
			return 'category-based';
		}

		if (strpos($structure, '%year%') !== false || strpos($structure, '%monthnum%') !== false) {
			return 'date-based';
		}

		return 'postname-based';
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string
	{
		return 'Bad Permalink Structure';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return 'Checks if permalink structure is optimized for SEO';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string
	{
		return 'SEO';
	}
}
