<?php
/**
 * Plugin Admin Page Performance Diagnostic
 *
 * Analyzes admin page load performance and identifies slow-loading plugin admin pages.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Admin Page Performance Diagnostic Class
 *
 * Detects admin pages with excessive load times, large DOM trees, or heavy resource usage.
 *
 * @since 1.2601.2200
 */
class Diagnostic_Plugin_Admin_Page_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-admin-page-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Admin Page Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes admin page load performance and identifies slow-loading plugin admin pages';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $menu, $submenu;

		$slow_pages = array();
		$issues     = array();

		// Check for known slow admin pages.
		$slow_patterns = array(
			'woocommerce'       => array( 'threshold' => 2.0, 'name' => 'WooCommerce' ),
			'elementor'         => array( 'threshold' => 2.5, 'name' => 'Elementor' ),
			'wp-rocket'         => array( 'threshold' => 1.5, 'name' => 'WP Rocket' ),
			'yoast'             => array( 'threshold' => 1.5, 'name' => 'Yoast SEO' ),
			'jetpack'           => array( 'threshold' => 2.0, 'name' => 'Jetpack' ),
			'wordfence'         => array( 'threshold' => 2.0, 'name' => 'Wordfence' ),
			'wpforms'           => array( 'threshold' => 1.5, 'name' => 'WPForms' ),
			'gravityforms'      => array( 'threshold' => 1.8, 'name' => 'Gravity Forms' ),
			'contact-form-7'    => array( 'threshold' => 1.2, 'name' => 'Contact Form 7' ),
			'wp-all-import'     => array( 'threshold' => 2.5, 'name' => 'WP All Import' ),
			'advanced-custom-fields' => array( 'threshold' => 1.5, 'name' => 'Advanced Custom Fields' ),
		);

		// Check active plugins for known slow admin pages.
		$active_plugins = get_option( 'active_plugins', array() );
		foreach ( $active_plugins as $plugin ) {
			foreach ( $slow_patterns as $pattern => $config ) {
				if ( strpos( $plugin, $pattern ) !== false ) {
					$slow_pages[] = $config['name'];
				}
			}
		}

		// Check menu structure complexity.
		$menu_count    = is_array( $menu ) ? count( $menu ) : 0;
		$submenu_total = 0;
		if ( is_array( $submenu ) ) {
			foreach ( $submenu as $items ) {
				$submenu_total += count( $items );
			}
		}

		if ( $menu_count > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of menu items */
				__( 'Large admin menu (%d top-level items) may impact navigation performance', 'wpshadow' ),
				$menu_count
			);
		}

		if ( $submenu_total > 200 ) {
			$issues[] = sprintf(
				/* translators: %d: number of submenu items */
				__( 'Excessive submenu items (%d total) can slow admin rendering', 'wpshadow' ),
				$submenu_total
			);
		}

		// Check admin notices (often performance killers).
		$admin_notices_count = did_action( 'admin_notices' );
		if ( $admin_notices_count > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of admin notices */
				__( 'High number of admin notices (%d) can degrade page performance', 'wpshadow' ),
				$admin_notices_count
			);
		}

		// Check for heavy admin page indicators.
		if ( ! empty( $slow_pages ) || ! empty( $issues ) ) {
			$severity     = 'medium';
			$threat_level = 45;

			if ( count( $slow_pages ) > 3 || count( $issues ) > 2 ) {
				$severity     = 'high';
				$threat_level = 70;
			}

			$description = __( 'Slow-loading admin pages detected that may impact user experience', 'wpshadow' );

			$details = array();
			if ( ! empty( $slow_pages ) ) {
				$details['slow_plugins'] = $slow_pages;
				$details['count']        = count( $slow_pages );
			}
			if ( ! empty( $issues ) ) {
				$details['performance_issues'] = $issues;
			}
			$details['menu_items']    = $menu_count;
			$details['submenu_items'] = $submenu_total;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugin-admin-page-performance',
				'details'      => $details,
			);
		}

		return null;
	}
}
