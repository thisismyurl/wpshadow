<?php
/**
 * Noindex For Staging Environment Not Configured Treatment
 *
 * Checks if staging environment noindex is configured.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Noindex For Staging Environment Not Configured Treatment Class
 *
 * Detects missing staging noindex configuration.
 *
 * @since 1.6030.2352
 */
class Treatment_Noindex_For_Staging_Environment_Not_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'noindex-for-staging-environment-not-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Noindex For Staging Environment Not Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if staging environment noindex is configured';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if this is a staging environment
		if ( strpos( $_SERVER['HTTP_HOST'] ?? '', 'staging' ) !== false || strpos( $_SERVER['HTTP_HOST'] ?? '', 'dev' ) !== false ) {
			// Check if noindex is set for staging
			if ( ! has_filter( 'wp_head', 'add_staging_noindex' ) ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __( 'Noindex for staging environment is not configured. Add noindex,nofollow meta robots tag to prevent staging sites from being indexed.', 'wpshadow' ),
					'severity'      => 'high',
					'threat_level'  => 75,
					'auto_fixable'  => true,
					'kb_link'       => 'https://wpshadow.com/kb/noindex-for-staging-environment-not-configured',
				);
			}
		}

		return null;
	}
}
