;(function ($) {
    "use strict";
    $(".wmnw-form-group").on("click",function () {
        var checkbox = $(this).find("input")
        var  wmnw_filed_hidden = $(this).find(".wmnw-filed-show-hide");
        if (checkbox.prop("checked")){
            $(wmnw_filed_hidden).show();
        }else {
            $(wmnw_filed_hidden).hide();
        }
    });
})(jQuery);