<?php
/**
 * Tool Stack Documented Diagnostic
 *
 * Tests if team tools and workflows are documented.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tool Stack Documented Diagnostic Class
 *
 * Verifies that the technical stack and workflows are documented.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Documents_Tool_Stack extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'documents-tool-stack';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tool Stack Documented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if team tools and workflows are documented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'code-quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$manual_flag = get_option( 'wpshadow_tool_stack_documented' );
		if ( $manual_flag ) {
			return null;
		}

		$keywords = array(
			'tool stack',
			'tech stack',
			'technology stack',
			'tooling',
			'development tools',
		);

		if ( self::has_documented_item( $keywords ) || self::has_stack_files() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No tool stack documentation found. Document tools, services, and workflows so onboarding and troubleshooting are faster.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/tool-stack-documented',
			'persona'      => 'enterprise-corp',
		);
	}

	/**
	 * Check for documentation evidence in posts or attachments.
	 *
	 * @since 1.6093.1200
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

	/**
	 * Check for stack documentation files in the site root.
	 *
	 * @since 1.6093.1200
	 * @return bool True if files exist.
	 */
	private static function has_stack_files() {
		$paths = array(
			ABSPATH . 'TECH_STACK.md',
			ABSPATH . 'STACK.md',
			ABSPATH . 'docs/stack.md',
			ABSPATH . 'docs/tooling.md',
		);

		foreach ( $paths as $path ) {
			if ( file_exists( $path ) ) {
				return true;
			}
		}

		return false;
	}
}
