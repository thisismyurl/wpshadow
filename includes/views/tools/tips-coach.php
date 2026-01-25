<?php

/**
 * Tips & Guidance Tool - Admin Tooltips Configuration
 *
 * Allows admins to enable/disable tooltip categories and see
 * a preview of helpful tips used across wp-admin.
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'read' ) ) {
	wp_die( esc_html__( 'Insufficient permissions.', 'wpshadow' ) );
}

$user_id    = get_current_user_id();
$prefs      = wpshadow_get_user_tip_prefs( $user_id );
$categories = wpshadow_get_tip_categories();
$catalog    = wpshadow_get_tooltip_catalog();

$disabled_categories = $prefs['disabled_categories'] ?? array();
$dismissed_tips      = $prefs['dismissed_tips'] ?? array();

// Organize tips by category
$tips_by_category = array();
foreach ( $catalog as $tip ) {
	$cat = $tip['category'] ?? 'navigation';
	if ( ! isset( $tips_by_category[ $cat ] ) ) {
		$tips_by_category[ $cat ] = array();
	}
	$tips_by_category[ $cat ][] = $tip;
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Tips & Guidance', 'wpshadow' ); ?></h1>
	<p><?php esc_html_e( 'Configure helpful tooltips that appear across WordPress admin. These friendly tips help beginners navigate and understand what each tool does.', 'wpshadow' ); ?></p>

	<div class="wpshadow-tips-toolbar wps-m-20">
		<button id="wpshadow-enable-all-tips" class="button button-secondary" style="margin-right: 10px;">
			<?php esc_html_e( 'Enable All Tips', 'wpshadow' ); ?>
		</button>
		<button id="wpshadow-disable-all-tips" class="button button-secondary">
			<?php esc_html_e( 'Disable All Tips', 'wpshadow' ); ?>
		</button>
	</div>

	<div id="wpshadow-tips-message" class="wps-none-m-20-p-12-rounded-4"></div>

	<div class="wpshadow-tips-settings wps-p-20-rounded-4">
		<h2><?php esc_html_e( 'Tip Categories', 'wpshadow' ); ?></h2>
		<p><?php esc_html_e( 'Check the categories below to enable tooltips. Unchecked categories will hide their tips from appearing in wp-admin.', 'wpshadow' ); ?></p>

		<div class="wpshadow-categories-list" style="margin-top: 20px;">
			<?php
			foreach ( $categories as $cat_slug => $cat_label ) :
				$is_disabled = in_array( $cat_slug, $disabled_categories, true );
				$tips_in_cat = count( $tips_by_category[ $cat_slug ] ?? array() );
				?>
				<div class="wpshadow-category-item wps-p-15-rounded-4">
					<label class="wps-flex-items-center">
						<input
							type="checkbox"
							class="wpshadow-category-toggle"
							data-category="<?php echo esc_attr( $cat_slug ); ?>"
							<?php checked( ! $is_disabled ); ?>
							style="margin-right: 10px; cursor: pointer; width: 18px; height: 18px;" />
						<?php echo esc_html( $cat_label ); ?>
						<span style="color: #666; font-size: 14px; font-weight: normal; margin-left: 10px;">
							(<?php echo esc_html( sprintf( _n( '%d tip', '%d tips', $tips_in_cat, 'wpshadow' ), $tips_in_cat ) ); ?>)
						</span>
					</label>

					<?php if ( ! empty( $tips_by_category[ $cat_slug ] ) ) : ?>
						<div class="wpshadow-tips-preview wps-p-10-rounded-3">
							<strong class="wps-block">
								<?php esc_html_e( 'Tips in this category:', 'wpshadow' ); ?>
							</strong>
							<ul class="wps-m-0">
								<?php foreach ( $tips_by_category[ $cat_slug ] as $tip ) : ?>
									<li style="margin-bottom: 6px;">
										<strong><?php echo esc_html( $tip['title'] ); ?></strong>
										<br />
										<span style="color: #666;"><?php echo esc_html( $tip['message'] ); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="wpshadow-tips-info wps-p-20-rounded-4">
		<h3 style="margin-top: 0;"><?php esc_html_e( 'How Tips Work', 'wpshadow' ); ?></h3>
		<ul class="wps-m-10">
			<li><?php esc_html_e( 'Enabled tips appear as helpful hover tooltips when you move your mouse over menu items, buttons, and other admin elements.', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Each tip can be dismissed individually by clicking the X button, and won\'t reappear for you.', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Disabling a category hides all tips in that category from appearing in wp-admin.', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'These settings apply only to your user account; other users have their own preferences.', 'wpshadow' ); ?></li>
		</ul>
	</div>

</div>

<script>
	(function($) {
		'use strict';

		var wpshadowTipsPage = {
			nonce: <?php echo json_encode( wp_create_nonce( 'wpshadow_tip_prefs' ) ); ?>,

			init: function() {
				$(' . wpshadow - category - toggle').on('change', this.onToggleCategory.bind(this));
				$('#wpshadow-enable-all-tips').on('click', this.enableAllCategories.bind(this));
				$('#wpshadow-disable-all-tips') {
					.on('click', this.disableAllCategories.bind(this));
				}
			},

			onToggleCategory: function(e) {
				var $checkbox = $(e.target);
				var category = $checkbox.data('category');
				var isEnabled = $checkbox.is(':checked');

				this.updateCategory(category, isEnabled);
			},

			updateCategory: function(category, isEnabled) {
				var self = this;
				var disabledCategories = array();

				// Collect all disabled categories
				$('.wpshadow-category-toggle:not(:checked)').each(
					function() {
						disabledCategories.push($(this).data('category'));
					}
				);

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpshadow_save_tip_prefs',
						nonce: this.nonce,
						disabled_categories: disabledCategories,
					},
					success: function(response) {
						if (response.success) {
							self.showMessage(
								isEnabled ?
								< ? php echo json_encode(__('Tip category enabled!', 'wpshadow')); ?
								>
								:
								<?php echo json_encode( __( 'Tip category disabled!', 'wpshadow' ) ); ?>,
								'success'
							);
						} else {
							self.showMessage(<?php echo json_encode( __( 'Error saving preference.', 'wpshadow' ) ); ?>, 'error');
						}
					},
					error: function() {
						self.showMessage(<?php echo json_encode( __( 'Connection error.', 'wpshadow' ) ); ?>, 'error');
					},
				});
			},

			enableAllCategories: function(e) {
				e.preventDefault();
				$('.wpshadow-category-toggle').prop('checked', true).first().trigger('change');
			},

			disableAllCategories: function(e) {
				e.preventDefault();
				$('.wpshadow-category-toggle').prop('checked', false).first().trigger('change');
			},

			showMessage: function(message, type) {
				var $message = $('#wpshadow-tips-message');
				var bgColor = type === 'success' ? '#d4edda' : '#f8d7da';
				var textColor = type === 'success' ? '#155724' : '#721c24';
				var borderColor = type === 'success' ? '#c3e6cb' : '#f5c6cb';

				$message
					.css({
						background: bgColor,
						color: textColor,
						borderColor: borderColor
					})
					.text(message)
					.show();

				setTimeout(function() {
					$message.fadeOut();
				}, 3000);
			},
		};

		$(function() {
			wpshadowTipsPage.init();
		});
	})(jQuery);
</script>
