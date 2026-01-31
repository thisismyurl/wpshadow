<?php
/**
 * Conditional Loading Not Implemented Diagnostic
 *
 * Checks if conditional loading is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2348
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Conditional Loading Not Implemented Diagnostic Class
 *
 * Detects missing conditional loading.
 *
 * @since 1.2601.2348
 */
class Diagnostic_Conditional_Loading_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'conditional-loading-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Conditional Loading Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if conditional loading is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2348
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts, $wp_styles;

		// Check total enqueued scripts and styles
		$total_scripts = count( $wp_scripts->queue );
		$total_styles  = count( $wp_styles->queue );
		$total         = $total_scripts + $total_styles;

		if ( $total > 30 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'You have %d scripts and styles enqueued. Implement conditional loading to load only necessary assets on specific pages.', 'wpshadow' ),
					$total
				),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/conditional-loading-not-implemented',
			);
		}

		return null;
	}
}
