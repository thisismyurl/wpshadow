<?php
/**
 * Portfolio Image Copyright Protection Diagnostic
 *
 * Verifies portfolio sites protect creative work from unauthorized use
 *
 * @package    WPShadow
 * @subpackage Diagnostics\\Portfolio
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Portfolio;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_ImageCopyrightProtection Class
 *
 * Checks for: watermark plugins, right-click protection, HTTPS
 *
 * @since 1.6031.1445
 */
class Diagnostic_ImageCopyrightProtection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-copyright-protection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Portfolio Image Copyright Protection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies portfolio sites protect creative work from unauthorized use';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'portfolio';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for relevant plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$plugin_keywords = array( 'watermark', 'protect', 'copyright', 'image-protection' );
		$has_plugin = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $plugin_keywords as $keyword ) {
				if ( stripos( $plugin, $keyword ) !== false ) {
					$has_plugin = true;
					break 2;
				}
			}
		}

		if ( ! $has_plugin ) {
			$issues[] = __( 'No relevant plugin detected', 'wpshadow' );
		}

		// Additional checks would go here for: No right-click protection found

		// Additional checks would go here for: Site not using HTTPS

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Copyright protection concerns: %s. Portfolio sites should protect creative work.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/image-copyright-protection',
		);
	}
}
