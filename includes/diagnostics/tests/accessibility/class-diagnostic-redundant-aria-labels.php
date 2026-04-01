<?php
/**
 * Redundant ARIA Labels Diagnostic
 *
 * Checks for redundant ARIA roles on native elements.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redundant ARIA Labels Diagnostic Class
 *
 * Verifies that native elements are not given redundant ARIA roles.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Redundant_ARIA_Labels extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'redundant-aria-labels';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Redundant ARIA Labels on Native Elements';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks for redundant ARIA roles on native elements';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$templates = array(
			get_theme_file_path( 'header.php' ),
			get_theme_file_path( 'footer.php' ),
			get_theme_file_path( 'template-parts/content.php' ),
		);

		$redundant_count = 0;
		foreach ( $templates as $template_path ) {
			if ( empty( $template_path ) || ! file_exists( $template_path ) ) {
				continue;
			}

			$content = file_get_contents( $template_path );
			if ( preg_match( '/<button[^>]*role=\"button\"/i', $content ) ) {
				$redundant_count++;
			}
			if ( preg_match( '/<a[^>]*role=\"link\"/i', $content ) ) {
				$redundant_count++;
			}
		}

		$stats['redundant_role_hits'] = $redundant_count;

		if ( $redundant_count > 0 ) {
			$issues[] = __( 'Redundant ARIA roles detected on native elements', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Native HTML elements already provide accessibility roles. Removing redundant ARIA reduces screen reader noise and confusion.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/redundant-aria-labels?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
