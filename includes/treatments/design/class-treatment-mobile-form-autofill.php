<?php
/**
 * Mobile Form Auto-fill Support
 *
 * Validates autocomplete attributes for faster mobile form completion.
 *
 * @package    WPShadow
 * @subpackage Treatments\Forms
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_HTML_Helper;
use WPShadow\Core\Treatment_Base;

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
class Treatment_Mobile_Form_Autofill extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-form-autofill-support';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Form Auto-fill Support';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates autocomplete attributes for mobile';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'forms';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Form_Autofill' );
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
		return Treatment_HTML_Helper::fetch_homepage_html(
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
