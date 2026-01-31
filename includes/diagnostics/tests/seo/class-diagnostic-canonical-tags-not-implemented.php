<?php
/**
 * Canonical Tags Not Implemented Diagnostic
 *
 * Checks if canonical tags are being used.
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
 * Canonical Tags Not Implemented Diagnostic Class
 *
 * Detects missing canonical tag implementation.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Canonical_Tags_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'canonical-tags-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Canonical Tags Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if canonical tags are set up';

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
		// Check if canonical tag support is hooked
		if ( ! has_filter( 'wp_head', 'rel_canonical' ) ) {
			$seo_plugins = array(
				'wordpress-seo/wp-seo.php',
				'all-in-one-seo-pack/all_in_one_seo_pack.php',
				'rank-math-seo/rank-math.php',
			);

			$seo_plugin_active = false;
			foreach ( $seo_plugins as $plugin ) {
				if ( is_plugin_active( $plugin ) ) {
					$seo_plugin_active = true;
					break;
				}
			}

			if ( ! $seo_plugin_active ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __( 'Canonical tags are not implemented. This can cause duplicate content issues in search engines.', 'wpshadow' ),
					'severity'      => 'high',
					'threat_level'  => 65,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/canonical-tags-not-implemented',
				);
			}
		}

		return null;
	}
}
