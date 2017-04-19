$(document).ready(function(){

    $('.input-thumbs::-webkit-slider-thumb').css('background', '#b2b2b2');
    $('.range input').each(function(){
        $(this).parent().parent().find("li:eq(" + ( $(this).val() - 1) + ")").addClass("active selected");
    });


    $(".notification_checkbox").live("click", function(e){
        var next_td = $(this).parent().parent().next();
        if($(this)[0].checked){
            next_td.removeClass("range-container-disabled");
            next_td.next().removeClass("range-container-disabled");
            next_td.next().find('input').removeAttr('disabled');
        }else{
            next_td.addClass("range-container-disabled");
            next_td.next().addClass("range-container-disabled");
            next_td.next().find('input').attr('disabled', 'disabled');
        }
    });

    var selectRangeInput = function(rangeInput){
        var val = (rangeInput.val() - 1) * 50;
        rangeInput.parent().css('background' , 'linear-gradient(to right, #4CAF50 0%, #4CAF50 ' + val + '%, #fff ' + val + '%, #fff 100%)');
        rangeInput.css('background' , 'linear-gradient(to right, #4CAF50 0%, #4CAF50 ' + val + '%, #4CAF50 ' + val + '%, #4CAF50 100%)');

        rangeInput.parent().parent().find("li").removeClass("active selected");
        rangeInput.parent().parent().find("li:eq(" + (rangeInput.val() - 1) + ")").addClass("active selected");

    };

    $('.range input').on('input', function () {
        selectRangeInput($(this));
    });

    $('#spodnotification_save_settings').live('click', function(e){

        var switches = $('.notification_checkbox');
        var thumbs   = $('.input-thumbs');

        for(var i=0; i < switches.length; i++){
            var params = $(switches[i]).attr('id').split(".");
            $.post(NOTIFICATION.ajax_notification_register_user_for_action,
                {
                    userId    : NOTIFICATION.userId,
                    plugin    : params[0],
                    action    : params[1],
                    type      : "mail",
                    frequency : $(thumbs[i]).val(),
                    status    : $(switches[i])[0].checked
                },
                function (data, status) {}
            );
        }
        OW.info(OW.getLanguageText('spodnotification', 'settings_saved'));
    });

});