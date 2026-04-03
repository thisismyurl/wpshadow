<?php
/**
 * Embed Assets Diagnostic
 *
 * Checks whether WordPress oEmbed scripts are loaded on every front-end
 * page unnecessarily, adding HTTP requests for sites that don't publish
 * embeddable content.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Embed_Assets Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Embed_Assets extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'embed-assets';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Embed Assets';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether WordPress oEmbed scripts are loaded on every front-end page. These scripts are unnecessary overhead on sites that do not publish embeddable content.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

/**
 * Confidence level of this diagnostic.
 *
 * @var string
 */
protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Detects plugins or settings that already disable oEmbed scripts. If none
	 * are found, checks whether the wp-embed script handle is registered.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when embed assets are still loaded, null when healthy.
	 */
	public static function check() {
		// Check for plugins or configurations known to disable oEmbed/embed scripts.
		$pm = get_option( 'perfmatters_options', array() );
		if ( is_array( $pm ) && ! empty( $pm['extras']['disable_embeds'] ) ) {
			return null;
		}

		$rocket = get_option( 'wp_rocket_settings', array() );
		if ( is_array( $rocket ) && ! empty( $rocket['embeds'] ) ) {
			return null;
		}

		// Autoptimize
		$ao = get_option( 'autoptimize_extra_settings', array() );
		if ( is_array( $ao ) && ! empty( $ao['autoptimize_extra_disable_wp_embeds'] ) ) {
			return null;
		}

		// WP Asset CleanUp (any configuration is enough to proceed)
		if ( false !== get_option( 'wpacu_settings', false ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'WordPress loads oEmbed scripts (wp-embed.js) on every front-end page to allow your content to be embedded on other sites. If you do not publish embeddable content or use third-party oEmbed cards, these scripts are unnecessary overhead. A performance plugin can disable them with a single setting.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 10,
			'kb_link'      => 'https://wpshadow.com/kb/embed-assets?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'note' => __( 'Use Perfmatters, WP Rocket, or a similar plugin to disable oEmbed scripts.', 'wpshadow' ),
			),
		);
	}
}
