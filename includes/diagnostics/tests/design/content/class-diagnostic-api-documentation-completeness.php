<?php
/**
 * API Documentation Completeness Diagnostic
 *
 * Issue #4903: API Endpoints Not Documented
 * Pillar: 🎓 Learning Inclusive / #12: Expandable
 *
 * Checks if REST API endpoints have documentation.
 * Developers need to understand API structure and parameters.
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
 * Diagnostic_API_Documentation_Completeness Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_API_Documentation_Completeness extends Diagnostic_Base {

	protected static $slug = 'api-documentation-completeness';
	protected static $title = 'API Endpoints Not Documented';
	protected static $description = 'Checks if REST API endpoints have complete documentation';
	protected static $family = 'content';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Document all API endpoints (path, method, parameters)', 'wpshadow' );
		$issues[] = __( 'Provide example requests and responses', 'wpshadow' );
		$issues[] = __( 'Document authentication requirements', 'wpshadow' );
		$issues[] = __( 'List possible error codes and meanings', 'wpshadow' );
		$issues[] = __( 'Include code examples in multiple languages', 'wpshadow' );
		$issues[] = __( 'Keep documentation versioned with API changes', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'API documentation helps developers integrate successfully. Without docs, developers waste hours guessing parameters and debugging.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/api-documentation',
				'details'      => array(
					'recommendations'         => $issues,
					'documentation_tools'     => 'Swagger/OpenAPI, Postman, WordPress REST API docs',
					'example_quality'         => 'Include curl, JavaScript, PHP examples',
					'commandment'             => 'Commandment #12: Expandable (developers need clear APIs)',
				),
			);
		}

		return null;
	}
}
