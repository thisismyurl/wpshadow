<?php
/**
 * Twitter Card Tags Diagnostic
 *
 * Issue #4975: No Twitter Card Tags
 * Pillar: #1: Helpful Neighbor
 *
 * Checks if Twitter Card tags are configured.
 * Twitter Cards control appearance on Twitter/X.
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
 * Diagnostic_Twitter_Card_Tags Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Twitter_Card_Tags extends Diagnostic_Base {

	protected static $slug = 'twitter-card-tags';
	protected static $title = 'No Twitter Card Tags';
	protected static $description = 'Checks if Twitter Card meta tags are configured';
	protected static $family = 'content';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Add twitter:card (summary, summary_large_image, player)', 'wpshadow' );
		$issues[] = __( 'Add twitter:title (page title)', 'wpshadow' );
		$issues[] = __( 'Add twitter:description (preview text)', 'wpshadow' );
		$issues[] = __( 'Add twitter:image (thumbnail)', 'wpshadow' );
		$issues[] = __( 'Add twitter:creator (author\'s Twitter handle)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Twitter Cards control how content appears when shared on Twitter/X. Without them, shares are plain and boring.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/twitter-cards',
				'details'      => array(
					'recommendations'         => $issues,
					'example'                 => '<meta name="twitter:card" content="summary_large_image">',
					'card_types'              => 'summary, summary_large_image, app, player',
					'benefits'                => 'Better visual appearance on Twitter/X',
				),
			);
		}

		return null;
	}
}
