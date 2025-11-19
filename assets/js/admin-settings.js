/**
 * Admin script for the Inbio Hire Me Popup settings page.
 *
 * Handles media uploader integration for selecting a profile picture and
 * updates the preview and hidden input fields accordingly. When the remove
 * button is clicked the preview is cleared and the hidden input is reset.
 */
(function($){
    $(function(){
        var frame;
        $('#inbio_profile_upload_btn').on('click', function(e){
            e.preventDefault();
            // If media frame already exists, reopen it.
            if (frame) {
                frame.open();
                return;
            }
            frame = wp.media({
                title: 'Select or Upload Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false
            });
            frame.on('select', function(){
                var attachment = frame.state().get('selection').first().toJSON();
                $('#inbio_profile_preview').attr('src', attachment.url).show();
                $('#inbio_profile_pic').val(attachment.id);
                $('#inbio_profile_remove_btn').show();
            });
            frame.open();
        });
        $('#inbio_profile_remove_btn').on('click', function(e){
            e.preventDefault();
            $('#inbio_profile_preview').attr('src', '').hide();
            $('#inbio_profile_pic').val('');
            $(this).hide();
        });
    });
})(jQuery);