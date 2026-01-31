<?php
/**
 * Post Type Capabilities Not Customized Diagnostic
 *
 * Checks if custom post type capabilities are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2346
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Type Capabilities Not Customized Diagnostic Class
 *
 * Detects missing custom post type capabilities.
 *
 * @since 1.2601.2346
 */
class Diagnostic_Post_Type_Capabilities_Not_Customized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-type-capabilities-not-customized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Type Capabilities Not Customized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if custom post type capabilities are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2346
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_post_types;

		// Check for custom post types without capabilities map
		$custom_post_types = array_filter(
			$wp_post_types,
			function( $post_type ) {
				return ! in_array( $post_type->name, array( 'post', 'page', 'attachment' ), true );
			}
		);

		if ( count( $custom_post_types ) > 0 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'You have %d custom post types. Ensure they have custom capabilities mapped for proper permission handling.', 'wpshadow' ),
					count( $custom_post_types )
				),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/post-type-capabilities-not-customized',
			);
		}

		return null;
	}
}
