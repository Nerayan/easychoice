<script>
    jQuery(document).ready(function () {
        var button = $("<button class='button button-primary' type='button' style='margin-left: 5px; margin-right: 10px'>Media Library</button>");
        button.on('click', function(e) {
           e.preventDefault();

            var selector = wp.media({
                title: 'Select an audio file to use',
                button: {
                    text: 'Use this audio file',
                },
                library: { type: "audio" },
                multiple: false	// Set to true to allow multiple files to be selected
            });

            selector.on('select', function() {
                console.log(selector.state().get('selection').first());

                $('#powerpress_url_podcast').val(selector.state().get('selection').first().get('url'));
            });

            selector.open();

           return false;
        });

        $('#powerpress_url_podcast').css({width: '60%'});
        button.insertAfter($('#powerpress_url_podcast'));
    });
</script>