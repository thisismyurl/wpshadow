<?php
/**
 * Legal Pages Not Created Diagnostic
 *
 * Checks if legal pages exist.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Legal Pages Not Created Diagnostic Class
 *
 * Detects missing legal pages.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Legal_Pages_Not_Created extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'legal-pages-not-created';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Legal Pages Not Created';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if legal pages exist';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for privacy policy and terms pages
		$privacy_page = get_option( 'wp_page_for_privacy_policy' );
		$terms_page   = get_option( 'wp_page_for_terms' );

		if ( empty( $privacy_page ) || empty( $terms_page ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Legal pages are not created. Create Privacy Policy and Terms of Service pages to comply with legal requirements.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/legal-pages-not-created',
			);
		}

		return null;
	}
}
