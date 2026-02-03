<?php
/**
 * Mobile Input Type Support
 *
 * Ensures forms use mobile-optimized input types.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Forms
 * @since      1.2602.1630
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Input Type Support
 *
 * Validates forms use proper input types (tel, email, url, number)
 * to trigger mobile-specific keyboards.
 *
 * @since 1.2602.1630
 */
class Diagnostic_Mobile_Input_Type_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-input-type-support';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Input Type Support';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Ensures forms use mobile-optimized input types';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'forms';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2602.1630
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = self::find_input_type_issues();

		if ( empty( $issues['all'] ) ) {
			return null; // Form inputs well-typed
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %d: count of wrongly-typed inputs */
				__( 'Found %d inputs not using mobile-optimized types', 'wpshadow' ),
				count( $issues['all'] )
			),
			'severity'        => 'medium',
			'threat_level'    => 50,
			'issues'          => array_slice( $issues['all'], 0, 5 ),
			'total_issues'    => count( $issues['all'] ),
			'recommendations' => $issues['recommendations'] ?? array(),
			'user_impact'     => __( 'Wrong keyboard types make mobile form entry slow', 'wpshadow' ),
			'auto_fixable'    => true,
			'kb_link'         => 'https://wpshadow.com/kb/input-type-support',
		);
	}

	/**
	 * Find input type issues.
	 *
	 * @since  1.2602.1630
	 * @return array Issues found.
	 */
	private static function find_input_type_issues(): array {
		$html = self::get_page_html();
		if ( ! $html ) {
			return array( 'all' => array() );
		}

		$issues = array(
			'all'              => array(),
			'recommendations'  => array(),
		);

		// Find all inputs
		preg_match_all( '/<input[^>]+>/i', $html, $inputs );

		foreach ( $inputs[0] ?? array() as $input ) {
			// Check type
			if ( ! preg_match( '/type\s*=\s*["\']?([^"\'\s>]+)["\']?/i', $input, $type_match ) ) {
				continue;
			}

			$type = strtolower( $type_match[1] );

			// Check for phone inputs
			if ( preg_match( '/phone|tel(?!emetry)/i', $input ) && $type !== 'tel' ) {
				$issues['all'][] = array(
					'issue'           => 'Phone field using type="' . $type . '" instead of "tel"',
					'recommended'     => 'type="tel"',
					'mobile_keyboard' => 'Numeric keypad with + and *',
				);
			}

			// Check for email inputs
			if ( preg_match( '/email|mail/i', $input ) && $type !== 'email' ) {
				$issues['all'][] = array(
					'issue'           => 'Email field using type="' . $type . '" instead of "email"',
					'recommended'     => 'type="email"',
					'mobile_keyboard' => 'Keyboard with @, ., and .com keys',
				);
			}

			// Check for URL inputs
			if ( preg_match( '/url|website|link/i', $input ) && $type !== 'url' ) {
				$issues['all'][] = array(
					'issue'           => 'URL field using type="' . $type . '" instead of "url"',
					'recommended'     => 'type="url"',
					'mobile_keyboard' => 'Keyboard with / and .com keys',
				);
			}

			// Check for number inputs
			if ( preg_match( '/number|quantity|amount|price/i', $input ) && $type !== 'number' ) {
				$issues['all'][] = array(
					'issue'           => 'Number field using type="' . $type . '" instead of "number"',
					'recommended'     => 'type="number"',
					'mobile_keyboard' => 'Numeric keypad',
				);
			}
		}

		$issues['recommendations'] = array(
			'Use type="tel" for phone numbers',
			'Use type="email" for email addresses',
			'Use type="url" for website URLs',
			'Use type="number" for quantities/prices',
			'Use type="date" for date inputs (native picker)',
			'Use type="time" for time inputs (native picker)',
		);

		return $issues;
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since  1.2602.1630
	 * @return string|null HTML content.
	 */
	private static function get_page_html(): ?string {
		$response = wp_remote_get(
			home_url( '/' ),
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		return wp_remote_retrieve_body( $response );
	}
}
