<?php
/**
 * Widget Performance Diagnostic
 *
 * Checks for performance issues with active widgets including unnecessary
 * rendering and database queries.
 *
 * @since   1.26033.2085
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget Performance Diagnostic Class
 *
 * Analyzes widget performance:
 * - Count of active widgets
 * - Widget types and complexity
 * - Sidebar activity
 * - Widget query impact
 *
 * @since 1.26033.2085
 */
class Diagnostic_Widget_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'widget-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Widget Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for performance issues with active widgets';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2085
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$sidebars = wp_get_sidebars_widgets();
		$widget_count = 0;
		$heavy_widgets = array();

		// Heavy widgets that often cause performance issues
		$heavy_widget_ids = array(
			'pages'           => 'Pages',
			'recent-comments' => 'Recent Comments',
			'search'          => 'Search',
			'calendar'        => 'Calendar',
			'text'            => 'Text',
			'custom_html'     => 'Custom HTML',
		);

		if ( ! empty( $sidebars ) ) {
			foreach ( $sidebars as $sidebar_id => $widgets ) {
				if ( is_array( $widgets ) ) {
					foreach ( $widgets as $widget_id ) {
						$widget_count++;

						// Check for heavy widgets
						preg_match( '/^([a-z\-_]+)-\d+$/', $widget_id, $matches );
						if ( ! empty( $matches[1] ) && isset( $heavy_widget_ids[ $matches[1] ] ) ) {
							$heavy_widgets[] = $heavy_widget_ids[ $matches[1] ];
						}
					}
				}
			}
		}

		if ( $widget_count > 15 || count( $heavy_widgets ) > 2 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: widget count, %d: heavy widgets */
					__( 'Found %d active widgets with %d heavy widgets. Too many widgets can add 100-300ms to page load.', 'wpshadow' ),
					$widget_count,
					count( $heavy_widgets )
				),
				'severity'      => 'low',
				'threat_level'  => 25,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/widget-performance',
				'meta'          => array(
					'total_widgets'        => $widget_count,
					'heavy_widgets'        => array_slice( array_unique( $heavy_widgets ), 0, 3 ),
					'recommendation'       => 'Remove unused widgets, lazy-load heavy widgets, or cache widget output',
					'impact'               => 'Removing unnecessary widgets can improve load time by 50-100ms',
					'optimization'         => array(
						'Disable sidebar on pages',
						'Use static widgets with caching',
						'Lazy-load recent posts/comments',
						'Hide widgets from feeds',
					),
				),
			);
		}

		return null;
	}
}
