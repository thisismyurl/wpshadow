<?php
/**
 * Theme Prefetching and Preloading Diagnostic
 *
 * Checks whether theme uses resource hints for performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Prefetching and Preloading Diagnostic
 *
 * Validates use of prefetch/preconnect/preload hints.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Theme_Prefetching_Preloading extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-prefetching-preloading';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Prefetching and Preloading';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether theme uses resource hints for performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$has_resource_hints = has_filter( 'wp_resource_hints' );

		if ( $has_resource_hints ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Theme does not configure resource hints for performance', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/theme-prefetching-preloading',
			'details'      => array(
				'issues' => array(
					__( 'Add preconnect/preload hints for fonts and critical assets', 'wpshadow' ),
				),
			),
		);
	}
}
