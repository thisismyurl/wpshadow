<?php
/**
 * No API Documentation or Integration Strategy Diagnostic
 *
 * Checks if API documentation and integration strategy exist.
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
 * API Documentation Diagnostic
 *
 * APIs are the connective tissue of modern business.
 * Without integration, you're locked in.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Api_Documentation_Or_Integration_Strategy extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-api-documentation-integration-strategy';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'API Documentation/Integration Strategy';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if API documentation and integration strategy exist';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'business-performance';

	/**
	 * Run diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_api_strategy() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No API documentation or integration strategy detected. APIs are the connective tissue: without them, you\'re locked in. Document: 1) Your API (REST endpoints, authentication, rate limits, error codes), 2) Popular integrations (Zapier, native integrations with other tools), 3) Webhook support (real-time updates), 4) SDKs (client libraries for popular languages). Why? 1) Easier for customers to connect, 2) More powerful use cases, 3) Network effects (popular integrations = more valuable), 4) Enterprise sales (enterprises demand integrations). Good API docs include: Code examples, API reference, authentication guides, error handling, webhooks, SDKs. Invest in integration marketplace (Zapier, native).', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-documentation-integration',
				'details'     => array(
					'issue'          => __( 'No API documentation or integration strategy detected', 'wpshadow' ),
					'recommendation' => __( 'Document API and develop integration strategy', 'wpshadow' ),
					'business_impact' => __( 'Limited connectivity reduces product value and customer stickiness', 'wpshadow' ),
					'api_components'  => self::get_api_components(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if API strategy exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if strategy detected, false otherwise.
	 */
	private static function has_api_strategy() {
		$api_posts = self::count_posts_by_keywords(
			array(
				'API',
				'integration',
				'webhook',
				'REST',
				'connector',
			)
		);

		return $api_posts > 0;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since 1.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return int Number of matching posts.
	 */
	private static function count_posts_by_keywords( $keywords ) {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'posts_per_page' => 1,
					'post_type'      => array( 'post', 'page' ),
					'post_status'    => 'publish',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				++$total;
			}
		}

		return $total;
	}

	/**
	 * Get API components.
	 *
	 * @since 1.6093.1200
	 * @return array API components and documentation.
	 */
	private static function get_api_components() {
		return array(
			'api_reference' => __( '1. API Reference: All endpoints, methods (GET/POST), parameters, responses, status codes', 'wpshadow' ),
			'authentication' => __( '2. Authentication: How to get API key? OAuth? JWT? Permissions/scopes?', 'wpshadow' ),
			'rate_limits'  => __( '3. Rate Limits: How many requests/minute? Throttling strategy?', 'wpshadow' ),
			'webhooks'     => __( '4. Webhooks: Real-time events (when X happens, POST to URL)', 'wpshadow' ),
			'sdks'         => __( '5. SDKs: Client libraries (Node.js, Python, Ruby, Go)', 'wpshadow' ),
			'examples'     => __( '6. Code Examples: How to call each endpoint (in multiple languages)', 'wpshadow' ),
			'errors'       => __( '7. Error Handling: Common errors, how to resolve', 'wpshadow' ),
			'integrations' => __( '8. Popular Integrations: Zapier, Google Sheets, Slack, others', 'wpshadow' ),
		);
	}
}
