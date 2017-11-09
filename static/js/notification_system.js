NOTIFICATION_SETTINGS = {};

NOTIFICATION_SETTINGS.init = function () {
    $(".notification_right_arrow").on('click', NOTIFICATION_SETTINGS.show_subaction);
    $(".notification_step").on('click', NOTIFICATION_SETTINGS.change_step);
};

NOTIFICATION_SETTINGS.change_step = function (e) {
    let target = $(e.currentTarget);
    target.parent().find(".notification_step").removeClass("selected");
    target.addClass("selected");
};

NOTIFICATION_SETTINGS.show_subaction = function (e)
{
    let target = $(e.currentTarget).parents().eq(2).find(".notification_setting_subaction_panel");
    let target_sub_container = $(e.currentTarget).parents().eq(2).find(".notification_info_subaction_container");

    if(!target[0].open) {
        target.css("animation", "open_subaction 1s forwards");
        $(e.currentTarget).css("animation", "rotate_90_cw 1s forwards");
        target[0].open = true;
        target_sub_container.delay(800).queue(function(next){
          $(this).css("display", "flex");
          next();
        });
    }else{
        target.css("animation", "close_subaction 1s forwards");
        $(e.currentTarget).css("animation", "rotate_90_ccw 1s forwards");
        target[0].open = false;
        target_sub_container.hide();
    }
};

$(document).ready(function(){
    NOTIFICATION_SETTINGS.init();
});