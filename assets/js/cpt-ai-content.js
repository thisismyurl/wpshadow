/**
 * CPT AI Content Suggestions
 *
 * Handles AI-powered content suggestions for custom post types.
 *
 * @package    WPShadow
 * @subpackage Assets
 * @since      1.6034.1200
 */

(function($) {
    'use strict';

    /**
     * Initialize AI content features
     */
    function initAIContent() {
        if (!$('.wpshadow-ai-content-box').length) {
            return;
        }

        bindEvents();
    }

    /**
     * Bind event handlers
     */
    function bindEvents() {
        $(document).on('click', '.wpshadow-ai-generate', handleGenerate);
        $(document).on('click', '.wpshadow-ai-apply', handleApply);
        $(document).on('click', '.wpshadow-ai-dismiss', handleDismiss);
    }

    /**
     * Handle AI generation
     */
    function handleGenerate(e) {
        e.preventDefault();

        const type = $('input[name="wpshadow_ai_type"]:checked').val();
        const content = getEditorContent();

        if (!content.trim()) {
            showError(wpShadowAI.i18n.noContent);
            return;
        }

        if (!type) {
            showError(wpShadowAI.i18n.selectType);
            return;
        }

        showLoading();
        hideSuggestion();

        $.ajax({
            url: wpShadowAI.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wpshadow_ai_suggest',
                nonce: wpShadowAI.nonce,
                post_id: $('#post_ID').val(),
                type: type,
                content: content
            },
            success: function(response) {
                if (response.success) {
                    showSuggestion(response.data.suggestion, type);
                } else {
                    showError(response.data.message || wpShadowAI.i18n.generateFailed);
                }
            },
            error: function() {
                showError(wpShadowAI.i18n.generateFailed);
            },
            complete: function() {
                hideLoading();
            }
        });
    }

    /**
     * Handle applying suggestion
     */
    function handleApply(e) {
        e.preventDefault();

        const suggestion = $('.wpshadow-ai-suggestion-text').text();
        const type = $('.wpshadow-ai-suggestion').data('type');

        if (!suggestion) {
            return;
        }

        applyToEditor(suggestion, type);
        hideSuggestion();
        showSuccess(wpShadowAI.i18n.applied);
    }

    /**
     * Handle dismissing suggestion
     */
    function handleDismiss(e) {
        e.preventDefault();
        hideSuggestion();
    }

    /**
     * Get editor content
     */
    function getEditorContent() {
        // Gutenberg editor
        if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
            const blocks = wp.data.select('core/editor').getBlocks();
            return blocks.map(block => block.attributes.content || '').join('\n\n');
        }
        
        // Classic editor
        if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
            return tinymce.activeEditor.getContent({ format: 'text' });
        }
        
        // Fallback to textarea
        return $('#content').val() || '';
    }

    /**
     * Apply suggestion to editor
     */
    function applyToEditor(content, type) {
        // Gutenberg editor
        if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
            if (type === 'improve' || type === 'expand') {
                // Replace existing content
                const blocks = wp.blocks.parse(content);
                wp.data.dispatch('core/block-editor').resetBlocks(blocks);
            } else if (type === 'seo') {
                // Append as new paragraph
                const newBlock = wp.blocks.createBlock('core/paragraph', { content });
                wp.data.dispatch('core/block-editor').insertBlocks(newBlock);
            }
            return;
        }
        
        // Classic editor
        if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
            if (type === 'improve' || type === 'expand') {
                tinymce.activeEditor.setContent(content);
            } else {
                tinymce.activeEditor.execCommand('mceInsertContent', false, '<p>' + content + '</p>');
            }
            return;
        }
        
        // Fallback to textarea
        if (type === 'improve' || type === 'expand') {
            $('#content').val(content);
        } else {
            $('#content').val($('#content').val() + '\n\n' + content);
        }
    }

    /**
     * Show loading indicator
     */
    function showLoading() {
        $('.wpshadow-ai-generate')
            .prop('disabled', true)
            .html('<span class="spinner is-active" style="float:none;margin:0 8px 0 0;"></span>' + 
                  wpShadowAI.i18n.generating);
    }

    /**
     * Hide loading indicator
     */
    function hideLoading() {
        $('.wpshadow-ai-generate')
            .prop('disabled', false)
            .html(wpShadowAI.i18n.generate);
    }

    /**
     * Show suggestion
     */
    function showSuggestion(suggestion, type) {
        const $suggestionBox = $('.wpshadow-ai-suggestion');
        $suggestionBox.data('type', type);
        $suggestionBox.find('.wpshadow-ai-suggestion-text').text(suggestion);
        $suggestionBox.slideDown();
    }

    /**
     * Hide suggestion
     */
    function hideSuggestion() {
        $('.wpshadow-ai-suggestion').slideUp();
    }

    /**
     * Show error message
     */
    function showError(message) {
        const $notice = $('<div class="notice notice-error is-dismissible"><p>' + 
                        message + '</p></div>');
        $('.wpshadow-ai-content-box').prepend($notice);
        
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

    /**
     * Show success message
     */
    function showSuccess(message) {
        const $notice = $('<div class="notice notice-success is-dismissible"><p>' + 
                        message + '</p></div>');
        $('.wpshadow-ai-content-box').prepend($notice);
        
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        initAIContent();
    });

})(jQuery);
