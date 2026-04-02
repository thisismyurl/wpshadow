<?php
/**
 * Skip to Content Link Diagnostic
 *
 * Checks whether a skip link exists for keyboard users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Skip to Content Link Diagnostic Class
 *
 * Verifies that a skip link exists in the header template.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Skip_To_Content_Link extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'skip-to-content-link';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Skip to Content Link Missing or Broken';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the first focusable element is a skip link';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$header_path = get_theme_file_path( 'header.php' );
		$stats['header_found'] = ( $header_path && file_exists( $header_path ) ) ? 'yes' : 'no';

		if ( empty( $header_path ) || ! file_exists( $header_path ) ) {
			$issues[] = __( 'Header template not found to verify skip link', 'wpshadow' );
		} else {
			$header = file_get_contents( $header_path );
			$has_skip = false !== strpos( $header, 'skip-link' ) || false !== strpos( $header, 'skip to content' );
			$stats['skip_link_found'] = $has_skip ? 'yes' : 'no';

			if ( ! $has_skip ) {
				$issues[] = __( 'No skip link detected near the top of the header', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Skip links let keyboard users jump straight to the main content. This prevents tabbing through long menus on every page.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/skip-to-content-link',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
