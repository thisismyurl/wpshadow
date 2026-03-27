<?php
/**
 * Lost Shortcodes and Formatting After Import Treatment
 *
 * Tests whether shortcodes and formatting survive import.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lost Shortcodes and Formatting After Import Treatment Class
 *
 * Tests whether page builder shortcodes, Gutenberg blocks, and formatting survive import.
 *
 * @since 1.6093.1200
 */
class Treatment_Lost_Shortcodes_And_Formatting_After_Import extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'lost-shortcodes-and-formatting-after-import';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Lost Shortcodes and Formatting After Import';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether shortcodes and formatting survive import';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Lost_Shortcodes_And_Formatting_After_Import' );
	}
}
