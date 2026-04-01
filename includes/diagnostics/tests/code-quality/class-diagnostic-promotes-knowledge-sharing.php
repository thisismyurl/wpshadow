<?php
/**
 * Knowledge Sharing Culture Diagnostic
 *
 * Tests if team shares learnings and best practices.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Knowledge Sharing Culture Diagnostic Class
 *
 * Verifies that learnings are captured and shared across the team.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Promotes_Knowledge_Sharing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'promotes-knowledge-sharing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Knowledge Sharing Culture';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if team shares learnings and best practices';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'code-quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$manual_flag = get_option( 'wpshadow_knowledge_sharing' );
		if ( $manual_flag ) {
			return null;
		}

		$keywords = array(
			'knowledge base',
			'playbook',
			'runbook',
			'how-to',
			'best practices',
		);

		if ( self::has_documented_item( $keywords ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No knowledge sharing artifacts found. Capture learnings in a knowledge base or playbook to reduce repeat incidents.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/knowledge-sharing-culture?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'persona'      => 'enterprise-corp',
		);
	}

	/**
	 * Check for documentation evidence in posts or attachments.
	 *
	 * @since 0.6093.1200
	 * @param  array $keywords Search terms.
	 * @return bool True if found.
	 */
	private static function has_documented_item( array $keywords ) {
		if ( ! function_exists( 'get_posts' ) ) {
			return false;
		}

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post', 'documentation', 'kb' ),
					'post_status'    => array( 'publish', 'private' ),
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				return true;
			}
		}

		return false;
	}
}
