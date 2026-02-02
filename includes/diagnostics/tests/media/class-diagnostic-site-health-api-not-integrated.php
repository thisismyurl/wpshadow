<?php
/**
 * Site Health API Not Integrated Diagnostic
 *
 * Tests for WordPress Site Health integration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Health API Not Integrated Diagnostic Class
 *
 * Tests for WordPress Site Health integration.
 *
 * @since 1.26033.0000
 */
class Diagnostic_Site_Health_API_Not_Integrated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-health-api-not-integrated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Health API Not Integrated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for WordPress Site Health integration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if Site Health is available.
		if ( ! function_exists( 'WP_Site_Health' ) ) {
			$issues[] = __( 'WordPress Site Health not available', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/site-health-api-not-integrated',
			);
		}

		// Check if Site Health checks are registered.
		if ( ! has_filter( 'site_health_tests' ) ) {
			$issues[] = __( 'No custom Site Health tests registered', 'wpshadow' );
		}

		// Check if WPShadow diagnostics feed into Site Health.
		if ( ! has_filter( 'wp_site_health_tests' ) ) {
			$issues[] = __( 'WPShadow diagnostics not integrated with Site Health API', 'wpshadow' );
		}

		// Check Site Health status.
		$site_health = WP_Site_Health::get_instance();

		if ( method_exists( $site_health, 'get_tests' ) ) {
			$tests = $site_health->get_tests();

			if ( empty( $tests ) ) {
				$issues[] = __( 'No Site Health tests available', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/site-health-api-not-integrated',
			);
		}

		return null;
	}
}
