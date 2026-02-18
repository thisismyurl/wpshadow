<?php
/**
 * Migration and Site Clone Preparation
 *
 * Validates site readiness for migration or cloning to new environment.
 *
 * @since   1.6030.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Migration_Preparation Class
 *
 * Checks if site is prepared for migration or cloning to new environment.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Migration_Preparation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'migration-preparation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Migration and Site Clone Preparation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates site readiness for migration or cloning';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup-recovery';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Pattern 1: No migration plugin installed
		$has_migration_plugin = self::has_migration_plugin();

		if ( ! $has_migration_plugin ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No migration or cloning plugin installed', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/migration-preparation',
				'details'      => array(
					'issue' => 'no_migration_plugin',
					'message' => __( 'No WordPress migration or site cloning plugin detected', 'wpshadow' ),
					'when_needed' => array(
						'Moving to new hosting provider',
						'Changing domain name',
						'Migrating to new server/VPS',
						'Creating staging/testing copy',
						'Consolidating multiple sites',
					),
					'migration_plugins' => array(
						'All in One WP Migration' => 'Easy export/import, supports cloud storage',
						'Duplicator' => 'Full site snapshots with restore',
						'WP Staging Pro' => 'Creates temporary testing clone',
						'MigrateDB Pro' => 'Database migration with search/replace',
						'UpdraftPlus' => 'Backup can be restored on new host',
					),
					'why_important' => __( 'Migration without proper tools causes database corruption and broken links', 'wpshadow' ),
					'common_migration_problems' => array(
						'Hard-coded URLs break in new location',
						'Database search/replace incomplete',
						'Plugin incompatibilities in new environment',
						'PHP version conflicts',
						'File permissions wrong',
					),
					'recommendation' => __( 'Install a migration plugin before you need it', 'wpshadow' ),
					'proactive_benefit' => __( 'Having migration plugin ready means faster emergency moves if needed', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Database has hardcoded URLs
		$hardcoded_urls = self::check_hardcoded_urls();

		if ( $hardcoded_urls > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Database contains hardcoded URLs that will break on migration', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/migration-preparation',
				'details'      => array(
					'issue' => 'hardcoded_urls_in_database',
					'found_count' => intval( $hardcoded_urls ),
					'message' => sprintf(
						/* translators: %d: number of hardcoded URLs */
						__( 'Found %d hardcoded URLs in database. Migration search/replace will miss these', 'wpshadow' ),
						intval( $hardcoded_urls )
					),
					'examples' => array(
						'&lt;img src="https://example.com/image.jpg"&gt; in post content',
						'&lt;a href="https://example.com/page"&gt; in post body',
						'Custom code with absolute URLs',
					),
					'migration_problem' => __( 'When you move to new domain, these hardcoded URLs still point to old site', 'wpshadow' ),
					'solution' => __( 'Use search/replace to convert to site URL functions or relative paths', 'wpshadow' ),
					'fixing_steps' => array(
						'1. Identify all hardcoded URL instances',
						'2. Use migration plugin search/replace on database',
						'3. Update custom code to use WordPress URL functions',
						'4. Use relative paths where possible',
						'5. Test all links after migration',
					),
					'wordpress_best_practice' => __( 'Use home_url(), site_url(), plugins_url() instead of hardcoding', 'wpshadow' ),
					'recommendation' => __( 'Fix hardcoded URLs before migration to prevent broken links', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: Plugin dependencies not documented
		$plugin_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE 'plugin_' AND option_value != ''" );

		if ( $plugin_count > 10 ) {
			$has_plugin_documentation = (bool) get_option( 'migration_plugin_list', false );

			if ( ! $has_plugin_documentation ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Plugin dependencies not documented for migration', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 35,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/migration-preparation',
					'details'      => array(
						'issue' => 'undocumented_plugin_dependencies',
						'plugin_count' => intval( $plugin_count ),
						'message' => sprintf(
							/* translators: %d: number of plugins */
							__( 'Site has %d plugins; no documentation of critical dependencies', 'wpshadow' ),
							intval( $plugin_count )
						),
						'why_matters' => __( 'On new server, some plugins might not activate if dependencies missing', 'wpshadow' ),
						'documentation_should_include' => array(
							'Essential plugins that must be installed first',
							'Premium plugins requiring license reactivation',
							'Plugins with specific PHP/MySQL version requirements',
							'Plugins with API key configuration needed',
							'Custom code plugins that need manual setup',
						),
						'migration_checklist' => array(
							'Document all active plugins before migration',
							'Note which are free vs premium',
							'Record license keys for premium plugins',
							'Document custom plugin configuration',
							'List plugins with API dependencies',
						),
						'recommendation' => __( 'Create plugin dependency documentation for migration safety', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 4: Database size unusually large (slow migration)
		$database_size = self::get_database_size();

		if ( $database_size > ( 500 * 1024 * 1024 ) ) { // Over 500MB
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Large database may cause slow migration', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/migration-preparation',
				'details'      => array(
					'issue' => 'large_database_migration_risk',
					'database_size_mb' => ceil( $database_size / ( 1024 * 1024 ) ),
					'message' => sprintf(
						/* translators: %d: database size in MB */
						__( 'Database is %d MB. Large databases can be slow to export/import', 'wpshadow' ),
						ceil( $database_size / ( 1024 * 1024 ) )
					),
					'migration_time_estimate' => array(
						'500MB database' => '10-30 minutes export + 15-45 minutes import',
						'1GB database' => '30-60 minutes export + 45-90 minutes import',
						'Large database' => 'Potential timeout or memory errors',
					),
					'optimization_recommendations' => array(
						'Clean up old post revisions before migration',
						'Delete spam/trash comments',
						'Optimize database tables (run OPTIMIZE TABLE)',
						'Archive old log files',
						'Remove unused plugins (reduces overhead)',
					),
					'time_saving_steps' => array(
						'Clean database before export (reduces file size)',
						'Increase PHP memory_limit temporarily',
						'Disable backup plugins during migration',
						'Use dedicated migration tool (not manual export)',
					),
					'recommendation' => __( 'Optimize database before large migration for faster transfer', 'wpshadow' ),
				),
			);
		}

		// Pattern 5: SSL certificate not ready for new domain
		if ( ! is_ssl() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Current site not SSL; new host SSL setup not confirmed', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/migration-preparation',
				'details'      => array(
					'issue' => 'ssl_not_configured',
					'message' => __( 'Site not currently using SSL. New host SSL setup critical for migration', 'wpshadow' ),
					'ssl_importance' => array(
						'Google ranking factor (SSL sites rank higher)',
						'User trust (browsers show security warnings without SSL)',
						'Payment processing (required for e-commerce)',
						'Security requirement (modern standard)',
					),
					'migration_ssl_checklist' => array(
						'Confirm new host provides free SSL (Let\'s Encrypt)',
						'Plan SSL certificate installation timing',
						'Update database URLs to https after installation',
						'Set WordPress address and site URL to https',
						'Configure SSL on old host before switching DNS',
						'Test SSL on new host thoroughly',
					),
					'migration_ssl_steps' => array(
						'1. Before migration: Install SSL certificate on new host',
						'2. Migrate site with https:// URLs',
						'3. Test all pages load over HTTPS',
						'4. Fix mixed content warnings',
						'5. Update DNS to point to new host',
						'6. Verify SSL certificate installed correctly',
					),
					'recommendation' => __( 'Ensure SSL certificate ready on new host before migration', 'wpshadow' ),
				),
			);
		}

		// Pattern 6: No test environment for pre-migration validation
		$has_test_environment = self::has_test_environment();

		if ( ! $has_test_environment ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No test environment to validate migration before going live', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/migration-preparation',
				'details'      => array(
					'issue' => 'no_test_environment',
					'message' => __( 'No staging/test environment to validate migration safely before cutover', 'wpshadow' ),
					'why_critical' => __( 'Testing migration on live site risks breaking everything', 'wpshadow' ),
					'test_environment_benefits' => array(
						'Validate full migration process without affecting users',
						'Test all functionality in new environment',
						'Identify configuration issues early',
						'Verify performance on new host',
						'Test rollback procedure if needed',
					),
					'recommended_workflow' => array(
						'1. Create test environment on new host',
						'2. Perform full test migration',
						'3. Verify all functionality',
						'4. Test database search/replace',
						'5. Check all links and images',
						'6. Perform load testing',
						'7. Document any issues',
						'8. Plan fixes before live migration',
					),
					'common_migration_issues' => array(
						'Links broken (URLs not updated)',
						'Images not loading (file paths wrong)',
						'Plugins not activated (dependencies missing)',
						'Performance degradation (different server)',
						'Database compatibility (MySQL version difference)',
					),
					'recommendation' => __( 'Set up test environment and do full test migration before live', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}

	/**
	 * Check if migration plugin installed.
	 *
	 * @since  1.6030.2148
	 * @return bool True if migration plugin active.
	 */
	private static function has_migration_plugin() {
		$migration_plugins = array(
			'all-in-one-wp-migration/all-in-one-wp-migration.php',
			'duplicator/duplicator.php',
			'wp-staging/wp-staging.php',
			'updraftplus/updraftplus.php',
			'migrate-db-pro/migrate-db-pro.php',
		);

		foreach ( $migration_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for hardcoded URLs in database.
	 *
	 * @since  1.6030.2148
	 * @return int Count of hardcoded URLs.
	 */
	private static function check_hardcoded_urls() {
		global $wpdb;

		$site_url = get_option( 'siteurl' );
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE %s AND post_content LIKE %s",
				'%' . $wpdb->esc_like( $site_url ) . '%',
				'%href=%'
			)
		);

		return absint( $count );
	}

	/**
	 * Get database size in bytes.
	 *
	 * @since  1.6030.2148
	 * @return int Database size.
	 */
	private static function get_database_size() {
		global $wpdb;

		$size = $wpdb->get_var(
			"SELECT SUM(data_length + index_length) FROM information_schema.TABLES WHERE table_schema = '" . DB_NAME . "'"
		);

		return absint( $size );
	}

	/**
	 * Check if test environment exists.
	 *
	 * @since  1.6030.2148
	 * @return bool True if test environment detected.
	 */
	private static function has_test_environment() {
		$staging_plugins = array(
			'wp-staging/wp-staging.php',
			'all-in-one-wp-migration/all-in-one-wp-migration.php',
		);

		foreach ( $staging_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}
}
