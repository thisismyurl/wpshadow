<?php
/**
 * Plugin Dependency Management
 *
 * Validates plugin dependencies and conflicts.
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
 * Diagnostic_Plugin_Dependencies Class
 *
 * Checks plugin dependencies and conflicts.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Plugin_Dependencies extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-dependencies';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Dependencies';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates plugin dependencies and conflicts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugin-ecosystem';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Pattern 1: Plugin with unmet dependencies
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $active_plugins as $plugin ) {
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
			$requires = $plugin_data['RequiresPlugins'] ?? '';

			if ( ! empty( $requires ) ) {
				$required_plugins = array_map( 'trim', explode( ',', $requires ) );

				foreach ( $required_plugins as $required ) {
					if ( ! is_plugin_active( $required . '/' . $required . '.php' ) ) {
						return array(
							'id'           => self::$slug,
							'title'        => self::$title,
							'description'  => __( 'Plugin has unmet dependencies', 'wpshadow' ),
							'severity'     => 'high',
							'threat_level' => 65,
							'auto_fixable' => false,
							'kb_link'      => 'https://wpshadow.com/kb/plugin-dependencies',
							'details'      => array(
								'issue' => 'unmet_dependencies',
								'plugin' => $plugin_data['Name'] ?? $plugin,
								'missing_dependency' => $required,
								'message' => sprintf(
									/* translators: %s: plugin names */
									__( '%s requires %s plugin to be active', 'wpshadow' ),
									$plugin_data['Name'] ?? $plugin,
									$required
								),
								'risks' => array(
									'Plugin features may not work',
									'Compatibility issues',
									'Potential site errors',
									'Data loss risk',
								),
								'solution' => array(
									'1. Install required plugin',
									'2. Activate required plugin',
									'3. Verify main plugin works',
									'4. Check for conflicts',
								),
								'dependency_declaration' => "// In plugin header comment
/*
Plugin Name: My Plugin
Description: Does something cool
Requires Plugins: dependency-plugin
*/",
								'checking_dependencies' => "\$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/my-plugin/my-plugin.php');
\$requires = \$plugin_data['RequiresPlugins'] ?? '';

if (!empty(\$requires)) {
	\$required = explode(',', \$requires);
	foreach (\$required as \$dep) {
		if (!is_plugin_active(trim(\$dep))) {
			wp_die('Required plugin not active');
		}
	}
}",
								'documenting_dependencies' => __( 'Include dependency information in plugin README', 'wpshadow' ),
								'version_compatibility' => __( 'Specify minimum versions for dependencies', 'wpshadow' ),
								'recommendation' => __( 'Install and activate all required plugins', 'wpshadow' ),
							),
						);
					}
				}
			}
		}

		// Pattern 2: Conflicting plugins detected
		$conflicts = array(
			'jetpack/jetpack.php' => array( 'akismet/akismet.php', 'wordfence/wordfence.php' ),
			'woocommerce/woocommerce.php' => array( 'easy-digital-downloads/easy-digital-downloads.php' ),
			'elementor/elementor.php' => array( 'divi/divi.php', 'beaver-builder/bb-plugin.php' ),
		);

		foreach ( $active_plugins as $plugin ) {
			if ( isset( $conflicts[ $plugin ] ) ) {
				foreach ( $conflicts[ $plugin ] as $conflict ) {
					if ( is_plugin_active( $conflict ) ) {
						$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
						$conflict_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $conflict );

						return array(
							'id'           => self::$slug,
							'title'        => self::$title,
							'description'  => __( 'Conflicting plugins detected', 'wpshadow' ),
							'severity'     => 'high',
							'threat_level' => 70,
							'auto_fixable' => false,
							'kb_link'      => 'https://wpshadow.com/kb/plugin-conflicts',
							'details'      => array(
								'issue' => 'plugin_conflict',
								'plugin1' => $plugin_data['Name'] ?? $plugin,
								'plugin2' => $conflict_data['Name'] ?? $conflict,
								'message' => sprintf(
									/* translators: %s: plugin names */
									__( '%s and %s are known to conflict', 'wpshadow' ),
									$plugin_data['Name'] ?? $plugin,
									$conflict_data['Name'] ?? $conflict
								),
								'conflict_symptoms' => array(
									'Broken features',
									'JavaScript errors',
									'Styling issues',
									'Performance problems',
									'Site crashes',
								),
								'resolution' => array(
									'Deactivate one of the conflicting plugins',
									'Look for alternative plugins',
									'Contact plugin developers',
									'Check support forums for workarounds',
								),
								'finding_alternatives' => "// Check WordPress.org for alternatives
// Search: 'page builder' or feature category
// Compare: reviews, ratings, support, features",
								'testing_after_change' => array(
									'Test all affected features',
									'Check for JavaScript errors',
									'Verify styling is correct',
									'Monitor site performance',
									'Check activity log',
								),
								'common_conflicts' => array(
									'Page builders' => 'Can\'t run multiple builders',
									'Cache plugins' => 'May interfere with each other',
									'SEO plugins' => 'Duplicate functionality',
									'Security plugins' => 'Can cause false positives',
								),
								'communication' => __( 'Contact plugin developers if conflict exists', 'wpshadow' ),
								'recommendation' => __( 'Deactivate or remove one of the conflicting plugins', 'wpshadow' ),
							),
						);
					}
				}
			}
		}

		// Pattern 3: Too many plugins slowing site
		if ( count( $active_plugins ) > 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Excessive number of plugins installed', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugin-performance',
				'details'      => array(
					'issue' => 'too_many_plugins',
					'active_count' => count( $active_plugins ),
					'message' => sprintf(
						/* translators: %d: count */
						__( '%d plugins are active (recommended: 10-20)', 'wpshadow' ),
						count( $active_plugins )
					),
					'performance_impact' => array(
						'Each plugin adds startup time',
						'More database queries',
						'Larger memory footprint',
						'Slower page loads',
						'Increased maintenance',
					),
					'optimization_strategies' => array(
						'Audit plugin functionality' => 'What does each do?',
						'Find all-in-one solutions' => 'Combine multiple plugins',
						'Remove unused plugins' => 'Delete deactivated ones',
						'Use multisite' => 'Share plugins across sites',
						'Custom code' => 'Replace simple plugins',
					),
					'plugin_audit' => "// Analyze what each plugin does
foreach (get_plugins() as \$plugin_file => \$plugin_data) {
	\$is_active = is_plugin_active(\$plugin_file);
	echo \$plugin_data['Name'] . ' - ' . 
		 (\$is_active ? 'ACTIVE' : 'INACTIVE') . '\\n';
	echo 'Purpose: ' . \$plugin_data['Description'] . '\\n\\n';
}",
					'consolidation_opportunities' => array(
						'SEO Rank Math + SEO by Yoast' => 'Keep one',
						'Contact Form 7 + WPForms' => 'Choose one',
						'Akismet + WordFence' => 'Redundant features',
						'Multiple caching plugins' => 'Conflicts',
					),
					'custom_code_replacement' => "// Instead of plugin, use custom code
add_filter('wp_sitemaps_posts_per_page', function() {
	return 50; // Custom sitemap limit
});",
					'multisite_sharing' => __( 'Use multisite to share core plugins', 'wpshadow' ),
					'testing_after_removal' => array(
						'Test all site functionality',
						'Check for broken features',
						'Monitor performance',
						'Verify data integrity',
					),
					'recommendation' => __( 'Reduce number of active plugins to 20 or fewer', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
