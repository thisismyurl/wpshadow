<?php
/**
 * Mobile Form Auto-fill Support
 *
 * Validates autocomplete attributes for faster mobile form completion.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Forms
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Form Auto-fill Support
 *
 * Ensures forms use HTML5 autocomplete attributes to enable
 * browser auto-fill, reducing typing burden on mobile devices.
 * WCAG1.0 Level AA requirement.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_Form_Autofill extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-form-autofill-support';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Form Auto-fill Support';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates autocomplete attributes for mobile';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'forms';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = self::find_autofill_issues();

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %d: number of fields without autocomplete */
				__( 'Found %d form fields without autocomplete attributes', 'wpshadow' ),
				count( $issues )
			),
			'severity'        => 'medium',
			'threat_level'    => 50,
			'issues'          => array_slice( $issues, 0, 10 ),
			'total_issues'    => count( $issues ),
			'wcag_violation'  => '1.3.5 Identify Input Purpose (Level AA)',
			'user_impact'     => __( 'Users must manually type all form data on mobile', 'wpshadow' ),
			'auto_fixable'    => true,
			'kb_link'         => 'https://wpshadow.com/kb/form-autofill',
		);
	}

	/**
	 * Find fields without autocomplete attributes.
	 *
	 * @since 1.6093.1200
	 * @return array Issues found.
	 */
	private static function find_autofill_issues(): array {
		$html = self::get_page_html();
		if ( ! $html ) {
			return array();
		}

		$issues = array();

		// Common field patterns that should have autocomplete
		$autocomplete_fields = array(
			'name'         => 'name',
			'first-name'   => 'given-name',
			'last-name'    => 'family-name',
			'email'        => 'email',
			'phone'        => 'tel',
			'address'      => 'street-address',
			'city'         => 'address-level2',
			'state'        => 'address-level1',
			'zip'          => 'postal-code',
			'country'      => 'country',
			'cc-number'    => 'cc-number',
			'cc-exp'       => 'cc-exp',
			'cc-csc'       => 'cc-csc',
		);

		foreach ( $autocomplete_fields as $field_pattern => $autocomplete_value ) {
			// Look for inputs matching pattern but without autocomplete
			$pattern = '/<input[^>]*name\s*=\s*["\'][^"\']*' . preg_quote( $field_pattern, '/' ) . '[^"\']*["\'][^>]*>/i';

			if ( preg_match_all( $pattern, $html, $matches ) ) {
				foreach ( $matches[0] as $input ) {
					if ( ! preg_match( '/autocomplete\s*=\s*["\']' . preg_quote( $autocomplete_value, '/' ) . '["\']/i', $input ) ) {
						preg_match( '/name\s*=\s*["\']([^"\']+)["\']/', $input, $name_match );
						$issues[] = array(
							'type'                      => 'missing-autocomplete',
							'field_name'                => $name_match[1] ?? $field_pattern,
							'recommended_autocomplete'  => $autocomplete_value,
							'field_type'                => $field_pattern,
						);
					}
				}
			}
		}

		return $issues;
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since 1.6093.1200
	 * @return string|null HTML content.
	 */
	private static function get_page_html(): ?string {
		return Diagnostic_HTML_Helper::fetch_homepage_html(
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
