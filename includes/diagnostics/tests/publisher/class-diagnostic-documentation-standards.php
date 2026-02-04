<?php
/**
 * Documentation Standards Diagnostic
 *
 * Tests if processes and decisions are documented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1517
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Documentation Standards Diagnostic Class
 *
 * Evaluates whether the site maintains documentation standards for processes,
 * decisions, content guidelines, and workflows.
 *
 * @since 1.6035.1517
 */
class Diagnostic_Documentation_Standards extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'maintains_documentation_standards';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Documentation Standards';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if processes and decisions are documented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publisher';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1517
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$stats         = array();
		$issues        = array();
		$warnings      = array();
		$score         = 0;
		$total_points  = 0;
		$earned_points = 0;

		// Check for documentation-related post types.
		$total_points += 15;
		$doc_post_types = array( 'documentation', 'docs', 'wiki', 'kb', 'knowledge_base', 'guide' );
		$registered_post_types = get_post_types( array( 'public' => true ), 'names' );

		$found_doc_types = array();
		foreach ( $doc_post_types as $doc_type ) {
			if ( in_array( $doc_type, $registered_post_types, true ) ) {
				$found_doc_types[] = $doc_type;
			}
		}

		if ( ! empty( $found_doc_types ) ) {
			$earned_points += 15;
		}

		$stats['documentation_post_types'] = array(
			'found' => count( $found_doc_types ),
			'list'  => $found_doc_types,
		);

		if ( empty( $found_doc_types ) ) {
			$warnings[] = __( 'No dedicated documentation post types detected', 'wpshadow' );
		}

		// Check for documentation plugins.
		$total_points += 15;
		$doc_plugins = array(
			'knowledge-base/knowledge-base.php'             => 'Knowledge Base',
			'betterdocs/betterdocs.php'                     => 'BetterDocs',
			'heroic-kb/heroic-kb.php'                       => 'Heroic KB',
			'echo-knowledge-base/echo-knowledge-base.php'   => 'Echo Knowledge Base',
			'wp-wiki/wp-wiki.php'                           => 'WP Wiki',
			'document-library-pro/document-library-pro.php' => 'Document Library Pro',
		);

		$active_doc_plugins = array();
		foreach ( $doc_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_doc_plugins[] = $name;
			}
		}

		if ( ! empty( $active_doc_plugins ) ) {
			$earned_points += 15;
		}

		$stats['documentation_plugins'] = array(
			'found' => count( $active_doc_plugins ),
			'list'  => $active_doc_plugins,
		);

		// Check for documentation pages/posts.
		$total_points += 15;
		$doc_keywords = array( 'documentation', 'guide', 'policy', 'process', 'workflow', 'standard', 'procedure' );
		$doc_pages    = array();

		foreach ( $doc_keywords as $keyword ) {
			$pages = get_posts(
				array(
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 10,
					'post_status'    => 'publish',
					's'              => $keyword,
				)
			);
			$doc_pages = array_merge( $doc_pages, $pages );
		}

		// Remove duplicates.
		$doc_pages = array_unique( $doc_pages, SORT_REGULAR );

		if ( count( $doc_pages ) >= 5 ) {
			$earned_points += 15;
		} elseif ( count( $doc_pages ) >= 2 ) {
			$earned_points += 10;
		}

		$stats['documentation_pages'] = count( $doc_pages );

		if ( count( $doc_pages ) < 5 ) {
			$warnings[] = __( 'Few documentation pages found (fewer than 5)', 'wpshadow' );
		}

		// Check for style guide/brand guidelines.
		$total_points += 10;
		$style_guide_pages = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				's'              => 'style guide',
			)
		);

		if ( ! empty( $style_guide_pages ) ) {
			$earned_points += 10;
			$stats['style_guide_exists'] = true;
		} else {
			$stats['style_guide_exists'] = false;
			$warnings[] = __( 'No style guide or brand guidelines found', 'wpshadow' );
		}

		// Check for editorial calendar/workflow tools.
		$total_points += 15;
		$workflow_plugins = array(
			'edit-flow/edit-flow.php'                       => 'Edit Flow',
			'publishpress/publishpress.php'                 => 'PublishPress',
			'co-authors-plus/co-authors-plus.php'           => 'Co-Authors Plus',
			'editorial-calendar/edcal.php'                  => 'Editorial Calendar',
			'revisionary/revisionary.php'                   => 'Revisionary',
		);

		$active_workflow_plugins = array();
		foreach ( $workflow_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_workflow_plugins[] = $name;
			}
		}

		if ( ! empty( $active_workflow_plugins ) ) {
			$earned_points += 15;
		}

		$stats['workflow_plugins'] = array(
			'found' => count( $active_workflow_plugins ),
			'list'  => $active_workflow_plugins,
		);

		if ( empty( $active_workflow_plugins ) ) {
			$warnings[] = __( 'No editorial workflow or calendar plugins detected', 'wpshadow' );
		}

		// Check for revision tracking.
		$total_points += 10;
		$revisions_enabled = defined( 'WP_POST_REVISIONS' ) && WP_POST_REVISIONS !== false;
		if ( $revisions_enabled ) {
			$earned_points += 10;
			$stats['revisions_enabled'] = true;
		} else {
			$stats['revisions_enabled'] = false;
			$warnings[] = __( 'Post revisions are disabled', 'wpshadow' );
		}

		// Check for content templates.
		$total_points += 10;
		$template_plugins = array(
			'content-templates-for-elementor/content-templates.php' => 'Content Templates',
			'reusable-blocks-extended/reusable-blocks-extended.php' => 'Reusable Blocks',
		);

		$active_template_plugins = array();
		foreach ( $template_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_template_plugins[] = $name;
			}
		}

		// Check for reusable blocks.
		$reusable_blocks = get_posts(
			array(
				'post_type'      => 'wp_block',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);

		if ( ! empty( $active_template_plugins ) || count( $reusable_blocks ) >= 3 ) {
			$earned_points += 10;
		}

		$stats['content_templates'] = array(
			'plugins'         => count( $active_template_plugins ),
			'reusable_blocks' => count( $reusable_blocks ),
		);

		// Check for version control hints.
		$total_points += 10;
		$git_hints = array(
			'.git'        => ABSPATH . '.git',
			'.gitignore'  => ABSPATH . '.gitignore',
		);

		$version_control_detected = false;
		foreach ( $git_hints as $hint => $path ) {
			if ( file_exists( $path ) ) {
				$version_control_detected = true;
				break;
			}
		}

		if ( $version_control_detected ) {
			$earned_points += 10;
			$stats['version_control'] = true;
		} else {
			$stats['version_control'] = false;
			$warnings[] = __( 'No version control system detected', 'wpshadow' );
		}

		// Calculate final score.
		if ( $total_points > 0 ) {
			$score = round( ( $earned_points / $total_points ) * 100 );
		}

		$stats['score']         = $score;
		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;

		// Determine severity.
		$severity     = 'medium';
		$threat_level = 35;

		if ( $score < 30 ) {
			$severity     = 'high';
			$threat_level = 50;
		} elseif ( $score > 70 ) {
			$severity     = 'low';
			$threat_level = 20;
		}

		// Return finding if documentation standards are insufficient.
		if ( $score < 50 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: documentation score percentage */
					__( 'Documentation standards score: %d%%. Maintaining documentation helps teams stay aligned and ensures consistency.', 'wpshadow' ),
					$score
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/documentation-standards',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}
}
