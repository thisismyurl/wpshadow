<?php
/**
 * Tagline Optimization Diagnostic
 *
 * Checks if tagline is customized and optimized for SEO. Detects generic or missing taglines.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2602.0100
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
 * Validates that the site tagline is customized, meaningful, and optimized for SEO.
 *
 * @since 1.2602.0100
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
	protected static $description = 'Checks if tagline is customized and optimized for SEO';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2602.0100
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$tagline   = get_bloginfo( 'description' );
		$site_name = get_bloginfo( 'name' );
		$issues    = array();
		$details   = array(
			'current_tagline' => $tagline,
			'tagline_length'  => strlen( $tagline ),
			'site_name'       => $site_name,
		);

		// Check if tagline is empty.
		if ( empty( $tagline ) ) {
			$issues[] = __( 'Site tagline is empty. A descriptive tagline helps search engines understand your site.', 'wpshadow' );
		} else {
			// Check if tagline is WordPress default.
			$default_taglines = array(
				'Just another WordPress site',
				'Just another WordPress Site',
				'Just Another WordPress Site',
			);

			if ( in_array( $tagline, $default_taglines, true ) ) {
				$issues[] = __( 'Using default WordPress tagline. Create a unique tagline that describes your site.', 'wpshadow' );
			}

			// Check if tagline is too short.
			if ( strlen( $tagline ) < 10 ) {
				$issues[] = sprintf(
					/* translators: %d: Current character count */
					__( 'Tagline is too short (%d characters). Recommended: at least 10 characters for meaningful description.', 'wpshadow' ),
					strlen( $tagline )
				);
			}

			// Check if tagline is too long for SEO.
			if ( strlen( $tagline ) > 160 ) {
				$issues[] = sprintf(
					/* translators: %d: Current character count */
					__( 'Tagline is too long (%d characters). Recommended: 160 characters or less for optimal SEO.', 'wpshadow' ),
					strlen( $tagline )
				);
			}

			// Check if tagline matches site name exactly.
			if ( ! empty( $site_name ) && strtolower( trim( $tagline ) ) === strtolower( trim( $site_name ) ) ) {
				$issues[] = __( 'Tagline is identical to site name. Use the tagline to add additional context about your site.', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => __( 'Site tagline needs optimization for better SEO and user experience.', 'wpshadow' ),
				'severity'           => 'low',
				'threat_level'       => 35,
				'site_health_status' => 'good',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/seo-tagline-optimization',
				'family'             => self::$family,
				'details'            => array(
					'issues' => $issues,
					'info'   => $details,
				),
			);
		}

		return null;
	}
}
