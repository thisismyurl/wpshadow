<?php
/**
 * Dashboard Customization Manager
 *
 * Allows users to customize which categories appear on their dashboard.
 * Respects user preferences while maintaining helpful defaults.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Core;

/**
 * Manages dashboard category visibility and customization
 */
class Dashboard_Customization {
	
	/**
	 * Get user's dashboard category preferences
	 *
	 * @param int $user_id User ID (defaults to current user).
	 * @return array Categories with visibility status.
	 */
	public static function get_user_preferences( $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		$prefs = get_user_meta( $user_id, 'wpshadow_dashboard_prefs', true );
		
		if ( ! is_array( $prefs ) ) {
			$prefs = self::get_default_preferences();
		}
		
		return $prefs;
	}
	
	/**
	 * Get default dashboard preferences (all visible)
	 *
	 * @return array Default category preferences.
	 */
	public static function get_default_preferences() {
		return array(
			'security'           => array( 'visible' => true, 'pinned' => false ),
			'performance'        => array( 'visible' => true, 'pinned' => false ),
			'code_quality'       => array( 'visible' => true, 'pinned' => false ),
			'seo'                => array( 'visible' => true, 'pinned' => false ),
			'design'             => array( 'visible' => true, 'pinned' => false ),
			'settings'           => array( 'visible' => true, 'pinned' => false ),
			'monitoring'         => array( 'visible' => true, 'pinned' => false ),
			'workflows'          => array( 'visible' => true, 'pinned' => false ),
			'wordpress_health'   => array( 'visible' => true, 'pinned' => false ),
		);
	}
	
	/**
	 * Save user preferences
	 *
	 * @param array $prefs User preferences.
	 * @param int   $user_id User ID (defaults to current user).
	 * @return bool Success status.
	 */
	public static function save_user_preferences( $prefs, $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		// Validate preferences structure
		$validated = self::validate_preferences( $prefs );
		
		return (bool) update_user_meta( $user_id, 'wpshadow_dashboard_prefs', $validated );
	}
	
	/**
	 * Validate preference structure
	 *
	 * @param array $prefs User preferences to validate.
	 * @return array Validated preferences.
	 */
	private static function validate_preferences( $prefs ) {
		$defaults = self::get_default_preferences();
		$validated = array();
		
		foreach ( $defaults as $category => $default_value ) {
			if ( isset( $prefs[ $category ] ) ) {
				$validated[ $category ] = array(
					'visible' => isset( $prefs[ $category ]['visible'] ) ? (bool) $prefs[ $category ]['visible'] : true,
					'pinned'  => isset( $prefs[ $category ]['pinned'] ) ? (bool) $prefs[ $category ]['pinned'] : false,
				);
			} else {
				$validated[ $category ] = $default_value;
			}
		}
		
		return $validated;
	}
	
	/**
	 * Toggle category visibility
	 *
	 * @param string $category Category name.
	 * @param int    $user_id User ID (defaults to current user).
	 * @return bool New visibility state.
	 */
	public static function toggle_category_visibility( $category, $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		$prefs = self::get_user_preferences( $user_id );
		
		if ( isset( $prefs[ $category ] ) ) {
			$prefs[ $category ]['visible'] = ! $prefs[ $category ]['visible'];
			self::save_user_preferences( $prefs, $user_id );
			return $prefs[ $category ]['visible'];
		}
		
		return false;
	}
	
	/**
	 * Pin/unpin category
	 *
	 * @param string $category Category name.
	 * @param bool   $pinned   Pin status.
	 * @param int    $user_id User ID (defaults to current user).
	 * @return bool Success status.
	 */
	public static function set_category_pinned( $category, $pinned, $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		$prefs = self::get_user_preferences( $user_id );
		
		if ( isset( $prefs[ $category ] ) ) {
			$prefs[ $category ]['pinned'] = (bool) $pinned;
			return self::save_user_preferences( $prefs, $user_id );
		}
		
		return false;
	}
	
	/**
	 * Get filtered categories based on user preferences
	 *
	 * @param array $all_categories All available categories with metadata.
	 * @param int   $user_id User ID (defaults to current user).
	 * @return array Filtered and sorted categories (pinned first).
	 */
	public static function get_filtered_categories( $all_categories, $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		$prefs = self::get_user_preferences( $user_id );
		$filtered = array();
		$pinned = array();
		
		foreach ( $all_categories as $key => $category_data ) {
			if ( isset( $prefs[ $key ]['visible'] ) && $prefs[ $key ]['visible'] ) {
				if ( isset( $prefs[ $key ]['pinned'] ) && $prefs[ $key ]['pinned'] ) {
					$pinned[ $key ] = $category_data;
				} else {
					$filtered[ $key ] = $category_data;
				}
			}
		}
		
		// Return pinned first, then regular
		return array_merge( $pinned, $filtered );
	}
	
