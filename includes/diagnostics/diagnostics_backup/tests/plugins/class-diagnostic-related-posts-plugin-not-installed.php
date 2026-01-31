<?php
/**
 * Related Posts Plugin Not Installed Diagnostic
 *
 * Checks if related posts functionality is available.
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
 * Related Posts Plugin Not Installed Diagnostic Class
 *
 * Detects missing related posts functionality.
 *
 * @since 1.2601.2320
 */
class Diagnostic_Related_Posts_Plugin_Not_Installed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'related-posts-plugin-not-installed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Related Posts Plugin Not Installed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if related posts are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2320
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$related_posts_plugins = array(
			'related-posts-for-wp/related-posts-for-wp.php',
			'wordpress-related-posts/wordpress-related-posts.php',
		);

		$related_active = false;
		foreach ( $related_posts_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$related_active = true;
				break;
			}
		}

		if ( ! $related_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Related posts feature is not enabled. Related posts improve user engagement and reduce bounce rate.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/related-posts-plugin-not-installed',
			);
		}

		return null;
	}
}
