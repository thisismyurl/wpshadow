<?php
/**
 * JavaScript Source Map Exposure Diagnostic
 *
 * Checks source map exposure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_JavaScript_Source_Map_Exposure Class
 *
 * Performs diagnostic check for Javascript Source Map Exposure.
 *
 * @since 1.6093.1200
 */
class Diagnostic_JavaScript_Source_Map_Exposure extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'javascript-source-map-exposure';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'JavaScript Source Map Exposure';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks source map exposure';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'prevent_source_map_exposure' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'JavaScript source map exposed in production. Disable source maps on production or require authentication.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/javascript-source-map-exposure',
			);
		}

		return null;
	}
}
