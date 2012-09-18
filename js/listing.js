var ck_mode = false;

/* This is so when control is held down extra commands appear */
$(function(){
    $('body').keydown(function(e) {
        if (e.ctrlKey && !ck_mode)
        {
            ck_mode = true;
            $('.ck_remove').show();
            $('.hidden_options').show();
        }
    });

    $('body').keyup(function(e) {
        if (ck_mode)
        {
            ck_mode = false;
            $('.ck_remove:not(:checked)').each(function(){
                $(this).hide();
            });

            if ($('.selection_options').is(':hidden'))
            {
                $('.hidden_options').hide();
            }
        }
    });

    $('.select_all').click(function(){
        $('.ck_remove').click();
        $('.ck_remove').change();
    });

    $('.ck_remove').change(function(){
        if (!this.checked && !ck_mode)
        {
            $(this).hide();
        }
        if ( $('.ck_remove:checked').length > 0 )
        {
            $('.selection_options').show();
        }
        else
        {
            $('.selection_options').hide();

            if (!ck_mode) {
                $('.hidden_options').hide();
            }
        }
    });

    $('tbody tr:odd').addClass('odd');
    $('tbody tr:even').addClass('even');

    $('.pagination_curpage').keydown(function(e){
        

        if (e.keyCode == 13)
        {
            if (parseFloat($(this).val()) <= parseFloat($('.pagination_total').text())  )
            {
                window.location = (baseurl + '/page:' + $(this).val());
            }
        }
    });

    $('.pagination_curpage').keyup(function(){
        $(this).val( $(this).val().replace(/[^0-9]/g, '') );

        if ($(this).val() === '')
        {
            $(this).val(curpage);
        }
    });
});