<?php
/**
 * Knowledge Base Diagnostic
 *
 * Checks whether a knowledge base or help center is available.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\CustomerSupport
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Knowledge Base Diagnostic Class
 *
 * Verifies that a help center or documentation area exists.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Knowledge_Base extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'knowledge-base';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Knowledge Base or Help Center';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if a help center or knowledge base exists';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'customer-support';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$kb_plugins = array(
			'echo-knowledge-base/echo-knowledge-base.php' => 'Echo Knowledge Base',
			'helpie/hlp-helpie.php'                       => 'Helpie',
			'we-docs/we-docs.php'                         => 'weDocs',
			'betterdocs/betterdocs.php'                   => 'BetterDocs',
		);

		$active_plugins = array();
		foreach ( $kb_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_plugins[] = $plugin_name;
			}
		}

		$stats['kb_plugins'] = ! empty( $active_plugins ) ? implode( ', ', $active_plugins ) : 'none';

		$kb_pages = self::find_pages_by_keywords(
			array(
				'knowledge base',
				'help center',
				'support center',
				'documentation',
				'faq',
				'help',
			)
		);

		$stats['kb_pages'] = ! empty( $kb_pages ) ? implode( ', ', $kb_pages ) : 'none';

		if ( empty( $active_plugins ) && empty( $kb_pages ) ) {
			$issues[] = __( 'No help center or knowledge base content detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'A knowledge base lets customers help themselves at any time. This can reduce support tickets and improve confidence.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/knowledge-base',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}

	/**
	 * Find pages or posts by keyword search.
	 *
	 * @since 1.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return array List of matching page titles.
	 */
	private static function find_pages_by_keywords( array $keywords ): array {
		$matches = array();

		foreach ( $keywords as $keyword ) {
			$results = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post' ),
					'post_status'    => 'publish',
					'posts_per_page' => 5,
				)
			);

			foreach ( $results as $post ) {
				$matches[ $post->ID ] = get_the_title( $post );
			}
		}

		return array_values( $matches );
	}
}
