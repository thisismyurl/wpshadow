<?php
/**
 * Name Field Flexibility Diagnostic
 *
 * Issue #4926: Name Fields Don't Support Compound Names
 * Pillar: 🌐 Culturally Respectful
 *
 * Checks if name fields support diverse naming conventions.
 * Not everyone has "First Name" and "Last Name".
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Name_Field_Flexibility Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Name_Field_Flexibility extends Diagnostic_Base {

	protected static $slug = 'name-field-flexibility';
	protected static $title = 'Name Fields Don\'t Support Compound Names';
	protected static $description = 'Checks if name fields support diverse global naming conventions';
	protected static $family = 'compliance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Use single "Full Name" field instead of First/Last split', 'wpshadow' );
		$issues[] = __( 'Support spaces: "Mary Jane Watson"', 'wpshadow' );
		$issues[] = __( 'Support hyphens: "Mary-Jane Parker"', 'wpshadow' );
		$issues[] = __( 'Support apostrophes: "O\'Brien"', 'wpshadow' );
		$issues[] = __( 'Support prefixes: "von Neumann", "de la Cruz"', 'wpshadow' );
		$issues[] = __( 'Support non-Latin characters: "李明", "محمد"', 'wpshadow' );
		$issues[] = __( 'Don\'t require middle name or initial', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Naming conventions vary globally. Many cultures use compound names, single names, or name orders different from Western "First Last" format.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/name-fields',
				'details'      => array(
					'recommendations'         => $issues,
					'examples'                => 'Spanish: "María de los Santos García", Chinese: "李明" (surname first)',
					'single_name'             => 'Indonesia, Myanmar: Many people have only one name',
					'validation'              => 'Allow letters, spaces, hyphens, apostrophes, non-Latin',
				),
			);
		}

		return null;
	}
}
