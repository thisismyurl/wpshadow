<?php
/**
 * Site Title Missing or Blank Treatment
 *
 * Tests for site title configuration.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Title Missing or Blank Treatment Class
 *
 * Tests for proper site title configuration.
 *
 * @since 1.6033.0000
 */
class Treatment_Site_Title_Missing_Or_Blank extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-title-missing-or-blank';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Site Title Missing or Blank';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for proper site title configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check site title.
		$site_title = get_bloginfo( 'name' );

		if ( empty( $site_title ) || strlen( trim( $site_title ) ) === 0 ) {
			$issues[] = __( 'Site title is empty or not set - affects SEO and branding', 'wpshadow' );
		} else {
			// Check if title is default/placeholder.
			if ( $site_title === 'WordPress' || $site_title === 'Just Another WordPress Site' ) {
				$issues[] = sprintf(
					/* translators: %s: current site title */
					__( 'Site title is default (%s) - should be customized for your site', 'wpshadow' ),
					$site_title
				);
			}
		}

		// Check site title length (SEO best practice 30-60 chars).
		if ( strlen( $site_title ) > 60 ) {
			$issues[] = sprintf(
				/* translators: %s: site title */
				__( 'Site title is very long (%d characters) - may be truncated in search results', 'wpshadow' ),
				strlen( $site_title )
			);
		}

		if ( strlen( $site_title ) < 3 && strlen( $site_title ) > 0 ) {
			$issues[] = __( 'Site title is too short (less than 3 characters)', 'wpshadow' );
		}

		// Check for special characters that may cause encoding issues.
		if ( preg_match( '/[<>"]/', $site_title ) ) {
			$issues[] = __( 'Site title contains special characters that may cause encoding issues', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/site-title-missing-or-blank',
			);
		}

		return null;
	}
}
