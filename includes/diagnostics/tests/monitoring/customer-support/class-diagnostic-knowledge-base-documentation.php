<?php
/**
 * Knowledge Base Documentation Diagnostic
 *
 * Checks if comprehensive knowledge base documentation is available.
 *
 * @package WPShadow\Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Knowledge Base Documentation
 *
 * Detects whether the site has comprehensive self-service documentation.
 */
class Diagnostic_Knowledge_Base_Documentation extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'knowledge-base-documentation';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Knowledge Base Documentation';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for self-service documentation and knowledge base';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'customer-support';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues  = array();
		$stats   = array();
		$plugins = array(
			'documentations/documentations.php'                => 'Documentations',
			'kb-articles/kb-articles.php'                      => 'KB Articles',
			'learndash/learndash.php'                          => 'LearnDash',
			'knowledge-base-for-woocommerce/kb-woo.php'        => 'KB for WooCommerce',
			'happyaddons-pro/happyaddons-pro.php'              => 'Happy Addons Pro',
		);

		$active = array();
		foreach ( $plugins as $file => $name ) {
			if ( is_plugin_active( $file ) ) {
				$active[] = $name;
			}
		}

		$stats['active_kb_tools']    = count( $active );
		$stats['kb_plugins_found']   = $active;

		// Check for documentation pages
		$doc_pages = self::find_pages_by_keywords( array( 'documentation', 'help', 'how to', 'guide', 'tutorial' ) );
		$stats['documentation_pages'] = count( $doc_pages );

		if ( empty( $active ) && count( $doc_pages ) < 3 ) {
			$issues[] = __( 'Limited knowledge base or documentation found', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'A comprehensive knowledge base reduces support tickets by 30-50%, as customers can find answers independently. Well-organized documentation also improves user experience and builds customer confidence.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/knowledge-base-setup?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}

	/**
	 * Find pages by keywords
	 *
	 * @param array $keywords Keywords to search for
	 * @return array List of matching pages
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
