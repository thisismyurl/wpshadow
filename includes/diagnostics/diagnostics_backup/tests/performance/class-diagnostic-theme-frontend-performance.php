<?php
/**
 * Theme Frontend Performance Diagnostic
 *
 * Checks if the active theme has performance optimization features enabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2309
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Frontend Performance Diagnostic Class
 *
 * Analyzes theme performance characteristics.
 *
 * @since 1.2601.2309
 */
class Diagnostic_Theme_Frontend_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-frontend-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Frontend Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes theme performance optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2309
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if theme supports async/defer scripts
		if ( ! current_theme_supports( 'async-scripts' ) && ! current_theme_supports( 'defer-scripts' ) ) {
			$issues[] = __( 'Theme does not declare async/defer script support', 'wpshadow' );
		}

		// Check if theme has proper critical CSS support
		if ( ! current_theme_supports( 'critical-css' ) ) {
			$issues[] = __( 'Theme may not optimize critical rendering path', 'wpshadow' );
		}

		// Check if theme supports web fonts optimization
		if ( ! current_theme_supports( 'web-fonts-optimization' ) ) {
			$issues[] = __( 'Theme web fonts may not be optimized', 'wpshadow' );
		}

		// Count enqueued scripts and stylesheets
		global $wp_scripts, $wp_styles;
		$script_count = count( $wp_scripts->queue );
		$style_count = count( $wp_styles->queue );

		if ( $script_count > 30 ) {
			$issues[] = sprintf(
				/* translators: %d: number of scripts */
				__( '%d scripts enqueued (consider consolidating)', 'wpshadow' ),
				$script_count
			);
		}

		if ( $style_count > 15 ) {
			$issues[] = sprintf(
				/* translators: %d: number of stylesheets */
				__( '%d stylesheets enqueued (consider consolidating)', 'wpshadow' ),
				$style_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of issues */
					__( 'Found %d theme performance concerns', 'wpshadow' ),
					count( $issues )
				),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/theme-frontend-performance',
			);
		}

		return null;
	}
}
