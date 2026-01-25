<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Are Terms of Service in place?
 *
 * Category: Compliance & Legal Risk
 * Priority: 1
 * Philosophy: 10
 *
 * Test Description:
 * Are Terms of Service in place?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Terms_Of_Service_Exists extends Diagnostic_Base {

	protected static $slug = 'terms-of-service-exists';

	protected static $title = 'Terms of Service Page';

	protected static $description = 'Detects missing Terms of Service page - a legal requirement and trust signal.';

	protected static $family = 'compliance';

	protected static $family_label = 'Compliance';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'terms-of-service-exists';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Terms of Service page exists', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Ensure your site has a Terms of Service page under common names for legal compliance and trust.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return 'compliance';
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		return 60;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/create-terms-of-service/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/trust-signals/';
	}

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding data or null if no issue
	 */
	public static function check(): ?array {
		// Common Terms of Service page titles
		$tos_names = array(
			'Terms of Service',
			'Terms & Conditions',
			'Terms',
			'Legal Terms',
			'User Agreement',
			'Terms and Conditions',
		);

		// Search for ToS page by title
		foreach ( $tos_names as $name ) {
			$page = get_page_by_title( $name, OBJECT, 'page' );
			if ( $page && 'publish' === $page->post_status ) {
				return null; // Found ToS page, no issue
			}
		}

		return array(
			'id'            => 'terms-of-service-exists',
			'title'         => 'Terms of Service Page Not Found',
			'description'   => 'No published Terms of Service page found. Create one with clear terms for legal compliance and user trust. <a href="https://wpshadow.com/kb/create-terms-of-service/" target="_blank">Learn how to create ToS</a>',
			'severity'      => 'medium',
			'category'      => 'compliance',
			'kb_link'       => 'https://wpshadow.com/kb/create-terms-of-service/',
			'training_link' => 'https://wpshadow.com/training/trust-signals/',
			'auto_fixable'  => false,
			'threat_level'  => 60,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Test Purpose:
	 * Verify check() method correctly detects Terms of Service pages.
	 * Pass criteria: ToS page exists under common names
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_terms_of_service_exists(): array {
		$result = self::check();

		if ( is_null( $result ) ) {
			return array(
				'passed'  => true,
				'message' => '✓ Terms of Service page found',
			);
		}

		return array(
			'passed'  => false,
			'message' => '✗ Terms of Service: ' . $result['title'],
		);
	}
}
