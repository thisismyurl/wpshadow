<?php
/**
 * Content Recommendation Engine Not Implemented Diagnostic
 *
 * Checks if content recommendations are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2340
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Recommendation Engine Not Implemented Diagnostic Class
 *
 * Detects missing content recommendations.
 *
 * @since 1.2601.2340
 */
class Diagnostic_Content_Recommendation_Engine_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-recommendation-engine-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Recommendation Engine Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if content recommendations are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$recommendation_plugins = array(
			'related-posts-for-wp/related-posts-for-wp.php',
			'jetpack/jetpack.php',
		);

		$recommendation_active = false;
		foreach ( $recommendation_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$recommendation_active = true;
				break;
			}
		}

		if ( ! $recommendation_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Content recommendations are not configured. Enable related posts or recommendations to increase page views and engagement.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/content-recommendation-engine-not-implemented',
			);
		}

		return null;
	}
}
