<?php
/**
 * Theme Customization Documented Diagnostic
 *
 * Tests if custom code is documented and organized.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Customization Documented Diagnostic Class
 *
 * Verifies customizations are documented when a child theme or custom CSS is present.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Documents_Customizations extends Diagnostic_Base {

	protected static $slug        = 'documents-customizations';
	protected static $title       = 'Theme Customization Documented';
	protected static $description = 'Tests if custom code is documented and organized';
	protected static $family      = 'code-quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$has_custom_css  = function_exists( 'wp_get_custom_css' ) ? (bool) wp_get_custom_css() : false;
		$has_child_theme = function_exists( 'is_child_theme' ) ? is_child_theme() : false;

		if ( ! $has_custom_css && ! $has_child_theme ) {
			return null; // No customizations detected
		}

		$manual_flag = get_option( 'wpshadow_theme_customizations_documented' );
		if ( $manual_flag ) {
			return null;
		}

		$keywords = array(
			'theme customization',
			'custom css',
			'theme changes',
			'child theme notes',
		);

		if ( self::has_documented_item( $keywords ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Customizations detected but no documentation found. Document changes so updates are safer and faster.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/theme-customization-documented?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'persona'      => 'developer',
		);
	}

	/**
	 * Check for documentation evidence in posts.
	 *
	 * @since 0.6093.1200
	 * @param  array $keywords Search terms.
	 * @return bool True if found.
	 */
	private static function has_documented_item( array $keywords ) {
		if ( ! function_exists( 'get_posts' ) ) {
			return false;
		}

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post', 'documentation', 'kb' ),
					'post_status'    => array( 'publish', 'private' ),
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				return true;
			}
		}

		return false;
	}
}
