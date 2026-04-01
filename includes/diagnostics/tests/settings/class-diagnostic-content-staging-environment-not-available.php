<?php
/**
 * Content Staging Environment Not Available Diagnostic
 *
 * Checks if staging environment is available.
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
 * Content Staging Environment Not Available Diagnostic Class
 *
 * Detects missing staging environment.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Content_Staging_Environment_Not_Available extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-staging-environment-not-available';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Staging Environment Not Available';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if staging environment is available';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if staging environment is configured
		if ( ! defined( 'STAGING_URL' ) && ! get_option( 'staging_site_url' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Content staging environment is not available. Set up a staging site for testing changes before deploying to production.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/content-staging-environment-not-available?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
