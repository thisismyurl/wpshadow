<?php
/**
 * Lazy Loading Attribute Not Used Diagnostic
 *
 * Checks if lazy loading attribute is used.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lazy Loading Attribute Not Used Diagnostic Class
 *
 * Detects missing lazy loading attribute usage.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Lazy_Loading_Attribute_Not_Used extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'lazy-loading-attribute-not-used';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Lazy Loading Attribute Not Used';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if lazy loading attribute is used';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for native lazy loading implementation
		if ( ! has_filter( 'img_tag_output', 'add_lazy_loading_attribute' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Lazy loading attribute is not used. Add loading="lazy" to images below the fold to defer loading and improve page speed and Core Web Vitals.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 50,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/lazy-loading-attribute-not-used',
			);
		}

		return null;
	}
}
