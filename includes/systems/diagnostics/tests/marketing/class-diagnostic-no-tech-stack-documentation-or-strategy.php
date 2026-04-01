<?php
/**
 * No Tech Stack Documentation or Strategy Diagnostic
 *
 * Checks if technology stack is documented.
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
 * Tech Stack Documentation Diagnostic
 *
 * Tech decisions compound over time.
 * Document why you chose each tool.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Tech_Stack_Documentation_Or_Strategy extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-tech-stack-documentation-strategy';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Tech Stack Documentation/Strategy';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if technology stack is documented';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'business-performance';

	/**
	 * Run diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_tech_documentation() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No tech stack documentation detected. Tech decisions compound over time. Document: 1) What you use (languages, frameworks, databases, infrastructure), 2) Why (pros/cons, decision rationale), 3) How it scales (limits, upgrade path), 4) Architecture (diagram, critical systems), 5) Dependencies (what breaks if X fails?), 6) Tech debt (what should we rewrite?). Helps: Onboarding new devs (understand stack faster), hiring (know what skills needed), scaling decisions (is our stack scalable?), debt management (what\'s slowing us down?). Example: "We use Node.js (fast dev) + React (developer experience) + PostgreSQL (reliability) + AWS (scale)". Update quarterly as you evolve.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/tech-stack-documentation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'issue'          => __( 'No tech stack documentation detected', 'wpshadow' ),
					'recommendation' => __( 'Document technology stack and strategic choices', 'wpshadow' ),
					'business_impact' => __( 'Slower onboarding, harder hiring, difficulty scaling', 'wpshadow' ),
					'stack_components' => self::get_stack_components(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if tech documentation exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if documentation detected, false otherwise.
	 */
	private static function has_tech_documentation() {
		$tech_posts = self::count_posts_by_keywords(
			array(
				'tech stack',
				'technology',
				'architecture',
				'database',
				'infrastructure',
			)
		);

		return $tech_posts > 0;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since 0.6093.1200
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
	 * Get stack components.
	 *
	 * @since 0.6093.1200
	 * @return array Stack components to document.
	 */
	private static function get_stack_components() {
		return array(
			'language'  => __( 'Language: Primary language(s) (JavaScript, Python, Go, etc.)', 'wpshadow' ),
			'framework' => __( 'Framework: Web framework (React, Vue, Angular, Django, Rails)', 'wpshadow' ),
			'database'  => __( 'Database: SQL (PostgreSQL, MySQL) or NoSQL (MongoDB, etc.)', 'wpshadow' ),
			'cache'     => __( 'Caching: Redis, Memcached, or built-in caching', 'wpshadow' ),
			'messaging' => __( 'Message Queue: Kafka, RabbitMQ, or AWS SQS', 'wpshadow' ),
			'infrastructure' => __( 'Infrastructure: AWS, GCP, Azure, or on-premise', 'wpshadow' ),
			'deployment' => __( 'Deployment: Docker, Kubernetes, or other', 'wpshadow' ),
			'ci_cd'     => __( 'CI/CD: GitHub Actions, CircleCI, Jenkins', 'wpshadow' ),
			'monitoring' => __( 'Monitoring: DataDog, New Relic, or built-in', 'wpshadow' ),
			'third_party' => __( 'Third-party Services: Stripe, Twilio, SendGrid, etc.', 'wpshadow' ),
		);
	}
}
