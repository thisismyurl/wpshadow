<?php
/**
 * Custom Fields and Post Meta Validation
 *
 * Validates custom field registration and post meta management.
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
 * Diagnostic_Custom_Fields_Meta Class
 *
 * Checks custom field and post meta configuration issues.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Custom_Fields_Meta extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-fields-meta';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Fields and Post Meta';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates custom field registration and post meta management';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'custom-post-types';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get registered meta keys
		$registered_meta = get_registered_meta_keys( 'post' );

		// Pattern 1: Custom fields without proper registration
		$meta_keys = $wpdb->get_col(
			"SELECT DISTINCT meta_key 
			FROM {$wpdb->postmeta} 
			WHERE meta_key NOT LIKE '\_%' 
			AND meta_key NOT IN ('_edit_lock', '_edit_last', '_wp_page_template')
			LIMIT 100"
		);

		$unregistered_meta = array();
		foreach ( $meta_keys as $key ) {
			if ( ! isset( $registered_meta[ $key ] ) ) {
				$unregistered_meta[] = $key;
			}
		}

		if ( count( $unregistered_meta ) > 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom fields used without proper registration', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/custom-fields-meta',
				'details'      => array(
					'issue'                    => 'unregistered_meta',
					'unregistered_keys'        => array_slice( $unregistered_meta, 0, 20 ),
					'total_unregistered'       => count( $unregistered_meta ),
					'message'                  => sprintf(
						/* translators: %d: number of meta keys */
						__( '%d custom field keys used without registration', 'wpshadow' ),
						count( $unregistered_meta )
					),
					'why_registration_matters' => array(
						'Type safety and validation',
						'REST API exposure control',
						'Better documentation',
						'Schema definition',
						'Sanitization callbacks',
					),
					'benefits_of_registration' => array(
						'Automatic sanitization',
						'Type checking',
						'REST API integration',
						'Better queries',
						'Default values',
					),
					'how_to_register'          => "register_post_meta('post', 'project_year', array(
	'type' => 'integer',
	'description' => 'Year the project was completed',
	'single' => true,
	'default' => 2024,
	'sanitize_callback' => 'absint',
	'auth_callback' => function() {
		return current_user_can('edit_posts');
	},
	'show_in_rest' => true,
));",
					'supported_types'          => array(
						'string'  => 'Text values',
						'boolean' => 'True/false',
						'integer' => 'Whole numbers',
						'number'  => 'Decimals',
						'array'   => 'Multiple values',
						'object'  => 'Complex structures',
					),
					'rest_api_integration'     => __( 'show_in_rest => true makes field available in block editor and REST API', 'wpshadow' ),
					'security_benefits'        => __( 'auth_callback controls who can read/write meta values', 'wpshadow' ),
					'code_organization'        => "// Register all meta fields on init
