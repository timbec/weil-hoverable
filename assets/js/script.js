(function ($) {
    jQuery(document).ready(function($) {

        $('.hoverbox-popup').magnificPopup({
            type: 'inline',
            preloader: true, 
            closeOnContentClick: true, 
            closeOnBgClick: true, 
            closeBtnInside: true,
            // Delay in milliseconds before popup is removed
            removalDelay: 300,

            // Class that is added to popup wrapper and background
            // make it unique to apply your CSS animations just to this exact popup
            mainClass: 'mfp-fade'
        }); 
    //     $(document).on('click', '.popup-modal-dismiss', function (e) {
	// 	e.preventDefault();
	// 	$.magnificPopup.close();
	// });

        /*Display box in admin panel*/
        $(".hov-add").click(function() {
            $(".hov-container").append($(".hov-template").html());
        });
        $(".hov-remove").live('click', function() {
            $(this).parent().parent().remove();
        });
    });
})(jQuery);