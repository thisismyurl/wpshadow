<?php
/**
 * Social Media Integration Not Configured Diagnostic
 *
 * Checks if social media integration is set up.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2346
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social Media Integration Not Configured Diagnostic Class
 *
 * Detects missing social media integration.
 *
 * @since 1.2601.2346
 */
class Diagnostic_Social_Media_Integration_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-media-integration-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Social Media Integration Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if social media sharing is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2346
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for social media sharing plugins
		$social_plugins = array(
			'jetpack/jetpack.php',
			'social-media-feather/social-media-feather.php',
			'social-networks-auto-poster-facebook-twitter-g/nextscripts-socialnetworkpostr.php',
		);

		$social_active = false;
		foreach ( $social_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$social_active = true;
				break;
			}
		}

		if ( ! $social_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Social media integration is not configured. Add social sharing buttons to increase engagement and reach.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/social-media-integration-not-configured',
			);
		}

		return null;
	}
}
