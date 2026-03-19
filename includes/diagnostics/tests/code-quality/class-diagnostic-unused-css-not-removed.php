<?php
/**
 * Unused CSS Not Removed Diagnostic
 *
 * Checks unused CSS removal.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Unused_CSS_Not_Removed Class
 *
 * Performs diagnostic check for Unused Css Not Removed.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Unused_CSS_Not_Removed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'unused-css-not-removed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Unused CSS Not Removed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks unused CSS removal';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'wp_head', 'remove_unused_css' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unused CSS removal is not configured yet. Trimming unused styles can reduce payload size and improve page speed.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/unused-css-not-removed',
			);
		}

		return null;
	}
}
