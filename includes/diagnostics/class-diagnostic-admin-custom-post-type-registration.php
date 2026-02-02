<?php
/**
 * Admin Custom Post Type Registration
 *
 * Checks if custom post types are properly registered and documented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0638
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin Custom Post Type Registration
 *
 * @since 1.26033.0638
 */
class Diagnostic_Admin_Custom_Post_Type_Registration extends Diagnostic_Base {

	protected static $slug = 'admin-custom-post-type-registration';
	protected static $title = 'Admin Custom Post Type Registration';
	protected static $description = 'Verifies custom post types are properly registered';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Get all custom post types
		$post_types = get_post_types( array( '_builtin' => false ), 'objects' );
		$public_count = 0;

		foreach ( $post_types as $post_type ) {
			// Check if post type is publicly accessible without proper capability
			if ( $post_type->public && empty( $post_type->capabilities ) ) {
				$public_count++;
			}
		}

		if ( $public_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of post types */
				__( '%d custom post type(s) are public but lack proper capabilities defined', 'wpshadow' ),
				$public_count
			);
		}

		// Check custom post type count
		$total_cpt = count( $post_types );
		if ( $total_cpt > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of post types */
				__( 'High number of custom post types (%d) registered', 'wpshadow' ),
				$total_cpt
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-custom-post-type-registration',
			);
		}

		return null;
	}
}
