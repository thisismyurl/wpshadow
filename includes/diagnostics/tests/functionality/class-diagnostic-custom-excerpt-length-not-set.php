<?php
/**
 * Custom Excerpt Length Not Set Diagnostic
 *
 * Checks if excerpt length is customized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2330
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Excerpt Length Not Set Diagnostic Class
 *
 * Detects default excerpt length.
 *
 * @since 1.2601.2330
 */
class Diagnostic_Custom_Excerpt_Length_Not_Set extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-excerpt-length-not-set';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Excerpt Length Not Set';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if excerpt length is customized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2330
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if there's a custom excerpt filter
		if ( ! has_filter( 'excerpt_length' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Excerpt length is using WordPress defaults (55 words). Consider customizing for better preview text.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/custom-excerpt-length-not-set',
			);
		}

		return null;
	}
}
