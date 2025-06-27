(function($){
    console.log('Gemini Weaver Divi loaded');

    $('#gwd-submit-prompt').on('click', function() {
        var prompt = $('#gwd-prompt-input').val();
        $('#gwd-status').text('Procesando...');

        $.ajax({
            method: 'POST',
            url: gwd_ajax.ajax_url,
            data: {
                action: 'gwd_process_prompt',
                prompt: prompt,
                nonce: gwd_ajax.nonce
            },
            success: function(response) {
                console.log(response);
                $('#gwd-status').text('');
                if (response.status === 'success') {
                    var shortcode = response.shortcode || '';
                    var $editor = jQuery('#content');
                    $editor.val($editor.val() + "\n" + shortcode);
                } else if (response.message) {
                    $('#gwd-status').text(response.message);
                }
            },
            error: function() {
                $('#gwd-status').text('Error al procesar el prompt.');
            }
        });
    });
})(jQuery);
