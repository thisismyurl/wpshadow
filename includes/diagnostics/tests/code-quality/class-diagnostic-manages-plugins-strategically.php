<?php
/**
 * Plugin Management Strategy Diagnostic
 *
 * Tests if unnecessary plugins are regularly audited and removed.
 *
 * @since   1.6050.0000
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Management Strategy Diagnostic Class
 *
 * Verifies that plugin audits or inventory practices exist.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Manages_Plugins_Strategically extends Diagnostic_Base {

	protected static $slug = 'manages-plugins-strategically';
	protected static $title = 'Plugin Management Strategy';
	protected static $description = 'Tests if unnecessary plugins are regularly audited and removed';
	protected static $family = 'code-quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$manual_flag = get_option( 'wpshadow_plugin_management_strategy' );
		if ( $manual_flag ) {
			return null;
		}

		$keywords = array(
			'plugin audit',
			'plugin inventory',
			'plugin review',
			'plugin management',
		);

		if ( self::has_documented_item( $keywords ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No plugin management strategy found. Audit plugins regularly to reduce risk and improve performance.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/plugin-management-strategy',
			'persona'      => 'developer',
		);
	}

	/**
	 * Check for documentation evidence in posts.
	 *
	 * @since  1.6050.0000
	 * @param  array $keywords Search terms.
	 * @return bool True if found.
	 */
	private static function has_documented_item( array $keywords ) {
		if ( ! function_exists( 'get_posts' ) ) {
			return false;
		}

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post', 'documentation', 'kb' ),
					'post_status'    => array( 'publish', 'private' ),
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				return true;
			}
		}

		return false;
	}
}
