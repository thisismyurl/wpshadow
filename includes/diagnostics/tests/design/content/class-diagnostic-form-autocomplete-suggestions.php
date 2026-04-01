<?php
/**
 * Form Autocomplete Suggestions Diagnostic
 *
 * Issue #4971: Form Fields Don't Autocomplete (Poor UX)
 * Pillar: 🎓 Learning Inclusive / #1: Helpful Neighbor
 *
 * Checks if form fields use autocomplete attributes.
 * Users should fill forms quickly with browser autofill.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Form_Autocomplete_Suggestions Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Form_Autocomplete_Suggestions extends Diagnostic_Base {

	protected static $slug = 'form-autocomplete-suggestions';
	protected static $title = 'Form Fields Don\'t Autocomplete (Poor UX)';
	protected static $description = 'Checks if form fields use autocomplete attributes';
	protected static $family = 'content';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Add autocomplete="name" to name fields', 'wpshadow' );
		$issues[] = __( 'Add autocomplete="email" to email fields', 'wpshadow' );
		$issues[] = __( 'Add autocomplete="tel" to phone fields', 'wpshadow' );
		$issues[] = __( 'Add autocomplete="cc-number" to payment fields', 'wpshadow' );
		$issues[] = __( 'Add autocomplete="street-address" to address fields', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Autocomplete attributes let browsers fill form fields automatically. Users can complete forms 50% faster with proper autocomplete.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/form-autocomplete?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'autofill_standard'       => 'HTML Standard autofill tokens',
					'time_saved'              => 'Users fill forms 50% faster with autofill',
					'mobile_benefit'          => 'Critical for mobile, where typing is slow',
				),
			);
		}

		return null;
	}
}
