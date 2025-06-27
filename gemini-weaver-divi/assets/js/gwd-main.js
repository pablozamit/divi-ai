
(function($){
    console.log('Gemini Weaver Divi loaded');

    function fetchHistory() {
        var postId = $('#gwd-post-id').val();
        $.ajax({
            method: 'POST',
            url: gwd_ajax.ajax_url,
            data: {
                action: 'gwd_get_history',
                post_id: postId,
                nonce: gwd_ajax.nonce
            },
            success: function(response) {
                var container = $('#gwd-history-container');
                container.empty();
                if (response.status === 'success') {
                    var history = response.history || [];
                    if (history.length === 0) {
                        container.append('<p>No history yet.</p>');
                    } else {
                        history.forEach(function(item) {
                            var escapedPrompt = $('<div>').text(item.prompt).html();
                            var row = '<div class="gwd-history-item">' +
                                '<p><strong>' + item.timestamp + '</strong></p>' +
                                '<p>' + escapedPrompt + '</p>' +
                                '<p><button class="button gwd-reapply" data-prompt="' + escapedPrompt.replace(/"/g, '&quot;') + '">Re-apply</button></p>' +
                                '<hr></div>';
                            container.append(row);
                        });
                    }
                } else if (response.message) {
                    container.append('<p>' + response.message + '</p>');
                }
            }
        });
    }

    function getSelectedModule() {
        if (window.ETBuilderAPI && typeof ETBuilderAPI.getActiveModule === 'function') {
            var mod = ETBuilderAPI.getActiveModule();
            if (mod && mod.model) {
                return {
                    id: mod.model.attributes.id || '',
                    shortcode: mod.model.get('rawContent') || ''
                };
            }
        }
        var $active = $('.et-fb-module--active, .et_fb_selected_module').first();
        if ($active.length) {
            return {
                id: $active.data('modelId') || '',
                shortcode: $active.data('shortcode') || ''
            };
        }
        return null;
    }

    $('#gwd-submit-prompt').on('click', function() {
        var prompt = $('#gwd-prompt-input').val();
        var postId = $('#gwd-post-id').val();
        var selected = getSelectedModule();
        $('#gwd-status').text('Procesando...');

        $.ajax({
            method: 'POST',
            url: gwd_ajax.ajax_url,
            data: {
                action: 'gwd_process_prompt',
                prompt: prompt,
                post_id: postId,
                element_id: selected ? selected.id : '',
                element_shortcode: selected ? selected.shortcode : '',
                nonce: gwd_ajax.nonce
            },
            success: function(response) {
                console.log(response);
                $('#gwd-status').text('');
                if (response.status === 'success') {
                    var shortcode = response.shortcode || '';
                    var html = response.preview_html || '';
                    $('#gwd-preview-content').val(shortcode);
                    $('#gwd-visual-preview-container').html(html);
    $('#gwd-preview-container').show();
    fetchHistory();
                } else if (response.message) {
                    $('#gwd-status').text(response.message);
                }
            },
            error: function() {
                $('#gwd-status').text('Error al procesar el prompt.');
            }
        });
    });

    $('#gwd-apply-shortcode').on('click', function() {
        var shortcode = $('#gwd-preview-content').val();
        jQuery('#content').val(shortcode);
        $('#gwd-preview-container').hide();
    });

    $('#gwd-cancel-shortcode').on('click', function() {
        $('#gwd-preview-content').val('');
        $('#gwd-visual-preview-container').empty();
        $('#gwd-preview-container').hide();
    });

    $('#gwd-toggle-visual-preview').on('click', function() {
        $('#gwd-visual-preview-container').toggle();
    });

    $('#gwd-history-toggle').on('click', function() {
        $('#gwd-history-container').toggle();
        if (!$('#gwd-history-container').data('loaded')) {
            fetchHistory();
            $('#gwd-history-container').data('loaded', true);
        }
    });

    $(document).on('click', '.gwd-reapply', function() {
        var prompt = $(this).data('prompt');
        $('#gwd-prompt-input').val(prompt);
        $('#gwd-history-container').hide();
    });
})(jQuery);
