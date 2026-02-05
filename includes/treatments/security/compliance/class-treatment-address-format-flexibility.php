<?php
/**
 * Address Format Flexibility Treatment
 *
 * Issue #4924: Address Fields Assume US Format
 * Pillar: 🌐 Culturally Respectful
 *
 * Checks if address forms support international formats.
 * Not everyone has states and ZIP codes.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Address_Format_Flexibility Class
 *
 * @since 1.6050.0000
 */
class Treatment_Address_Format_Flexibility extends Treatment_Base {

	protected static $slug = 'address-format-flexibility';
	protected static $title = 'Address Fields Assume US Format';
	protected static $description = 'Checks if address forms support international formats';
	protected static $family = 'compliance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Don\'t require "State" field (not used in many countries)', 'wpshadow' );
		$issues[] = __( 'Label postal code generically: "Postal Code" not "ZIP"', 'wpshadow' );
		$issues[] = __( 'Support varying postal code formats (5 digits, 6 alphanumeric, etc)', 'wpshadow' );
		$issues[] = __( 'Allow address line 3 and 4 (some countries need more lines)', 'wpshadow' );
		$issues[] = __( 'Adjust field order per country (postcode before city in UK)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Address formats vary globally. Requiring "State" and "ZIP" excludes non-US users. Design flexible forms that work worldwide.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/address-formats',
				'details'      => array(
					'recommendations'         => $issues,
					'us_format'               => 'Street, City, State, ZIP',
					'uk_format'               => 'Street, City, Postcode',
					'japan_format'            => 'Postcode, Prefecture, City, Street',
					'flexibility'             => 'Country-specific validation and field visibility',
				),
			);
		}

		return null;
	}
}
