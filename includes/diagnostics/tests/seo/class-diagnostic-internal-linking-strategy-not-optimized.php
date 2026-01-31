<?php
/**
 * Internal Linking Strategy Not Optimized Diagnostic
 *
 * Checks if internal linking is optimized.
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
 * Internal Linking Strategy Not Optimized Diagnostic Class
 *
 * Detects poor internal linking patterns.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Internal_Linking_Strategy_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'internal-linking-strategy-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Internal Linking Strategy Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if internal linking is optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check for "related posts" plugin
		$related_plugins = array(
			'related-posts-for-wp/index.php',
			'wordpress-popular-posts/wordpress-popular-posts.php',
			'related-posts/plugin.php',
		);

		$has_linking_plugin = false;
		foreach ( $related_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_linking_plugin = true;
				break;
			}
		}

		// Count posts with no links to other posts
		$orphaned_posts = $wpdb->get_var(
			"SELECT COUNT(p.ID) FROM {$wpdb->posts} p 
			WHERE p.post_type = 'post' AND p.post_status = 'publish' 
			AND p.post_content NOT LIKE '%<a href=\"%wp-content%\">%' 
			AND p.post_content NOT LIKE '%<a href=\"%/blog/%\">%'"
		);

		if ( $orphaned_posts > 50 && ! $has_linking_plugin ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d posts have minimal internal links. This reduces SEO benefit and user engagement. Consider adding related posts or internal linking strategy.', 'wpshadow' ),
					absint( $orphaned_posts )
				),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/internal-linking-strategy-not-optimized',
			);
		}

		return null;
	}
}
