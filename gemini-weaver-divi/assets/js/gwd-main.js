
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

    $('#gwd-submit-prompt').on('click', function() {
        var prompt = $('#gwd-prompt-input').val();
        var postId = $('#gwd-post-id').val();
        $('#gwd-status').text('Procesando...');

        $.ajax({
            method: 'POST',
            url: gwd_ajax.ajax_url,
            data: {
                action: 'gwd_process_prompt',
                prompt: prompt,
                post_id: postId,
                nonce: gwd_ajax.nonce
            },
            success: function(response) {
                console.log(response);
                $('#gwd-status').text('');
                if (response.status === 'success') {
                    var shortcode = response.shortcode || '';
                    $('#gwd-preview-content').val(shortcode);
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
        $('#gwd-preview-container').hide();
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