add_action('init', function() {
	register_post_meta('portfolio', 'project_year', array(...));
	register_post_meta('portfolio', 'client_name', array(...));
	register_post_meta('portfolio', 'technologies', array(...));
});",
					'recommendation'           => __( 'Register all custom meta fields with register_post_meta()', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Meta boxes without nonce verification
		global $wp_meta_boxes;
		$meta_box_issues = array();

		if ( ! empty( $wp_meta_boxes ) ) {
			foreach ( $wp_meta_boxes as $post_type => $contexts ) {
				foreach ( $contexts as $context => $priorities ) {
					foreach ( $priorities as $priority => $boxes ) {
						foreach ( $boxes as $box_id => $box ) {
							// Check if custom meta box (not core)
							if ( ! in_array( $box_id, array( 'submitdiv', 'formatdiv', 'categorydiv', 'tagsdiv-post_tag', 'postimagediv' ), true ) ) {
								$meta_box_issues[] = array(
									'id'        => $box_id,
									'title'     => $box['title'],
									'post_type' => $post_type,
								);
							}
						}
					}
				}
			}
		}

		if ( count( $meta_box_issues ) > 3 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom meta boxes may lack security verification', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/custom-fields-meta',
				'details'      => array(
					'issue'                        => 'meta_box_security',
					'meta_boxes'                   => array_slice( $meta_box_issues, 0, 10 ),
					'total_meta_boxes'             => count( $meta_box_issues ),
					'message'                      => sprintf(
						/* translators: %d: number of meta boxes */
						__( '%d custom meta boxes detected - verify security implementation', 'wpshadow' ),
						count( $meta_box_issues )
					),
					'security_requirements'        => array(
						'Nonce verification',
						'Capability checks',
						'Data sanitization',
						'Escaping on output',
					),
					'common_vulnerabilities'       => array(
						'Missing nonce verification' => 'CSRF attacks possible',
						'No capability check'        => 'Unauthorized access',
						'No input sanitization'      => 'XSS attacks',
						'Trusting $_POST directly'   => 'Data injection',
					),
					'secure_meta_box_pattern'      => "// Display meta box
function my_meta_box_html(\$post) {
	// Add nonce field
	wp_nonce_field('my_meta_box_nonce', 'my_meta_box_nonce_field');
	
	// Get current value
	\$value = get_post_meta(\$post->ID, '_my_meta_key', true);
	?>
	<label for=\"my_field\">My Field:</label>
	<input type=\"text\" id=\"my_field\" name=\"my_field\" value=\"<?php echo esc_attr(\$value); ?>\" />
	<?php
}

// Save meta box data
function my_meta_box_save(\$post_id) {
	// Check nonce
	if (!isset(\$_POST['my_meta_box_nonce_field']) || 
		!wp_verify_nonce(\$_POST['my_meta_box_nonce_field'], 'my_meta_box_nonce')) {
		return;
	}
	
	// Check capability
	if (!current_user_can('edit_post', \$post_id)) {
		return;
	}
	
	// Check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	
	// Sanitize and save
	if (isset(\$_POST['my_field'])) {
		\$value = sanitize_text_field(\$_POST['my_field']);
		update_post_meta(\$post_id, '_my_meta_key', \$value);
	}
}
add_action('save_post', 'my_meta_box_save');",
					'nonce_verification_checklist' => array(
						'✓ Add wp_nonce_field() in meta box HTML',
						'✓ Verify nonce with wp_verify_nonce() on save',
						'✓ Check user capabilities with current_user_can()',
						'✓ Skip during autosave (check DOING_AUTOSAVE)',
						'✓ Sanitize input with sanitize_*() functions',
						'✓ Escape output with esc_*() functions',
					),
					'capability_examples'          => array(
						'edit_post'            => 'Can edit specific post',
						'edit_posts'           => 'Can edit posts in general',
						'edit_published_posts' => 'Can edit published posts',
						'manage_options'       => 'Administrator only',
					),
					'sanitization_functions'       => array(
						'sanitize_text_field()'     => 'Single line text',
						'sanitize_textarea_field()' => 'Multi-line text',
						'sanitize_email()'          => 'Email addresses',
						'esc_url_raw()'             => 'URLs',
						'absint()'                  => 'Positive integers',
						'wp_kses_post()'            => 'HTML content',
					),
					'recommendation'               => __( 'Review all custom meta boxes for nonce verification and capability checks', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: Custom fields without REST API support
		$no_rest_support = array();

		foreach ( $registered_meta as $key => $args ) {
			if ( empty( $args['show_in_rest'] ) && ! str_starts_with( $key, '_' ) ) {
				$no_rest_support[] = $key;
			}
		}

		if ( ! empty( $no_rest_support ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom fields not exposed to REST API', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/custom-fields-meta',
				'details'      => array(
					'issue'                    => 'no_rest_support',
					'affected_meta_keys'       => array_slice( $no_rest_support, 0, 20 ),
					'total_affected'           => count( $no_rest_support ),
					'message'                  => sprintf(
						/* translators: %d: number of meta keys */
						__( '%d registered meta fields not available in REST API', 'wpshadow' ),
						count( $no_rest_support )
					),
					'why_rest_api_matters'     => array(
						'Block editor integration',
						'Custom block development',
						'Headless WordPress',
						'Mobile apps',
						'Third-party integrations',
					),
					'block_editor_impact'      => __( 'Meta fields without REST API cannot be edited in block editor sidebar', 'wpshadow' ),
					'enabling_rest_support'    => "register_post_meta('post', 'project_year', array(
	'type' => 'integer',
	'single' => true,
	'show_in_rest' => true, // Enable REST API
));",
					'custom_rest_schema'       => "register_post_meta('post', 'technologies', array(
	'type' => 'array',
	'single' => true,
	'show_in_rest' => array(
		'schema' => array(
			'type' => 'array',
			'items' => array(
				'type' => 'string',
			),
		),
	),
));",
					'security_with_rest_api'   => array(
						'REST API respects auth_callback',
						'Private fields remain private',
						'Capability checks still apply',
					),
					'when_not_to_expose'       => array(
						'Sensitive data (passwords, keys)',
						'Internal system fields',
						'Temporary processing flags',
						'Admin-only configuration',
					),
					'block_editor_integration' => "// Make field editable in block editor sidebar
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { TextControl } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';

registerPlugin('my-plugin', {
	render: () => {
		const [meta, setMeta] = useEntityProp('postType', 'post', 'meta');
		
		return (
			<PluginDocumentSettingPanel
				name=\"my-meta-panel\"
				title=\"Project Details\"
			>
				<TextControl
					label=\"Project Year\"
					value={meta.project_year}
					onChange={(value) => setMeta({...meta, project_year: value})}
				/>
			</PluginDocumentSettingPanel>
		);
	},
});",
					'recommendation'           => __( 'Enable REST API support for fields used in block editor', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Underscore-prefixed meta without proper protection
		$underscore_meta = $wpdb->get_results(
			"SELECT DISTINCT meta_key, COUNT(*) as count 
			FROM {$wpdb->postmeta} 
			WHERE meta_key LIKE '\_%'
			AND meta_key NOT IN ('_edit_lock', '_edit_last', '_wp_page_template', '_thumbnail_id', '_wp_page_template')
			GROUP BY meta_key
			ORDER BY count DESC
			LIMIT 20",
			ARRAY_A
		);

		if ( count( $underscore_meta ) > 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Many underscore-prefixed meta keys in use', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/custom-fields-meta',
				'details'      => array(
					'issue'                            => 'underscore_meta_usage',
					'meta_keys'                        => $underscore_meta,
					'message'                          => sprintf(
						/* translators: %d: number of meta keys */
						__( '%d underscore-prefixed meta keys detected', 'wpshadow' ),
						count( $underscore_meta )
					),
					'what_are_underscore_meta'         => __( 'Meta keys starting with _ are "private" and hidden from custom fields UI', 'wpshadow' ),
					'when_to_use_underscores'          => array(
						'Internal system fields',
						'Plugin/theme configuration',
						'Temporary processing flags',
						'Fields not for manual editing',
					),
					'when_not_to_use_underscores'      => array(
						'User-editable fields',
						'Public-facing data',
						'Content visible to editors',
						'Fields shown in admin',
					),
					'visibility_impact'                => array(
						'With underscore (_)' => 'Hidden from Custom Fields box',
						'Without underscore'  => 'Visible in Custom Fields box',
					),
					'naming_conventions'               => array(
						'_my_plugin_internal' => 'Plugin-specific internal',
						'_cache_data'         => 'Temporary cache data',
						'my_public_field'     => 'User-visible field',
						'client_name'         => 'Editable content field',
					),
					'security_note'                    => __( 'Underscore prefix does NOT provide security - still use auth_callback', 'wpshadow' ),
					'common_wordpress_underscore_meta' => array(
						'_edit_lock'        => 'Post editing lock',
						'_edit_last'        => 'Last editor user ID',
						'_thumbnail_id'     => 'Featured image post ID',
						'_wp_page_template' => 'Page template file',
					),
					'refactoring_example'              => "// If field should be public, remove underscore
// Before:
update_post_meta(\$post_id, '_project_client', \$client);

// After:
update_post_meta(\$post_id, 'project_client', \$client);
register_post_meta('portfolio', 'project_client', array(
	'type' => 'string',
	'single' => true,
	'show_in_rest' => true,
));",
					'recommendation'                   => __( 'Use underscores only for internal/system fields, not user-editable content', 'wpshadow' ),
				),
			);
		}

		// Pattern 5: Serialized data in post meta
		$serialized_meta = $wpdb->get_results(
			"SELECT DISTINCT meta_key, COUNT(*) as count 
			FROM {$wpdb->postmeta} 
			WHERE meta_value LIKE 'a:%' 
			OR meta_value LIKE 'O:%'
			OR meta_value LIKE 's:%'
			GROUP BY meta_key
			HAVING count > 10
			ORDER BY count DESC
			LIMIT 20",
			ARRAY_A
		);

		if ( ! empty( $serialized_meta ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Serialized data stored in post meta', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/custom-fields-meta',
				'details'      => array(
					'issue'                         => 'serialized_meta',
					'affected_meta_keys'            => $serialized_meta,
					'message'                       => sprintf(
						/* translators: %d: number of meta keys */
						__( '%d meta keys storing serialized data', 'wpshadow' ),
						count( $serialized_meta )
					),
					'problems_with_serialization'   => array(
						'Not searchable in database',
						'Cannot be indexed',
						'Hard to query efficiently',
						'Breaks with search/replace',
						'URL changes break serialized data',
					),
					'search_replace_danger'         => __( 'Database URL search/replace breaks serialized data (lengths mismatch)', 'wpshadow' ),
					'better_alternatives'           => array(
						'JSON encoding'      => 'Modern, searchable, easier to work with',
						'Separate meta rows' => 'Each value in own row (queryable)',
						'Custom tables'      => 'For complex relational data',
					),
					'json_vs_serialization'         => array(
						'JSON'          => array(
							'Pros'     => 'Readable, parseable, safer',
							'Cons'     => 'Slightly larger',
							'Use when' => 'Modern code, API exposure',
						),
						'Serialization' => array(
							'Pros'     => 'Compact, preserves types',
							'Cons'     => 'Breaks easily, not searchable',
							'Use when' => 'Legacy compatibility only',
						),
					),
					'migration_example'             => "// From serialization to JSON
\$old_data = get_post_meta(\$post_id, 'old_serialized_field', true);
\$new_data = maybe_unserialize(\$old_data);
update_post_meta(\$post_id, 'new_json_field', wp_json_encode(\$new_data));

// Reading JSON
\$json_data = get_post_meta(\$post_id, 'new_json_field', true);
\$data = json_decode(\$json_data, true);",
					'separate_rows_example'         => "// Instead of serializing array:
\$technologies = array('PHP', 'JavaScript', 'React');
update_post_meta(\$post_id, 'technologies', serialize(\$technologies)); // ❌ Bad

// Store as separate rows:
delete_post_meta(\$post_id, 'technology'); // Clear old
foreach (\$technologies as \$tech) {
	add_post_meta(\$post_id, 'technology', \$tech); // ✓ Queryable
}

// Query posts by technology:
\$posts = get_posts(array(
	'post_type' => 'portfolio',
	'meta_query' => array(
		array(
			'key' => 'technology',
			'value' => 'React',
		),
	),
));",
					'when_serialization_acceptable' => array(
						'Legacy plugin compatibility',
						'Small, truly private data',
						'Never needs searching',
					),
					'recommendation'                => __( 'Use JSON or separate meta rows instead of PHP serialization', 'wpshadow' ),
				),
			);
		}

		// Pattern 6: High meta row count per post
		$high_meta_counts = $wpdb->get_results(
			"SELECT post_id, COUNT(*) as meta_count, p.post_type
			FROM {$wpdb->postmeta} pm
			JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			GROUP BY post_id
			HAVING meta_count > 50
			ORDER BY meta_count DESC
			LIMIT 10",
			ARRAY_A
		);

		if ( ! empty( $high_meta_counts ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Posts with excessive meta field counts', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/custom-fields-meta',
				'details'      => array(
					'issue'                      => 'excessive_meta_rows',
					'high_meta_posts'            => $high_meta_counts,
					'message'                    => sprintf(
						/* translators: %d: number of posts */
						__( '%d posts have more than 50 meta fields', 'wpshadow' ),
						count( $high_meta_counts )
					),
					'why_this_is_problematic'    => array(
						'Slows get_post_meta() calls',
						'Increases database query time',
						'Wastes memory',
						'Slows admin pages',
					),
					'performance_impact'         => __( 'Each meta row adds overhead to post loading (~0.5-2ms per 10 rows)', 'wpshadow' ),
					'common_causes'              => array(
						'Page builders'     => 'Store settings per-block',
						'Form plugins'      => 'One meta row per field',
						'Revision tracking' => 'Duplicate meta per revision',
						'Repeater fields'   => 'Separate row per repeat item',
					),
					'how_much_is_too_much'       => array(
						'< 20 meta rows'   => 'Normal, acceptable',
						'20-50 meta rows'  => 'Moderate, monitor',
						'50-100 meta rows' => 'High, optimize',
						'> 100 meta rows'  => 'Critical, refactor',
					),
					'optimization_strategies'    => array(
						'Consolidation'     => 'Combine related fields into JSON',
						'Custom tables'     => 'Move complex data to dedicated table',
						'Selective loading' => 'Lazy load meta only when needed',
						'Caching'           => 'Cache full post meta arrays',
					),
					'json_consolidation_example' => "// Before: 10 separate meta rows
update_post_meta(\$post_id, 'contact_name', 'John');
update_post_meta(\$post_id, 'contact_email', 'john@example.com');
update_post_meta(\$post_id, 'contact_phone', '555-1234');
// ... 7 more fields

// After: 1 meta row with JSON
\$contact_data = array(
	'name' => 'John',
	'email' => 'john@example.com',
	'phone' => '555-1234',
	// ... all fields
);
update_post_meta(\$post_id, 'contact_data', wp_json_encode(\$contact_data));",
					'custom_table_example'       => "// For complex relational data
CREATE TABLE {$wpdb->prefix}portfolio_projects (
	id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	post_id bigint(20) unsigned NOT NULL,
	client_name varchar(255) NOT NULL,
	project_year year NOT NULL,
	technologies text NOT NULL,
	// ... many more fields
	PRIMARY KEY (id),
	KEY post_id (post_id)
);",
					'when_to_use_custom_tables'  => array(
						'> 20 related fields',
						'Complex queries needed',
						'Relational data',
						'High-performance requirements',
					),
					'recommendation'             => __( 'Consolidate related meta fields or use custom tables for complex data', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}
}
