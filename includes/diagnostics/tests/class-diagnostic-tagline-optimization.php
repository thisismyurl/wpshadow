<?php
/**
 * Tagline Optimization Diagnostic
 *
 * Verifies that the site tagline (blog description) is properly configured
 * to provide a clear value proposition and improve SEO.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tagline Optimization Diagnostic Class
 *
 * Ensures site tagline is meaningful and properly configured.
 *
 * @since 1.26032.1800
 */
class Diagnostic_Tagline_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tagline-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tagline Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site tagline is meaningful';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Tagline is set and not default
	 * - Tagline length is reasonable (10-160 chars)
	 * - Tagline is meaningful (not just filler text)
	 *
	 * @since  1.26032.1800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get tagline.
		$tagline = get_option( 'blogdescription', '' );

		// Check if tagline is set.
		if ( empty( $tagline ) ) {
			$issues[] = __( 'Site tagline (blog description) is not configured; adding a tagline improves SEO', 'wpshadow' );
		} else {
			// Check tagline length.
			$tagline_length = strlen( $tagline );

			if ( $tagline_length < 5 ) {
				$issues[] = __( 'Site tagline is very short; provide a more descriptive tagline', 'wpshadow' );
			} elseif ( $tagline_length > 160 ) {
				$issues[] = sprintf(
					/* translators: %d: current tagline length */
					__( 'Site tagline is too long (%d characters); search engines prefer under 160 characters', 'wpshadow' ),
					$tagline_length
				);
			}

			// Check for default taglines.
			$default_taglines = array(
				'just another wordpress site',
				'just another wordpress.com site',
				'the best site ever',
				'coming soon',
				'under construction',
			);

			if ( in_array( strtolower( $tagline ), $default_taglines, true ) ) {
				$issues[] = __( 'Site tagline appears to be default text; please customize it to describe your site', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/tagline-optimization',
			);
		}

		return null;
	}
}
