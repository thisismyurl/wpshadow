<?php
/**
 * Site Tagline Missing or Poorly Configured Diagnostic
 *
 * Tests for site tagline configuration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Tagline Missing or Poorly Configured Diagnostic Class
 *
 * Tests for proper site tagline configuration.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Site_Tagline_Missing_Or_Poorly_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-tagline-missing-or-poorly-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Tagline Missing or Poorly Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for proper site tagline configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check site tagline (description).
		$site_tagline = get_bloginfo( 'description' );

		if ( empty( $site_tagline ) || strlen( trim( $site_tagline ) ) === 0 ) {
			$issues[] = __( 'Site tagline is empty - should describe your site purpose', 'wpshadow' );
		} else {
			// Check if tagline is default/placeholder.
			if ( $site_tagline === 'Just Another WordPress Site' ) {
				$issues[] = __( 'Site tagline is default - should be customized', 'wpshadow' );
			}
		}

		// Check tagline length (SEO best practice 120-160 chars).
		if ( strlen( $site_tagline ) > 160 ) {
			$issues[] = sprintf(
				/* translators: %d: character count */
				__( 'Site tagline is very long (%d characters) - may be truncated in search results', 'wpshadow' ),
				strlen( $site_tagline )
			);
		}

		if ( strlen( $site_tagline ) > 0 && strlen( $site_tagline ) < 3 ) {
			$issues[] = __( 'Site tagline is too short (less than 3 characters)', 'wpshadow' );
		}

		// Check for special characters.
		if ( preg_match( '/[<>"]/', $site_tagline ) ) {
			$issues[] = __( 'Site tagline contains special characters - may cause encoding issues', 'wpshadow' );
		}

		// Check if tagline is used in templates.
		$theme = wp_get_theme();
		if ( $theme->exists() ) {
			// This is a simplified check - actual theme may or may not use tagline.
			$issues[] = sprintf(
				/* translators: %s: theme name */
				__( 'Using theme %s - verify tagline is displayed properly', 'wpshadow' ),
				$theme->get( 'Name' )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/site-tagline-missing-or-poorly-configured',
			);
		}

		return null;
	}
}
