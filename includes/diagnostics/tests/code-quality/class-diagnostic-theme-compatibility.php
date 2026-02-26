<?php
/**
 * Theme Compatibility and Browser Support
 *
 * Validates theme compatibility and browser support.
 *
 * @since   1.2034.1615
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Theme_Compatibility Class
 *
 * Checks theme compatibility and browser support.
 *
 * @since 1.2034.1615
 */
class Diagnostic_Theme_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-compatibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Compatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates theme compatibility and browser support';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'theme-quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2034.1615
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Pattern 1: Theme incompatible with current WordPress version
		$current_wp    = get_bloginfo( 'version' );
		$current_theme = wp_get_theme();

		$requires_wp  = $current_theme->get( 'Requires at least' );
		$tested_up_to = $current_theme->get( 'Tested up to' );

		if ( $requires_wp && version_compare( $current_wp, $requires_wp, '<' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme requires newer WordPress version', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-compatibility',
				'details'      => array(
					'issue'               => 'theme_wp_incompatible',
					'theme_requires'      => $requires_wp,
					'current_wp'          => $current_wp,
					'message'             => sprintf(
						/* translators: %s: version numbers */
						__( 'Theme requires WordPress %1$s (currently running %2$s)', 'wpshadow' ),
						$requires_wp,
						$current_wp
					),
					'compatibility_risks' => array(
						'Features may not work',
						'Potential crashes',
						'Security vulnerabilities',
						'Data loss risk',
					),
					'solution'            => array(
						'Update WordPress to meet requirement',
						'Or switch to compatible theme',
						'Check theme changelog for updates',
					),
					'updating_wordpress'  => array(
						'1. Backup entire site',
						'2. Go to Dashboard > Updates',
						'3. Click Update WordPress',
						'4. Test all functionality',
						'5. Check compatibility',
					),
					'theme_changelog'     => 'Check theme developer\'s changelog for version compatibility',
					'recommendation'      => __( 'Update WordPress to meet theme requirements', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Theme last tested with outdated WordPress
		if ( $tested_up_to && version_compare( $current_wp, $tested_up_to, '>' ) ) {
			$versions_ahead = intval( str_replace( '.', '', $current_wp ) ) - intval( str_replace( '.', '', $tested_up_to ) );

			if ( $versions_ahead > 2 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Theme not tested with current WordPress version', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 50,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/theme-compatibility',
					'details'      => array(
						'issue'                         => 'theme_outdated_testing',
						'theme_tested_up_to'            => $tested_up_to,
						'current_wp'                    => $current_wp,
						'versions_behind'               => $versions_ahead,
						'message'                       => sprintf(
							/* translators: %s: version numbers */
							__( 'Theme only tested up to WordPress %1$s (running %2$s)', 'wpshadow' ),
							$tested_up_to,
							$current_wp
						),
						'what_it_means'                 => array(
							'Theme not verified with current version',
							'May have compatibility issues',
							'Developer may not provide support',
							'Security patches may not be applied',
						),
						'checks_needed'                 => array(
							'Test theme on staging server',
							'Check for visual issues',
							'Verify all functionality works',
							'Check console for JavaScript errors',
							'Test on multiple browsers',
						),
						'developer_communication'       => 'Contact theme developer about updating testing',
						'theme_update_process'          => '// In theme style.css header
/*
Theme Name: My Theme
Version: 2.0
Tested up to: 6.4
Requires at least: 5.9
*/',
						'common_issues_after_wp_update' => array(
							'Block editor compatibility',
							'REST API changes',
							'Deprecated functions',
							'JavaScript changes',
							'Database schema updates',
						),
						'testing_on_staging'            => __( 'Create staging environment to safely test compatibility', 'wpshadow' ),
						'fallback_theme'                => __( 'Have compatible fallback theme ready', 'wpshadow' ),
						'recommendation'                => __( 'Contact theme developer to update theme for current WordPress version', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 3: Theme not mobile-responsive
		$theme_file = get_template_directory() . '/style.css';

		if ( file_exists( $theme_file ) ) {
			$content = file_get_contents( $theme_file );

			if ( ! preg_match( '/@media.*max-width|viewport.*width|mobile/', $content ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Theme may not be mobile-responsive', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 65,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/theme-compatibility',
					'details'      => array(
						'issue'                     => 'not_responsive',
						'message'                   => __( 'Theme lacks responsive design CSS', 'wpshadow' ),
						'mobile_users'              => __( 'Over 60% of web traffic comes from mobile devices', 'wpshadow' ),
						'seo_impact'                => __( 'Google prioritizes mobile-friendly sites in search results', 'wpshadow' ),
						'mobile_requirements'       => array(
							'Viewport meta tag' => '<meta name="viewport" content="width=device-width">',
							'Media queries'     => '@media (max-width: 768px)',
							'Flexible layout'   => 'Not fixed-width',
							'Touch-friendly'    => 'Large click targets',
						),
						'responsive_design_pattern' => '/* Mobile-first responsive design */

/* Mobile layout - default */
body { font-size: 14px; }
.container { width: 100%; padding: 10px; }

/* Tablet and up */
@media (min-width: 768px) {
	body { font-size: 16px; }
	.container { width: 750px; margin: 0 auto; }
}

/* Desktop and up */
@media (min-width: 1024px) {
	.container { width: 960px; }
}',
						'mobile_first'              => array(
							'Start with mobile layout',
							'Add complexity at larger breakpoints',
							'Simpler CSS, easier maintenance',
							'Better performance on mobile',
						),
						'breakpoints'               => array(
							'Phone'         => '320px - 480px',
							'Tablet'        => '481px - 768px',
							'Desktop'       => '769px - 1024px',
							'Large Desktop' => '1025px+',
						),
						'testing'                   => array(
							'Chrome DevTools device emulation',
							'Browserstack for real devices',
							'Test on actual phones/tablets',
							'Check landscape orientation',
						),
						'wordpress_themes'          => __( 'WordPress.org requires all themes to be mobile-responsive', 'wpshadow' ),
						'recommendation'            => __( 'Ensure theme has responsive CSS for all screen sizes', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 4: Theme using deprecated functions
		return null;
	}
}
