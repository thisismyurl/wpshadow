<?php
/**
 * Mobile Search Input Optimization
 *
 * Validates search input fields for mobile usability.
 *
 * @package    WPShadow
 * @subpackage Treatments\Forms
 * @since      1.602.1445
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_HTML_Helper;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Search Input Optimization
 *
 * Ensures search inputs are optimized for mobile devices with
 * proper size, placeholder text, and submit button accessibility.
 *
 * @since 1.602.1445
 */
class Treatment_Mobile_Search_Input extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-search-input-optimization';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Search Input Optimization';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates search inputs for mobile usability';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'forms';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = self::find_search_issues();

		if ( empty( $issues['all'] ) ) {
			return null;
		}

		return array(
			'id'             => self::$slug,
			'title'          => self::$title,
			'description'    => sprintf(
				/* translators: %d: number of search optimization issues */
				__( 'Found %d mobile search optimization issues', 'wpshadow' ),
				count( $issues['all'] )
			),
			'severity'       => 'medium',
			'threat_level'   => 50,
			'issues'         => $issues['all'],
			'has_search'     => $issues['has_search'] ?? false,
			'user_impact'    => __( 'Search is difficult to use on mobile devices', 'wpshadow' ),
			'auto_fixable'   => true,
			'kb_link'        => 'https://wpshadow.com/kb/mobile-search',
		);
	}

	/**
	 * Find search input issues.
	 *
	 * @since  1.602.1445
	 * @return array Issues found.
	 */
	private static function find_search_issues(): array {
		$html = self::get_page_html();
		if ( ! $html ) {
			return array( 'all' => array() );
		}

		$issues = array();
		$has_search = false;

		// Check for search forms
		if ( preg_match( '/<form[^>]*(?:search|searchform)[^>]*>/i', $html ) ||
			 preg_match( '/<input[^>]*type\s*=\s*["\']search["\']/i', $html ) ) {
			$has_search = true;
		}

		if ( ! $has_search ) {
			return array(
				'all'        => array(),
				'has_search' => false,
			);
		}

		// Check search input type
		if ( ! preg_match( '/<input[^>]*type\s*=\s*["\']search["\']/i', $html ) ) {
			$issues[] = array(
				'type'  => 'wrong-input-type',
				'issue' => 'Search input should use type="search"',
			);
		}

		// Check for submit button
		$submit_checks = self::check_submit_button( $html );
		$issues = array_merge( $issues, $submit_checks );

		// Check for placeholder text
		$placeholder_checks = self::check_placeholder( $html );
		$issues = array_merge( $issues, $placeholder_checks );

		// Check for autocomplete
		$autocomplete_checks = self::check_autocomplete( $html );
		$issues = array_merge( $issues, $autocomplete_checks );

		return array(
			'all'        => $issues,
			'has_search' => true,
		);
	}

	/**
	 * Check for accessible submit button.
	 *
	 * @since  1.602.1445
	 * @param  string $html HTML content.
	 * @return array Submit button issues.
	 */
	private static function check_submit_button( string $html ): array {
		$issues = array();

		// Check for submit button
		$has_submit_button = preg_match( '/<button[^>]*type\s*=\s*["\']submit["\']/i', $html );
		$has_submit_input = preg_match( '/<input[^>]*type\s*=\s*["\']submit["\']/i', $html );

		if ( ! $has_submit_button && ! $has_submit_input ) {
			$issues[] = array(
				'type'  => 'no-submit-button',
				'issue' => 'Search form missing visible submit button',
			);
		}

		// Check for icon-only button without aria-label
		if ( preg_match( '/<button[^>]*class\s*=\s*["\'][^"\']*(?:icon|search-icon)[^"\']*["\']/i', $html ) ) {
			if ( ! preg_match( '/<button[^>]*aria-label\s*=\s*["\'][^"\']+["\']/i', $html ) ) {
				$issues[] = array(
					'type'  => 'icon-button-no-label',
					'issue' => 'Search icon button missing aria-label',
				);
			}
		}

		return $issues;
	}

	/**
	 * Check for placeholder text.
	 *
	 * @since  1.602.1445
	 * @param  string $html HTML content.
	 * @return array Placeholder issues.
	 */
	private static function check_placeholder( string $html ): array {
		$issues = array();

		// Extract search input
		if ( preg_match( '/<input[^>]*(?:type\s*=\s*["\']search["\']|name\s*=\s*["\']s["\'])[^>]*>/i', $html, $matches ) ) {
			if ( ! preg_match( '/placeholder\s*=\s*["\'][^"\']+["\']/i', $matches[0] ) ) {
				$issues[] = array(
					'type'  => 'no-placeholder',
					'issue' => 'Search input missing helpful placeholder text',
				);
			}
		}

		return $issues;
	}

	/**
	 * Check for autocomplete support.
	 *
	 * @since  1.602.1445
	 * @param  string $html HTML content.
	 * @return array Autocomplete issues.
	 */
	private static function check_autocomplete( string $html ): array {
		$issues = array();

		// Check if search has autocomplete disabled
		if ( preg_match( '/<input[^>]*type\s*=\s*["\']search["\'][^>]*autocomplete\s*=\s*["\']off["\']/i', $html ) ) {
			$issues[] = array(
				'type'  => 'autocomplete-disabled',
				'issue' => 'Search autocomplete disabled (reduces mobile efficiency)',
			);
		}

		return $issues;
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since  1.602.1445
	 * @return string|null HTML content.
	 */
	private static function get_page_html(): ?string {
		return Treatment_HTML_Helper::fetch_homepage_html(
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
