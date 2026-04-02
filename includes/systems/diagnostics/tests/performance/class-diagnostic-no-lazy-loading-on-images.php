<?php
/**
 * Missing Lazy Loading on Images Diagnostic
 *
 * Detects when images are not using lazy loading,
 * causing slower page load times and poor performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Lazy Loading on Images
 *
 * Checks whether images implement lazy loading attributes
 * or plugins for performance optimization.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Lazy_Loading_On_Images extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-lazy-loading-images';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Lazy Loading on Images';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether images use lazy loading for performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check WordPress version (native lazy loading in 5.5+)
		global $wp_version;
		$supports_native_lazy = version_compare( $wp_version, '5.5', '>=' );

		// Check for lazy loading plugins
		$has_lazy_plugin = is_plugin_active( 'wp-smushit/wp-smush.php' ) ||
			is_plugin_active( 'imagify/imagify.php' ) ||
			is_plugin_active( 'lazy-load-for-videos/lazy-load-for-videos.php' ) ||
			is_plugin_active( 'a3-lazy-load/a3-lazy-load.php' );

		// Check for manual lazy loading
		$homepage = wp_remote_get( home_url() );
		if ( ! is_wp_error( $homepage ) ) {
			$body = wp_remote_retrieve_body( $homepage );
			$has_lazy_attr = strpos( $body, 'loading="lazy"' ) !== false ||
				strpos( $body, 'data-src' ) !== false;
		} else {
			$has_lazy_attr = false;
		}

		if ( ! $supports_native_lazy && ! $has_lazy_plugin && ! $has_lazy_attr ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Your images aren\'t using lazy loading, which means your page downloads all images immediately, even ones below the fold that users might never see. Lazy loading waits to load images until they\'re visible in the viewport—like only turning on lights in rooms you enter. This can make your site load 2-3x faster for most visitors. WordPress has built-in lazy loading since version 5.5.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Page Load Speed',
					'potential_gain' => '2-3x faster page loads',
					'roi_explanation' => 'Lazy loading reduces initial page load time, improving user experience and SEO rankings. Each second of delay loses 7% of conversions.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/lazy-loading-images',
			);
		}

		return null;
	}
}
