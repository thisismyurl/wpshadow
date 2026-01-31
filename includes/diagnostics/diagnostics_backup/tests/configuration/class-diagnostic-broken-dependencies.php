<?php
/**
 * Diagnostic: Broken Script/Style Dependencies
 *
 * Detects registered scripts and styles with broken or missing dependencies.
 * Broken dependencies can cause JavaScript errors and layout issues.
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
 * Class Diagnostic_Broken_Dependencies
 *
 * Checks for broken script and style dependencies.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Broken_Dependencies extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'broken-dependencies';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Broken Script/Style Dependencies';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects registered scripts/styles with broken dependencies';

	/**
	 * Check for broken script and style dependencies.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wp_scripts, $wp_styles;

		$broken_scripts = array();
		$broken_styles  = array();

		// Check script dependencies.
		if ( $wp_scripts instanceof \WP_Scripts ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( empty( $script->deps ) ) {
					continue;
				}

				foreach ( $script->deps as $dep ) {
					if ( ! isset( $wp_scripts->registered[ $dep ] ) ) {
						$broken_scripts[ $handle ] = $dep;
					}
				}
			}
		}

		// Check style dependencies.
		if ( $wp_styles instanceof \WP_Styles ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( empty( $style->deps ) ) {
					continue;
				}

				foreach ( $style->deps as $dep ) {
					if ( ! isset( $wp_styles->registered[ $dep ] ) ) {
						$broken_styles[ $handle ] = $dep;
					}
				}
			}
		}

		$total_broken = count( $broken_scripts ) + count( $broken_styles );

		if ( $total_broken > 0 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Number of broken dependencies */
					_n(
						'%d script or style has broken dependencies',
						'%d scripts or styles have broken dependencies',
						$total_broken,
						'wpshadow'
					),
					$total_broken
				),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/broken_dependencies',
				'meta'        => array(
					'broken_scripts' => $broken_scripts,
					'broken_styles'  => $broken_styles,
					'total_broken'   => $total_broken,
				),
			);
		}

		// All dependencies are valid.
		return null;
	}
}
