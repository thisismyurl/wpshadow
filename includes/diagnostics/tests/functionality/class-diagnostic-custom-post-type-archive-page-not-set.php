<?php
/**
 * Custom Post Type Archive Page Not Set Diagnostic
 *
 * Checks if custom post type archives are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2320
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Post Type Archive Page Not Set Diagnostic Class
 *
 * Detects missing custom post type archives.
 *
 * @since 1.2601.2320
 */
class Diagnostic_Custom_Post_Type_Archive_Page_Not_Set extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-post-type-archive-page-not-set';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Post Type Archive Page Not Set';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if custom post type archives are set up';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2320
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for registered custom post types
		$post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' );

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type ) {
				if ( ! $post_type->has_archive ) {
					return array(
						'id'            => self::$slug,
						'title'         => self::$title,
						'description'   => sprintf(
							__( 'Custom post type "%s" does not have an archive page. Enable archive support to improve SEO.', 'wpshadow' ),
							$post_type->label
						),
						'severity'      => 'low',
						'threat_level'  => 15,
						'auto_fixable'  => false,
						'kb_link'       => 'https://wpshadow.com/kb/custom-post-type-archive-page-not-set',
					);
				}
			}
		}

		return null;
	}
}
