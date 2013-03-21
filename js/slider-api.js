(function($){
    $('#site-key').click(function(){
        $('#site-key').select();
    });

    $('.sliders:first').addClass('active');
    if ($(".sliders").length > 1) {
        $('.sliders').each(function(i){
            var id = $(this).attr('id');
            var label_text = $('.slider-label:first', this).text();
            var label = $('<span />').text(label_text).html();
            if (i === 0) {
                $('#sliders-tab').append(
                    '<li class="active"><a href="#'+id+'" data-toggle="tab"><i class="icon-slider"></i> '+label+'</a></li>'
                );
            } else {
                $('#sliders-tab').append(
                    '<li><a href="#'+id+'" data-toggle="tab"><i class="icon-slider"></i> '+label+'</a></li>'
                );
            }
        });
        if (location.hash) {
            $('#sliders-tab a').each(function(){
                if ($(this).attr('href') === location.hash) {
                    $(this).tab('show');
                }
            });
        }
    }

    if ($('#settings').length) {
        $('input[name="delete-slider"]').change(function(){
            if ($(this).is(':checked')) {
                $('#delete-id').val($(this).val());
                $('#delete-form button[type="submit"]').attr('disabled', false);
            } else {
                $('#delete-id').val('');
                $('#delete-form button[type="submit"]').attr('disabled', true);
            }
        });
        $('#delete-form').submit(function(){
            if (confirm('Are you sure you want to delete ?')) {
                return true;
            } else {
                return false;
            }
        });
    }
})(jQuery);
