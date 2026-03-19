<?php
/**
 * No Lazy Loading for Images Diagnostic
 *
 * Detects when lazy loading is not enabled,
 * causing unnecessary image downloads on page load.
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
 * Diagnostic: No Lazy Loading for Images
 *
 * Checks whether lazy loading is enabled
 * to defer off-screen image loading.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Lazy_Loading_For_Images extends Diagnostic_Base {

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
	protected static $title = 'Lazy Loading for Images';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether lazy loading is enabled';

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
		global $wp_version;

		// WordPress 5.5+ has native lazy loading
		if ( version_compare( $wp_version, '5.5', '<' ) ) {
			// Check for lazy loading plugins
			$has_lazy_loading = is_plugin_active( 'a3-lazy-load/a3-lazy-load.php' ) ||
				is_plugin_active( 'rocket-lazy-load/rocket-lazy-load.php' ) ||
				is_plugin_active( 'wp-rocket/wp-rocket.php' );

			if ( ! $has_lazy_loading ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __(
						'Lazy loading isn\'t enabled, which means all images download immediately even if visitors never scroll to them. Lazy loading waits to load images until they\'re about to appear on screen—like only turning on lights in rooms you enter. For pages with 20 images, this can save 2-3MB of initial load. Native lazy loading (WordPress 5.5+) is free. For older versions, plugins add it easily. Makes especially big difference on mobile.',
						'wpshadow'
					),
					'severity'      => 'medium',
					'threat_level'  => 55,
					'auto_fixable'  => false,
					'business_impact' => array(
						'metric'         => 'Initial Page Load Speed',
						'potential_gain' => '2-3x faster for image-heavy pages',
						'roi_explanation' => 'Lazy loading defers off-screen images, reducing initial load by 2-3MB on image-heavy pages.',
					),
					'kb_link'       => 'https://wpshadow.com/kb/lazy-loading-images',
				);
			}
		}

		return null;
	}
}
