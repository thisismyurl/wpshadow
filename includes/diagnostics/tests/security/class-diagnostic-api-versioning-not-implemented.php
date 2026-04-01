<?php
/**
 * API Versioning Not Implemented Diagnostic
 *
 * Checks if API versioning is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API Versioning Not Implemented Diagnostic Class
 *
 * Detects missing API versioning.
 *
 * @since 0.6093.1200
 */
class Diagnostic_API_Versioning_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'api-versioning-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'API Versioning Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if API versioning is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if API versioning is set
		if ( ! has_filter( 'rest_request_before_callbacks', 'check_api_version' ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'API versioning is not implemented. Use API version numbers to support multiple client versions and manage deprecation gracefully.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/api-versioning-not-implemented?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'       => array(
					'why'            => __( 'Versioning = backward compatibility with old clients. Real scenario: API v1.0 endpoint /users/{id}/email returns firstname_email. Code changes in v1.1 to use full_email. Old mobile app breaks (wrong field). Without versioning: Users can\'t update app (API broken for them). With versioning: /v1/users/{id}/email vs /v2/users/{id}/email = both work. Old app keeps working.', 'wpshadow' ),
					'recommendation' => __( '1. Use URL versioning: /wp-json/v1/users or /wp-json/v2/users. 2. Keep old version alive for 12+ months. 3. Version in response headers: API-Version: 2.0. 4. Document deprecation schedule 6 months in advance. 5. Return 301 redirects from old endpoints. 6. Include deprecation warnings in response. 7. Monitor v1 usage to determine end-of-life. 8. Test all versions in staging before production. 9. Update documentation when new version released. 10. Provide migration guide in knowledge base.', 'wpshadow' ),
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'api-versioning', 'version-management' );
			return $finding;
		}

		return null;
	}
}
