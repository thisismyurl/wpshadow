<?php
/**
 * Open Graph Tags Diagnostic
 *
 * Issue #4974: No Open Graph Tags (Social Sharing)
 * Pillar: #1: Helpful Neighbor / 🎓 Learning Inclusive
 *
 * Checks if Open Graph tags are configured.
 * OG tags control how content appears when shared socially.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Open_Graph_Tags Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Open_Graph_Tags extends Diagnostic_Base {

	protected static $slug = 'open-graph-tags';
	protected static $title = 'No Open Graph Tags (Social Sharing)';
	protected static $description = 'Checks if Open Graph meta tags are configured';
	protected static $family = 'content';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Add og:title (page title)', 'wpshadow' );
		$issues[] = __( 'Add og:description (preview text)', 'wpshadow' );
		$issues[] = __( 'Add og:image (thumbnail image)', 'wpshadow' );
		$issues[] = __( 'Add og:url (canonical URL)', 'wpshadow' );
		$issues[] = __( 'Add og:type (website, article, video)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Open Graph tags control what appears when content is shared on Facebook, LinkedIn, Twitter. Without them, shares look broken.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/open-graph',
				'details'      => array(
					'recommendations'         => $issues,
					'example'                 => '<meta property="og:title" content="Page Title">',
					'platforms'               => 'Facebook, LinkedIn, Twitter (X), Slack',
					'social_benefit'          => 'Increase shares with better-looking previews',
				),
			);
		}

		return null;
	}
}
