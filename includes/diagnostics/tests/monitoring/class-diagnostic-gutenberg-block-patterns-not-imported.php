<?php
/**
 * Gutenberg Block Patterns Not Imported Diagnostic
 *
 * Checks if block patterns are imported.
 *
 * @package    WPShadow
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
 * Gutenberg Block Patterns Not Imported Diagnostic Class
 *
 * Detects missing block patterns.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Gutenberg_Block_Patterns_Not_Imported extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gutenberg-block-patterns-not-imported';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Gutenberg Block Patterns Not Imported';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if block patterns are imported';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for block patterns
		if ( ! has_action( 'init', 'register_block_patterns' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Gutenberg block patterns are not imported. Register custom block patterns to speed up content creation with pre-designed layouts.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/gutenberg-block-patterns-not-imported?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
