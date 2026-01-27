<?php
/**
 * Diagnostic: Duplicate Script Handle Detection
 *
 * Detects scripts or styles registered with duplicate handles.
 * Duplicate handles can cause unpredictable behavior and conflicts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Duplicate_Script_Handle
 *
 * Checks for duplicate script and style handles.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Duplicate_Script_Handle extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'duplicate-script-handle';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Duplicate Script Handle Detection';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects scripts or styles with duplicate handles';

	/**
	 * Check for duplicate script and style handles.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wp_scripts, $wp_styles;

		$script_handles = array();
		$style_handles  = array();
		$duplicates     = array();

		// Collect all script handles.
		if ( $wp_scripts instanceof \WP_Scripts ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( isset( $script_handles[ $handle ] ) ) {
					$duplicates['scripts'][ $handle ] = array(
						'source1' => $script_handles[ $handle ],
						'source2' => $script->src ?? 'unknown',
					);
				} else {
					$script_handles[ $handle ] = $script->src ?? 'unknown';
				}
			}
		}

		// Collect all style handles.
		if ( $wp_styles instanceof \WP_Styles ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( isset( $style_handles[ $handle ] ) ) {
					$duplicates['styles'][ $handle ] = array(
						'source1' => $style_handles[ $handle ],
						'source2' => $style->src ?? 'unknown',
					);
				} else {
					$style_handles[ $handle ] = $style->src ?? 'unknown';
				}
			}
		}

		// Check for cross-contamination (script handle used for style or vice versa).
		$cross_contamination = array_intersect_key( $script_handles, $style_handles );

		if ( ! empty( $cross_contamination ) ) {
			$duplicates['cross_contamination'] = $cross_contamination;
		}

		$total_duplicates = count( $duplicates );

		if ( $total_duplicates > 0 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Duplicate script or style handles detected. This can cause conflicts and unpredictable behavior.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/duplicate_script_handle',
				'meta'        => array(
					'duplicates' => $duplicates,
				),
			);
		}

		// No duplicates found.
		return null;
	}
}
