/**
 * WPS Video Library Scripts
 *
 * @package WPSHADOW_WP_SUPPORT
 * @since 1.2601.73002
 */

(function($) {
	'use strict';

	const VideoLibrary = {
		init() {
			// Check service status on page load
			this.checkServiceStatus();

			// Event handlers
			$('.wps-generate-video, .wps-regenerate-video').on('click', this.generateVideo.bind(this));
			$('.wps-watch-video').on('click', this.watchVideo.bind(this));
			$('.wps-download-video').on('click', this.downloadVideo.bind(this));
			$('.wps-embed-video').on('click', this.showEmbedCode.bind(this));
			$('.wps-modal-close').on('click', this.closeModal.bind(this));
			$(window).on('click', this.closeModalOutside.bind(this));
			$('#wps-copy-embed-code').on('click', this.copyEmbedCode.bind(this));
		},

		checkServiceStatus() {
			// Only check if there are generate buttons (service configured)
			if ($('.wps-generate-video').length === 0) {
				return;
			}

			$.ajax({
				url: wpsVideoLibrary.ajax_url,
				method: 'POST',
				data: {
					action: 'wpshadow_check_video_service',
					nonce: wpsVideoLibrary.nonce
				},
				success(response) {
					if (!response.success) {
						// Show warning if service is offline
						if (response.data && response.data.status === 'offline') {
							$('.wps-video-library-page h1').after(
								'<div class="notice notice-warning"><p>' +
								'<strong>Warning:</strong> Video generation service appears to be offline. ' +
								'Generated videos may fail until the service is restored.' +
								'</p></div>'
							);
						}
					}
				}
			});
		},

		generateVideo(e) {
			const $button = $(e.currentTarget);
			const videoId = $button.data('video-id');
			const $card = $button.closest('.wps-video-card');
			const isRegenerate = $button.hasClass('wps-regenerate-video');

			// Confirm regeneration
			if (isRegenerate && !confirm('Regenerate this video? The current version will be replaced.')) {
				return;
			}

			$.ajax({
				url: wpsVideoLibrary.ajax_url,
				method: 'POST',
				data: {
					action: 'wpshadow_generate_video',
					nonce: wpsVideoLibrary.nonce,
					video_id: videoId
				},
				beforeSend() {
					$card.addClass('loading');
					$button.prop('disabled', true);
					if (isRegenerate) {
						$button.find('.dashicons').removeClass('dashicons-update').addClass('dashicons-update-alt');
					}
				},
				success(response) {
					if (response.success) {
						$card.addClass('success');
						setTimeout(() => {
							// Reload to show updated video card
							location.reload();
						}, 1500);
					} else {
						$card.addClass('error');
						const message = response.data && response.data.type === 'not_configured'
							? wpsVideoLibrary.strings.serviceOffline
							: wpsVideoLibrary.strings.generationError;
						
						this.showNotice($card, message, 'error');
					}
				}.bind(this),
				error() {
					$card.addClass('error');
					this.showNotice($card, wpsVideoLibrary.strings.generationError, 'error');
				}.bind(this),
				complete() {
					$card.removeClass('loading');
					$button.prop('disabled', false);
					if (isRegenerate) {
						$button.find('.dashicons').removeClass('dashicons-update-alt').addClass('dashicons-update');
					}
				}
			});
		},

		watchVideo(e) {
			const $button = $(e.currentTarget);
			const videoId = $button.data('video-id');
			
			// In a real implementation, this would load the video player
			// For now, show a placeholder message
			$('#wps-video-player').html(
				'<div style="color: #fff; text-align: center; padding: 50px;">' +
				'<p style="font-size: 18px; margin-bottom: 20px;">Video Player</p>' +
				'<p>Video ID: ' + videoId + '</p>' +
				'<p style="margin-top: 20px; font-size: 14px; opacity: 0.8;">' +
				'In a production environment, the actual video would be loaded here from the video generation service.' +
				'</p>' +
				'</div>'
			);
			
			this.openModal('#wps-video-modal');
		},

		downloadVideo(e) {
			const $button = $(e.currentTarget);
			const videoId = $button.data('video-id');

			$.ajax({
				url: wpsVideoLibrary.ajax_url,
				method: 'POST',
				data: {
					action: 'wpshadow_download_video',
					nonce: wpsVideoLibrary.nonce,
					video_id: videoId
				},
				beforeSend() {
					$button.prop('disabled', true);
					$button.find('.dashicons').addClass('spin-animation');
				},
				success(response) {
					if (response.success && response.data.download_url) {
						// Trigger download
						window.location.href = response.data.download_url;
					} else {
						alert('Failed to get download URL. Please try again.');
					}
				},
				error() {
					alert('Download failed. Please try again.');
				},
				complete() {
					$button.prop('disabled', false);
					$button.find('.dashicons').removeClass('spin-animation');
				}
			});
		},

		showEmbedCode(e) {
			const $button = $(e.currentTarget);
			const videoId = $button.data('video-id');

			$.ajax({
				url: wpsVideoLibrary.ajax_url,
				method: 'POST',
				data: {
					action: 'wpshadow_get_video_embed',
					nonce: wpsVideoLibrary.nonce,
					video_id: videoId
				},
				beforeSend() {
					$button.prop('disabled', true);
				},
				success(response) {
					if (response.success && response.data.embed_code) {
						$('#wps-embed-code').val(response.data.embed_code);
						this.openModal('#wps-embed-modal');
					} else {
						alert('Failed to get embed code. Please try again.');
					}
				}.bind(this),
				error() {
					alert('Request failed. Please try again.');
				},
				complete() {
					$button.prop('disabled', false);
				}
			});
		},

		copyEmbedCode(e) {
			const $textarea = $('#wps-embed-code');
			const $button = $(e.currentTarget);
			
			$textarea.select();
			document.execCommand('copy');
			
			const originalText = $button.text();
			$button.text('Copied!').addClass('button-primary');
			
			setTimeout(() => {
				$button.text(originalText).removeClass('button-primary');
			}, 2000);
		},

		openModal(selector) {
			$(selector).fadeIn(300);
		},

		closeModal(e) {
			$(e.target).closest('.wps-modal').fadeOut(300);
		},

		closeModalOutside(e) {
			if ($(e.target).hasClass('wps-modal')) {
				$(e.target).fadeOut(300);
			}
		},

		showNotice($card, message, type) {
			const $notice = $('<div class="notice notice-' + type + '" style="margin-top: 10px;"><p>' + message + '</p></div>');
			$card.find('.wps-video-actions').before($notice);
			
			setTimeout(() => {
				$notice.fadeOut(300, function() {
					$(this).remove();
				});
				$card.removeClass('error success');
			}, 5000);
		}
	};

	$(document).ready(() => VideoLibrary.init());

})(jQuery);
