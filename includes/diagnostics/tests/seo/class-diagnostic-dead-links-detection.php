<?php
/**
 * Dead Links Detection Diagnostic
 *
 * Checks if a broken link monitoring tool is configured.
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
 * Diagnostic_Dead_Links_Detection Class
 *
 * Validates that broken link detection is available or configured.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Dead_Links_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dead-links-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Dead Links Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if broken link detection is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$has_checker = is_plugin_active( 'broken-link-checker/broken-link-checker.php' )
			|| is_plugin_active( 'broken-link-checker-pro/broken-link-checker-pro.php' );

		if ( ! $has_checker ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No broken link monitoring detected. Broken links harm SEO and user trust.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dead-links-detection?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'has_checker' => $has_checker,
				),
			);
		}

		return null;
	}
}