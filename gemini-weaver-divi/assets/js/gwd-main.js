(function($){
    console.log('Gemini Weaver Divi loaded');

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
})(jQuery);
