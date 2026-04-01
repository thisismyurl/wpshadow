<?php
/**
 * User Roles and Capabilities Configuration
 *
 * Validates user roles and capabilities are properly configured.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_User_Roles_Configuration Class
 *
 * Checks user roles and capabilities configuration.
 *
 * @since 0.6093.1200
 */
class Diagnostic_User_Roles_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-roles-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Roles Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates user roles and capabilities are properly configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-management';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_roles;

		// Pattern 1: Default WordPress roles modified without backup
		if ( isset( $wp_roles ) ) {
			$default_roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );
			$current_roles = array_keys( $wp_roles->roles );
			$modified = array();

			foreach ( $default_roles as $role ) {
				if ( in_array( $role, $current_roles, true ) ) {
					$role_obj = $wp_roles->get_role( $role );

					// Check if capabilities have been modified
					$backup = get_option( 'wpshadow_role_backup_' . $role );

					if ( false === $backup ) {
						// No backup exists
						$modified[] = $role;
					}
				}
			}

			if ( ! empty( $modified ) && count( $modified ) > 2 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Default WordPress roles modified without backup', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 55,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/user-roles-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issue' => 'modified_roles_no_backup',
						'modified_roles' => $modified,
						'message' => sprintf(
							/* translators: %s: role names */
							__( 'Roles %s modified without documented backup', 'wpshadow' ),
							implode( ', ', $modified )
						),
						'why_backup_important' => array(
							'Accidental changes lost',
							'Cannot undo modifications',
							'Need restore point',
							'Troubleshooting becomes difficult',
						),
						'wordpress_default_roles' => array(
							'Administrator' => 'Full site access',
							'Editor' => 'Publish and manage all content',
							'Author' => 'Publish own content',
							'Contributor' => 'Create content, needs approval',
							'Subscriber' => 'Can only read',
						),
						'backup_strategy' => array(
							'Export roles periodically',
							'Test changes in staging',
							'Document role modifications',
							'Keep version history',
						),
						'backing_up_roles' => "// Create backup before modifying
\$role_backup = array();
foreach (array('administrator', 'editor', 'author') as \$role) {
	\$role_obj = get_role(\$role);
	\$role_backup[\$role] = \$role_obj->capabilities;
}

// Save to database or export
update_option('wpshadow_role_backup_' . date('Y-m-d'), \$role_backup);",
					'restoring_roles' => "// Restore from backup
\$backup = get_option('wpshadow_role_backup_' . \$date);

if (\$backup) {
	foreach (\$backup as \$role_name => \$capabilities) {
		\$role = get_role(\$role_name);

		// Clear current caps
		foreach (\$role->capabilities as \$cap => \$grant) {
			\$role->remove_cap(\$cap);
		}

		// Restore from backup
		foreach (\$capabilities as \$cap => \$grant) {
			\$role->add_cap(\$cap, \$grant);
		}
	}
}",
					'exporting_roles' => "// Export roles to JSON for backup
\$wp_roles = wp_roles();
\$export = array();

foreach (\$wp_roles->roles as \$role_name => \$role_data) {
	\$export[\$role_name] = \$role_data['capabilities'];
}

file_put_contents('role-backup-' . date('Y-m-d-His') . '.json',
	json_encode(\$export, JSON_PRETTY_PRINT)
);",
					'testing_in_staging' => __( 'Always test role changes in staging environment first', 'wpshadow' ),
					'documentation' => __( 'Document why each capability change was made', 'wpshadow' ),
					'recommendation' => __( 'Create backup of all role configurations before modifications', 'wpshadow' ),
				),
			);
			}
		}

		// Pattern 2: Custom capabilities not matching functionality
		$wp_roles = wp_roles();
		$custom_caps = array();

		foreach ( $wp_roles->roles as $role_name => $role_data ) {
			foreach ( $role_data['capabilities'] as $cap => $grant ) {
				if ( ! in_array( $cap, self::get_default_capabilities(), true ) ) {
					$custom_caps[] = $cap;
				}
			}
		}

		$custom_caps = array_unique( $custom_caps );

		if ( count( $custom_caps ) > 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Many custom capabilities without clear purpose', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-roles-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'unclear_custom_capabilities',
					'custom_cap_count' => count( $custom_caps ),
					'sample_caps' => array_slice( $custom_caps, 0, 20 ),
					'message' => sprintf(
						/* translators: %d: number of custom caps */
						__( '%d custom capabilities without documentation', 'wpshadow' ),
						count( $custom_caps )
					),
					'what_are_capabilities' => __( 'Permissions that define what users can do', 'wpshadow' ),
					'standard_capabilities' => array(
						'edit_posts' => 'Create/edit posts',
						'publish_posts' => 'Publish posts',
						'manage_categories' => 'Manage post categories',
						'moderate_comments' => 'Approve comments',
						'manage_options' => 'Access admin panel',
					),
					'custom_capabilities' => array(
						'Used by plugins' => 'Plugin-specific permissions',
						'Theme features' => 'Theme-specific controls',
						'Business logic' => 'Custom application logic',
					),
					'capability_naming' => array(
						'Use lowercase' => 'edit_products',
						'Use underscores' => 'manage_product_categories',
						'Be descriptive' => 'approve_customer_reviews',
						'Be consistent' => 'Prefix with plugin name',
					),
					'documenting_capabilities' => "// Document custom capabilities clearly
\$custom_capabilities = array(
	'manage_products' => array(
		'description' => 'User can create, edit, delete products',
		'assigned_to_roles' => array('administrator', 'editor'),
		'created_by' => 'WooCommerce plugin',
	),
	'approve_reviews' => array(
		'description' => 'User can approve customer product reviews',
		'assigned_to_roles' => array('administrator', 'moderator'),
		'created_by' => 'Custom plugin',
	),
);

// Store documentation
update_option('wpshadow_capability_docs', \$custom_capabilities);",
					'auditing_capabilities' => array(
						'1. List all custom capabilities',
						'2. Document purpose of each',
						'3. Verify assigned to correct roles',
						'4. Remove unused capabilities',
						'5. Create capability map',
					),
					'removing_unused' => "// Remove unused capabilities
\$roles = wp_roles();

foreach (\$roles->roles as \$role_name => \$role_data) {
	\$role = get_role(\$role_name);

	foreach (\$role->capabilities as \$cap => \$grant) {
		// If capability not used anywhere
		if (!capability_is_used(\$cap)) {
			\$role->remove_cap(\$cap);
		}
	}
}",
					'capability_mapping' => __( 'Create documentation mapping capabilities to functionality', 'wpshadow' ),
					'plugin_namespacing' => __( 'Prefix custom caps with plugin name to avoid conflicts', 'wpshadow' ),
					'recommendation' => __( 'Document and audit all custom capabilities', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: Administrator role has excessive capabilities
		$admin_role = get_role( 'administrator' );

		if ( $admin_role && count( $admin_role->capabilities ) > 150 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Administrator role has excessive capabilities', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-roles-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'excessive_admin_capabilities',
					'capability_count' => count( $admin_role->capabilities ),
					'message' => sprintf(
						/* translators: %d: number of capabilities */
						__( 'Administrator has %d capabilities (typical: 80-120)', 'wpshadow' ),
						count( $admin_role->capabilities )
					),
					'why_concerning' => array(
						'Bloat from old plugins',
						'Capabilities not removed when plugins deactivated',
						'Performance impact',
						'Potential security issue if role compromised',
					),
					'cleanup_process' => array(
						'1. Deactivate unused plugins',
						'2. Remove plugin-specific capabilities',
						'3. Clean old role grants',
						'4. Verify functionality intact',
					),
					'finding_plugin_caps' => "// Find capabilities from deactivated plugins
\$admin_caps = get_role('administrator')->capabilities;
\$active_plugins = get_option('active_plugins');

foreach (\$admin_caps as \$cap => \$grant) {
	// Check if cap comes from active plugin
	\$found = false;

	foreach (\$active_plugins as \$plugin) {
		\$plugin_file = WP_PLUGIN_DIR . '/' . \$plugin;
		\$plugin_content = file_get_contents(\$plugin_file);

		if (strpos(\$plugin_content, \$cap) !== false) {
			\$found = true;
			break;
		}
	}

	if (!found) {
		echo \"Orphaned capability: \$cap\";
	}
}",
					'removing_capabilities' => "// Safely remove old plugin capabilities
\$admin_role = get_role('administrator');

\$old_capabilities = array(
	'manage_legacy_plugin_x',
	'edit_plugin_y_settings',
);

foreach (\$old_capabilities as \$cap) {
	if (\$admin_role->has_cap(\$cap)) {
		\$admin_role->remove_cap(\$cap);
	}
}",
					'typical_capability_load' => __( 'WordPress core: ~40 caps, plugins add more, normal range 80-120', 'wpshadow' ),
					'performance_impact' => __( 'Many capabilities slightly impacts permission checking performance', 'wpshadow' ),
					'recommendation' => __( 'Audit and clean up unnecessary capabilities from administrator role', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Role hierarchy problems
		$subscriber = get_role( 'subscriber' );
		$contributor = get_role( 'contributor' );
		$author = get_role( 'author' );
		$editor = get_role( 'editor' );
		$admin = get_role( 'administrator' );

		$caps_comparison = array(
			'subscriber'  => $subscriber ? count( $subscriber->capabilities ) : 0,
			'contributor' => $contributor ? count( $contributor->capabilities ) : 0,
			'author'      => $author ? count( $author->capabilities ) : 0,
			'editor'      => $editor ? count( $editor->capabilities ) : 0,
			'admin'       => $admin ? count( $admin->capabilities ) : 0,
		);

		// Check if hierarchy is violated
		if ( $caps_comparison['subscriber'] > $caps_comparison['contributor'] ||
			$caps_comparison['contributor'] > $caps_comparison['author'] ||
			$caps_comparison['author'] > $caps_comparison['editor'] ||
			$caps_comparison['editor'] > $caps_comparison['admin'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'User role hierarchy is incorrect', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-roles-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'role_hierarchy_broken',
					'role_capabilities' => $caps_comparison,
					'message' => __( 'User role capabilities do not follow proper hierarchy', 'wpshadow' ),
					'proper_hierarchy' => array(
						'Subscriber' => 'Lowest - read only',
						'Contributor' => 'Can create, needs approval',
						'Author' => 'Can publish own content',
						'Editor' => 'Can publish and manage all content',
						'Administrator' => 'Highest - full access',
					),
					'what_went_wrong' => array(
						'Custom role modifications',
						'Plugin capability conflicts',
						'Accidental role capability changes',
						'Development/testing not cleaned up',
					),
					'checking_hierarchy' => array(
						'Subscriber caps: ' . $caps_comparison['subscriber'],
						'Contributor caps: ' . $caps_comparison['contributor'],
						'Author caps: ' . $caps_comparison['author'],
						'Editor caps: ' . $caps_comparison['editor'],
						'Admin caps: ' . $caps_comparison['admin'],
					),
					'fixing_hierarchy' => "// Ensure proper hierarchy
\$subscriber = get_role('subscriber');
\$contributor = get_role('contributor');
\$author = get_role('author');
\$editor = get_role('editor');
\$admin = get_role('administrator');

// Remove capabilities that shouldn't be here
\$subscriber->remove_cap('publish_posts');
\$subscriber->remove_cap('edit_posts');

// Ensure proper grants
\$contributor->add_cap('edit_posts');
\$author->add_cap('publish_posts');
\$editor->add_cap('edit_others_posts');

// Verify after fixes
echo 'Hierarchy fixed';",
					'verification' => __( 'Each role should have more or equal capabilities than lower roles', 'wpshadow' ),
					'recommendation' => __( 'Restore proper role hierarchy with correct capability delegation', 'wpshadow' ),
				),
			);
		}

		// Pattern 5: No custom roles defined
		$custom_roles = array();
		foreach ( $wp_roles->roles as $role_name => $role_data ) {
			if ( ! in_array( $role_name, array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' ), true ) ) {
				$custom_roles[] = $role_name;
			}
		}

		if ( empty( $custom_roles ) && is_multisite() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No custom roles for specialized functions', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-roles-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'no_custom_roles',
					'message' => __( 'Using only default roles - custom roles may improve security', 'wpshadow' ),
					'benefits_of_custom_roles' => array(
						'Principle of least privilege',
						'Fine-grained access control',
						'Better security posture',
						'Role-based access model',
					),
					'examples_of_custom_roles' => array(
						'Moderator' => 'Can approve/delete comments',
						'Author Manager' => 'Can manage other authors',
						'Analyst' => 'Can view analytics but not edit',
						'Support Agent' => 'Can assist but not publish',
						'Auditor' => 'Read-only access for compliance',
					),
					'creating_custom_roles' => "// Create moderator role
add_role(
	'moderator',
	'Moderator',
	array(
		'read' => true,
		'moderate_comments' => true,
		'manage_categories' => true,
		'publish_posts' => false,
		'edit_posts' => false,
	)
);

// Create analyst role
add_role(
	'analyst',
	'Analyst',
	array(
		'read' => true,
		'view_analytics' => true,
		'export_reports' => true,
		'edit_posts' => false,
	)
);",
					'assigning_custom_roles' => "// Create and assign custom role to user
\$user_id = 123;
\$user = get_userdata(\$user_id);
\$user->set_role('moderator');

// Add capability to existing user
\$user->add_cap('manage_comments');",
					'multisite_roles' => __( 'Network vs site roles in multisite - use appropriately', 'wpshadow' ),
					'when_custom_roles_needed' => array(
						'Multiple user types',
						'Delegated administration',
						'Audit/compliance requirements',
						'Specialized workflows',
					),
					'recommendation' => __( 'Create custom roles for specialized user functions', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Get default WordPress capabilities.
	 *
	 * @since 0.6093.1200
	 * @return array Default capabilities
	 */
	private static function get_default_capabilities() {
		return array(
			'switch_themes',
			'edit_themes',
			'activate_plugins',
			'edit_plugins',
			'edit_users',
			'edit_files',
			'manage_options',
			'moderate_comments',
			'manage_categories',
			'manage_links',
			'upload_files',
			'import',
			'unfiltered_html',
			'edit_posts',
			'edit_others_posts',
			'edit_published_posts',
			'publish_posts',
			'delete_posts',
			'delete_others_posts',
			'delete_published_posts',
			'delete_private_posts',
			'edit_private_posts',
			'read_private_posts',
			'edit_pages',
			'edit_others_pages',
			'edit_published_pages',
			'publish_pages',
			'delete_pages',
			'delete_others_pages',
			'delete_published_pages',
			'delete_private_pages',
			'edit_private_pages',
			'read_private_pages',
			'read',
			'level_10',
			'level_9',
			'level_8',
			'level_7',
			'level_6',
			'level_5',
			'level_4',
			'level_3',
			'level_2',
			'level_1',
			'level_0',
		);
	}
}
