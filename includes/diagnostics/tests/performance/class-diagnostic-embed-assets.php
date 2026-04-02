<?php
/**
 * Embed Assets Reviewed Diagnostic (Stub)
 *
 * TODO stub mapped to the performance gauge.
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
 * Diagnostic_Embed_Assets_Reviewed Class
 *
 * TODO: Implement full test logic and remediation guidance.
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
	protected static $description = 'TODO: Implement diagnostic logic for Embed Assets';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check if wp-embed script is enqueued unnecessarily on sites without embeds.
	 *
	 * TODO Fix Plan:
	 * - Disable embed assets where they provide no benefit.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
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
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/embed-assets',
			'details'      => array(
				'note' => __( 'Use Perfmatters, WP Rocket, or a similar plugin to disable oEmbed scripts.', 'wpshadow' ),
			),
		);
	}
}
