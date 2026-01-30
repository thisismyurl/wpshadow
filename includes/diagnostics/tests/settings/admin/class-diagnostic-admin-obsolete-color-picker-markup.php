<?php
/**
 * Admin Obsolete Color Picker Markup Diagnostic
 *
 * Detects use of obsolete color picker assets (e.g., farbtastic) instead of the
 * current wp-color-picker/iris components recommended in modern WordPress admin UI.
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
 * Admin Obsolete Color Picker Markup Diagnostic Class
 *
 * Flags admin pages loading legacy color picker assets, which usually correlate
 * with outdated markup that lacks accessibility and visual consistency.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Obsolete_Color_Picker_Markup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-obsolete-color-picker-markup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Obsolete Color Picker Markup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects legacy color picker assets (farbtastic) instead of wp-color-picker/iris';

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

		$legacy_handles = array();

		if ( $wp_scripts && $wp_scripts->is_enqueued( 'farbtastic' ) ) {
			$legacy_handles[] = 'farbtastic (script)';
		}

		if ( $wp_styles && $wp_styles->is_enqueued( 'farbtastic' ) ) {
			$legacy_handles[] = 'farbtastic (style)';
		}

		// Also flag if iris/wp-color-picker are missing but farbtastic present.
		$iris_missing  = ! ( $wp_scripts && $wp_scripts->is_registered( 'iris' ) );
		$picker_missing = ! ( $wp_scripts && $wp_scripts->is_registered( 'wp-color-picker' ) );

		if ( $iris_missing && ( $wp_scripts && $wp_scripts->is_registered( 'farbtastic' ) ) ) {
			$legacy_handles[] = __( 'Iris (modern color picker) is missing while farbtastic is present.', 'wpshadow' );
		}

		if ( empty( $legacy_handles ) ) {
			return null;
		}

		$items_list = '';
		foreach ( $legacy_handles as $handle ) {
			$items_list .= "\n- " . esc_html( $handle );
		}

		return array(
			'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: count, 2: list */
					__( 'Detected legacy color picker assets (%1$d). Outdated farbtastic markup can break accessibility and design alignment. Migrate to wp-color-picker/iris components.%2$s', 'wpshadow' ),
					count( $legacy_handles ),
					$items_list
				),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-obsolete-color-picker-markup',
			'meta'         => array(
				'legacy_handles' => $legacy_handles,
			),
		);
	}
}
