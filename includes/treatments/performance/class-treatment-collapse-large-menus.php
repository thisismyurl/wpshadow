<?php
/**
 * Treatment: Collapse Large Menus
 *
 * Collapses admin menus by default to reduce rendering overhead.
 *
 * Philosophy: Inspire Confidence (#8) - Clean interface
 * KB Link: https://wpshadow.com/kb/large-menu-overhead
 * Training: https://wpshadow.com/training/large-menu-overhead
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Collapse Large Menus treatment
 */
class Treatment_Collapse_Large_Menus extends Treatment_Base {

	/**
	 * Apply the treatment
	 *
	 * @param array $options Treatment options
	 * @return bool Success status
	 */
	public static function apply( array $options = [] ): bool {
		// Store setting
		update_option( 'wpshadow_collapse_admin_menu', true );

		// Add admin body class for CSS targeting
		add_filter( 'admin_body_class', [ __CLASS__, 'add_collapsed_menu_class' ] );

		// Add CSS to collapse menu
		add_action( 'admin_head', [ __CLASS__, 'add_collapsed_menu_css' ] );

		// Add JS to improve collapsed menu UX
		add_action( 'admin_footer', [ __CLASS__, 'add_collapsed_menu_js' ] );

		// Track KPI
		KPI_Tracker::record_treatment_applied( __CLASS__, 1 );

		return true;
	}

	/**
	 * Add collapsed menu body class
	 *
	 * @param string $classes Current classes
	 * @return string Modified classes
	 */
	public static function add_collapsed_menu_class( string $classes ): string {
		if ( get_option( 'wpshadow_collapse_admin_menu', false ) ) {
			$classes .= ' wpshadow-collapsed-menu';
		}
		return $classes;
	}

	/**
	 * Add collapsed menu CSS
	 */
	public static function add_collapsed_menu_css(): void {
		if ( ! get_option( 'wpshadow_collapse_admin_menu', false ) ) {
			return;
		}
		?>
		<style>
		/* Collapse submenus by default */
		.wpshadow-collapsed-menu #adminmenu .wp-submenu {
			display: none;
		}
		
		/* Show submenu when parent is hovered/focused */
		.wpshadow-collapsed-menu #adminmenu li.opensub .wp-submenu,
		.wpshadow-collapsed-menu #adminmenu li:hover .wp-submenu,
		.wpshadow-collapsed-menu #adminmenu li.wp-has-current-submenu .wp-submenu {
			display: block;
		}
		
		/* Reduce submenu item height */
		.wpshadow-collapsed-menu #adminmenu .wp-submenu li a {
			padding: 6px 12px;
			font-size: 12px;
		}
		
		/* Add visual indicator for collapsed submenus */
		.wpshadow-collapsed-menu #adminmenu .wp-has-submenu > a:after {
			content: '▶';
			float: right;
			font-size: 10px;
			opacity: 0.5;
		}
		
		.wpshadow-collapsed-menu #adminmenu li.opensub > a:after,
		.wpshadow-collapsed-menu #adminmenu li:hover > a:after {
			content: '▼';
		}
		</style>
		<?php
	}

	/**
	 * Add collapsed menu JS
	 */
	public static function add_collapsed_menu_js(): void {
		if ( ! get_option( 'wpshadow_collapse_admin_menu', false ) ) {
			return;
		}
		?>
		<script>
		jQuery(function($) {
			// Click to toggle submenu
			$('#adminmenu .wp-has-submenu > a').on('click', function(e) {
				var $li = $(this).parent();
				
				// Don't interfere with normal navigation
				if ($(e.target).is('a')) {
					return;
				}
				
				// Toggle opensub class
				$('#adminmenu li.opensub').not($li).removeClass('opensub');
				$li.toggleClass('opensub');
			});
		});
		</script>
		<?php
	}

	/**
	 * Undo the treatment
	 *
	 * @return bool Success status
	 */
	public static function undo(): bool {
		delete_option( 'wpshadow_collapse_admin_menu' );
		remove_filter( 'admin_body_class', [ __CLASS__, 'add_collapsed_menu_class' ] );
		remove_action( 'admin_head', [ __CLASS__, 'add_collapsed_menu_css' ] );
		remove_action( 'admin_footer', [ __CLASS__, 'add_collapsed_menu_js' ] );
		return true;
	}

	/**
	 * Get display name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'Collapse Large Menus', 'wpshadow' );
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return sprintf(
			__( 'Collapses admin submenus by default to reduce menu rendering overhead and improve navigation. Submenus expand on hover or click. <a href="%s" target="_blank">Learn about menu optimization</a>', 'wpshadow' ),
			'https://wpshadow.com/kb/large-menu-overhead'
		);
	}
}
