<?php
/**
 * Accessibility Link Labels Missing Diagnostic
 *
 * Checks if links have proper accessible labels.
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
 * Accessibility Link Labels Missing Diagnostic Class
 *
 * Detects missing link accessibility labels.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Accessibility_Link_Labels_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'accessibility-link-labels-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Accessibility Link Labels Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if links have accessible labels';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for accessibility plugins
		$accessibility_plugins = array(
			'accessibility-checker/accessibility-checker.php',
			'wp-accessibility/wp-accessibility.php',
		);

		$accessibility_active = false;
		foreach ( $accessibility_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$accessibility_active = true;
				break;
			}
		}

		if ( ! $accessibility_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No accessibility checker is active. Screen readers need proper link labels to describe link purposes to users.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/accessibility-link-labels-missing',
			);
		}

		return null;
	}
}
