<?php
/**
 * Admin Uncategorized Menu Items Missing Grouping Markup Diagnostic
 *
 * Detects long runs of admin menu items without grouping separators. Lack of grouping
 * makes navigation harder for users and indicates plugins are injecting menu items
 * without using WordPress separator patterns.
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
 * Admin Uncategorized Menu Items Missing Grouping Markup Diagnostic Class
 *
 * Checks the admin menu structure for missing grouping separators when there are
 * long consecutive menu items. Groups improve scanability and accessibility.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Uncategorized_Admin_Menu_Items_Missing_Grouping_Markup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-uncategorized-admin-menu-items-missing-grouping-markup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Uncategorized Menu Items Missing Grouping Markup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects admin menus with long runs of items lacking grouping separators';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Threshold of consecutive items without a separator before warning.
	 *
	 * @var int
	 */
	const RUN_THRESHOLD = 8;

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

		global $menu;

		if ( empty( $menu ) || ! is_array( $menu ) ) {
			return null;
		}

		$runs           = array();
		$current_run    = array();
		$current_length = 0;

		foreach ( $menu as $menu_item ) {
			if ( empty( $menu_item ) || ! is_array( $menu_item ) ) {
				continue;
			}

			$slug  = isset( $menu_item[2] ) ? $menu_item[2] : '';
			$title = isset( $menu_item[0] ) ? wp_strip_all_tags( $menu_item[0] ) : '';

			// Reset run at separators.
			if ( strpos( $slug, 'separator' ) !== false ) {
				if ( $current_length >= self::RUN_THRESHOLD ) {
					$runs[] = $current_run;
				}

				$current_run    = array();
				$current_length = 0;
				continue;
			}

			// Skip empty titles.
			if ( empty( $title ) ) {
				continue;
			}

			$current_length++;
			$current_run[] = array(
				'title' => $title,
				'slug'  => $slug,
			);
		}

		// Capture trailing run.
		if ( $current_length >= self::RUN_THRESHOLD ) {
			$runs[] = $current_run;
		}

		if ( empty( $runs ) ) {
			return null; // Grouping is present or runs are short.
		}

		// Build description.
		$items_list = '';
		$max_runs   = 3;
		$shown      = array_slice( $runs, 0, $max_runs );

		foreach ( $shown as $run ) {
			$items_list .= "\n" . __( 'Run:', 'wpshadow' );
			$subset = array_slice( $run, 0, 5 );
			foreach ( $subset as $item ) {
				$items_list .= sprintf(
					"\n- %s (slug: %s)",
					esc_html( $item['title'] ),
					esc_html( $item['slug'] )
				);
			}

			if ( count( $run ) > 5 ) {
				$items_list .= sprintf(
					/* translators: %d: number of additional items */
					__( "\n...and %d more in this group", 'wpshadow' ),
					count( $run ) - 5
				);
			}
		}

		return array(
			'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of long runs, 2: list of items */
					__( 'Detected %1$d long run(s) of admin menu items without grouping separators. Adding separators improves scanability, accessibility, and aligns with WordPress admin patterns.%2$s', 'wpshadow' ),
					count( $runs ),
					$items_list
				),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-uncategorized-admin-menu-items-missing-grouping-markup',
			'meta'         => array(
				'runs' => $runs,
			),
		);
	}
}