	/**
	 * Render customization UI (settings panel)
	 *
	 * @return void Outputs HTML directly.
	 */
	public static function render_settings_panel() {
		if ( ! current_user_can( 'read' ) ) {
			return;
		}
		
		$prefs = self::get_user_preferences();
		$categories = array(
			'security'           => __( 'Security', 'wpshadow' ),
			'performance'        => __( 'Performance', 'wpshadow' ),
			'code_quality'       => __( 'Code Quality', 'wpshadow' ),
			'seo'                => __( 'SEO', 'wpshadow' ),
			'design'             => __( 'Design', 'wpshadow' ),
			'settings'           => __( 'Settings', 'wpshadow' ),
			'monitoring'         => __( 'Monitoring', 'wpshadow' ),
			'workflows'          => __( 'Workflows', 'wpshadow' ),
			'wordpress_health'   => __( 'WordPress Site Health', 'wpshadow' ),
		);
		?>
		<div class="wps-card wps-mt-5">
			<div class="wps-card-header">
				<h3 class="wps-card-title" class="wps-m-0">
					<?php esc_html_e( 'Customize Your Dashboard', 'wpshadow' ); ?>
				</h3>
			</div>
			<p class="wps-text-muted">
				<?php esc_html_e( 'Choose which categories to display and pin the most important ones to the top.', 'wpshadow' ); ?>
			</p>
			
			<div class="wps-grid wps-grid-auto-250 wps-gap-4 wps-mt-4">
				<?php foreach ( $categories as $key => $label ) : ?>
					<div class="wps-flex wps-items-center wps-gap-3" class="wps-p-12-rounded-var(--wps-radius-md)">
						<input 
							type="checkbox" 
							class="wpshadow-category-toggle" 
							data-category="<?php echo esc_attr( $key ); ?>"
							<?php checked( $prefs[ $key ]['visible'] ?? true ); ?>
							style="cursor: pointer; width: 18px; height: 18px;"
						/>
						<label class="wps-m-0">
							<?php echo esc_html( $label ); ?>
						</label>
						<button 
							class="wpshadow-category-pin" 
							data-category="<?php echo esc_attr( $key ); ?>"
							style="background: none; border: none; cursor: pointer; font-size: 18px; padding: 0; opacity: <?php echo ( $prefs[ $key ]['pinned'] ?? false ) ? '1' : '0.3'; ?>;"
							title="<?php esc_attr_e( 'Pin to top', 'wpshadow' ); ?>"
						>
							📌
						</button>
					</div>
				<?php endforeach; ?>
			</div>
			
			<button id="wpshadow-save-customization" class="wps-btn wps-btn-primary wps-mt-4">
				<?php esc_html_e( 'Save Preferences', 'wpshadow' ); ?>
			</button>
		</div>
		
		<script>
		document.addEventListener( 'DOMContentLoaded', function() {
			const toggles = document.querySelectorAll( '.wpshadow-category-toggle' );
			const pins = document.querySelectorAll( '.wpshadow-category-pin' );
			const saveBtn = document.getElementById( 'wpshadow-save-customization' );
			const pinStates = {};
			
			toggles.forEach( toggle => {
				const category = toggle.dataset.category;
				pinStates[ category ] = {
					visible: toggle.checked,
					pinned: toggle.closest( 'div' ).querySelector( '.wpshadow-category-pin' ).style.opacity === '1'
				};
			});
			
			pins.forEach( pin => {
				pin.addEventListener( 'click', function( e ) {
					e.preventDefault();
					const category = this.dataset.category;
					const currentOpacity = this.style.opacity || '0.3';
					this.style.opacity = currentOpacity === '1' ? '0.3' : '1';
					pinStates[ category ].pinned = this.style.opacity === '1';
				});
			});
			
			toggles.forEach( toggle => {
				toggle.addEventListener( 'change', function() {
					const category = this.dataset.category;
					pinStates[ category ].visible = this.checked;
				});
			
			saveBtn.addEventListener( 'click', function() {
				wp.ajax.post( 'wpshadow_save_dashboard_prefs', {
					nonce: wpshadow.nonce,
					prefs: pinStates
				}).done( function( response ) {
					alert( '<?php echo esc_js( __( 'Dashboard preferences saved!', 'wpshadow' ) ); ?>' );
					location.reload();
				}).fail( function( error ) {
					alert( 'Error saving preferences: ' + error );
				});
			});
		});
		</script>
		<?php
	}
}
