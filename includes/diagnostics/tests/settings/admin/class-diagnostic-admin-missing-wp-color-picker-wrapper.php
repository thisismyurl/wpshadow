<?php
/**
 * Admin Missing WP Color Picker Wrapper Diagnostic
 *
 * Detects admin pages where the wp-color-picker script is enqueued without its
 * accompanying stylesheet, which often results in missing picker wrappers and
 * broken UI. Ensures both script and style are registered and enqueued.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Missing WP Color Picker Wrapper Diagnostic Class
 *
 * Checks that wp-color-picker assets are registered and that when the script
 * is enqueued, the style is too. Missing the style typically means the picker
 * wrapper markup is absent or unstyled.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Missing_Wp_Color_Picker_Wrapper extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-missing-wp-color-picker-wrapper';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing WP Color Picker Wrapper';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects color picker script enqueued without its required style';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		global $wp_scripts, $wp_styles;

		$script_enqueued = $wp_scripts && $wp_scripts->is_enqueued( 'wp-color-picker' );
		$style_enqueued  = $wp_styles && $wp_styles->is_enqueued( 'wp-color-picker' );
		$script_reg      = $wp_scripts && $wp_scripts->is_registered( 'wp-color-picker' );
		$style_reg       = $wp_styles && $wp_styles->is_registered( 'wp-color-picker' );

		$issues = array();

		if ( $script_enqueued && ! $style_enqueued ) {
			$issues[] = __( 'wp-color-picker script enqueued but stylesheet is not enqueued.', 'wpshadow' );
		}

		if ( $script_enqueued && ! $style_reg ) {
			$issues[] = __( 'wp-color-picker stylesheet is not registered.', 'wpshadow' );
		}

		if ( $style_enqueued && ! $script_enqueued ) {
			$issues[] = __( 'wp-color-picker stylesheet enqueued without script (unexpected).', 'wpshadow' );
		}

		if ( $script_reg && ! $style_reg ) {
			$issues[] = __( 'wp-color-picker stylesheet is missing; register it alongside the script.', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$items_list = '';
		foreach ( $issues as $issue ) {
			$items_list .= "\n- " . esc_html( $issue );
		}

		return array(
			'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of issues, 2: list */
					__( 'Detected %1$d issue(s) with wp-color-picker assets. Missing the style alongside the script often breaks the color picker wrapper and accessibility hooks.%2$s', 'wpshadow' ),
					count( $issues ),
					$items_list
				),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-missing-wp-color-picker-wrapper',
			'meta'         => array(
				'issues' => $issues,
			),
		);
	}
}
