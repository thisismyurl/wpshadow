<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Admin Font Bloat
 * Philosophy: Show value (#9) and educate (#5) by surfacing heavy font usage in wp-admin.
 *
 * @package WPShadow
 */
class Diagnostic_Admin_Font_Bloat extends Diagnostic_Base {
	/**
	 * Detect excessive font loading in wp-admin.
	 *
	 * @return array|null Finding data or null if healthy.
	 */
	public static function check(): ?array {
		if ( ! is_admin() ) {
			return null;
		}

		global $wp_styles;
		if ( ! isset( $wp_styles ) || empty( $wp_styles->queue ) ) {
			return null;
		}

		$font_handles     = array();
		$font_face_blocks = 0;

		foreach ( $wp_styles->queue as $handle ) {
			if ( ! isset( $wp_styles->registered[ $handle ] ) ) {
				continue;
			}

			$style = $wp_styles->registered[ $handle ];
			$src   = is_string( $style->src ) ? $style->src : '';

			if ( self::is_font_src( $src ) ) {
				$font_handles[] = $handle;
			}

			$after = $wp_styles->get_data( $handle, 'after' );
			if ( is_array( $after ) ) {
				foreach ( $after as $css ) {
					$font_face_blocks += substr_count( (string) $css, '@font-face' );
				}
			}
		}

		$font_handles   = array_values( array_unique( $font_handles ) );
		$font_handle_ct = count( $font_handles );

		// Allow a small number of font loads for icons/core before flagging.
		if ( $font_handle_ct <= 3 && $font_face_blocks <= 6 ) {
			return null;
		}

		$handle_list = implode( ', ', array_slice( $font_handles, 0, 5 ) );
		if ( $font_handle_ct > 5 ) {
			$handle_list .= ', ...';
		}

		return array(
			'id'           => 'admin-font-bloat',
			'title'        => sprintf( __( 'Admin Loading %d Font Sources', 'wpshadow' ), $font_handle_ct ),
			'description'  => sprintf(
				__( 'wp-admin is loading %1$d @font-face blocks across %2$d styles (%3$s). Limit admin fonts to a single system stack to speed up dashboards.', 'wpshadow' ),
				$font_face_blocks,
				$font_handle_ct,
				$handle_list ?: __( 'unknown sources', 'wpshadow' )
			),
			'severity'     => 'medium',
			'category'     => 'performance',
			'kb_link'      => 'https://wpshadow.com/kb/admin-font-bloat',
			'training_link'=> 'https://wpshadow.com/training/admin-performance',
			'auto_fixable' => false,
			'threat_level' => 45,
		);
	}

	/**
	 * Determine whether a stylesheet src points to fonts.
	 *
	 * @param string $src Source URL or path.
	 *
	 * @return bool
	 */
	private static function is_font_src( string $src ): bool {
		if ( '' === $src ) {
			return false;
		}

		$src = strtolower( $src );

		if ( false !== strpos( $src, 'fonts.googleapis' ) || false !== strpos( $src, 'fonts.gstatic' ) ) {
			return true;
		}

		if ( false !== strpos( $src, '/fonts/' ) || false !== strpos( $src, 'fontawesome' ) ) {
			return true;
		}

		return (bool) preg_match( '/\.(woff2?|ttf|otf|eot)(\?|$)/', $src );
	}
}
