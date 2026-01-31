<?php
/**
 * Metabox Registration Not Optimized Diagnostic
 *
 * Checks if metaboxes are registered efficiently.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Metabox Registration Not Optimized Diagnostic Class
 *
 * Detects metabox registration issues.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Metabox_Registration_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'metabox-registration-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Metabox Registration Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if metaboxes are optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_filter;

		// Check for metabox registration hooks
		if ( isset( $wp_filter['add_meta_boxes'] ) ) {
			$count = count( $wp_filter['add_meta_boxes']->callbacks );

			if ( $count > 15 ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => sprintf(
						__( '%d metaboxes are registered. Excessive metaboxes slow down post edit screens.', 'wpshadow' ),
						absint( $count )
					),
					'severity'      => 'low',
					'threat_level'  => 20,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/metabox-registration-not-optimized',
				);
			}
		}

		return null;
	}
}
