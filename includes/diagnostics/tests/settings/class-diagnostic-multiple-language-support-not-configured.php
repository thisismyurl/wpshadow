<?php
/**
 * Multiple Language Support Not Configured Diagnostic
 *
 * Checks if multiple language support is configured.
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
 * Multiple Language Support Not Configured Diagnostic Class
 *
 * Detects missing multi-language setup.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Multiple_Language_Support_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multiple-language-support-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multiple Language Support Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if multiple language support is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if translation files exist
		if ( ! is_dir( WP_CONTENT_DIR . '/languages' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Multiple language support is not configured. Use translation plugins like WPML or Polylang to create multi-language sites.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/multiple-language-support-not-configured?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
