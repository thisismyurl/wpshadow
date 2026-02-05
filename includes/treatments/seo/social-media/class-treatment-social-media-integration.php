<?php
/**
 * Social Media Integration Treatment
 *
 * Checks if social media sharing and integration is configured.
 *
 * @package WPShadow\Treatments
 * @since   1.6032.0147
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

/**
 * Treatment: Social Media Integration
 *
 * Detects whether the site has social media sharing and integration features.
 */
class Treatment_Social_Media_Integration extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-media-integration';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Social Media Integration';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for social media sharing and integration';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'social-media';

	/**
	 * Run the treatment check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues  = array();
		$stats   = array();
		$plugins = array(
			'social-snap/social-snap.php'                          => 'Social Snap',
			'social-media-feather/social-media-feather.php'        => 'Social Media Feather',
			'social-warfare/social-warfare.php'                    => 'Social Warfare',
			'monarch/monarch.php'                                  => 'Monarch',
			'shareaholic/shareaholic.php'                          => 'Shareaholic',
		);

		$active = array();
		foreach ( $plugins as $file => $name ) {
			if ( is_plugin_active( $file ) ) {
				$active[] = $name;
			}
		}

		$stats['social_sharing_tools'] = count( $active );
		$stats['social_plugins']       = $active;

		// Check for social media meta tags
		$header_content = '';
		$header_path    = get_theme_file_path( 'header.php' );
		if ( file_exists( $header_path ) ) {
			$header_content = file_get_contents( $header_path );
		}
		$stats['og_tags_found'] = preg_match( '/og:/', $header_content ) > 0;

		if ( empty( $active ) && ! $stats['og_tags_found'] ) {
			$issues[] = __( 'No social media integration or sharing tools detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Social media sharing increases organic reach and drives traffic from social networks. Proper integration with Open Graph tags ensures your content displays beautifully when shared, encouraging more clicks and engagement.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/social-media',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
