$(document).ready(function() {
    var $slider = $('#fullpage');
    var slides = [];

    $slider.fullpage();

    function loadNextScreen() {
        getSlide(slides, function() {
            var active = $('.section.active').index();
            var $content = $(slides[active]);
            $slider.append( $content );

            // rebuild not working right..
            // destroy and re-init plugin
            // move to last index
            $.fn.fullpage.destroy('all');
            $slider.fullpage();
            $.fn.fullpage.silentMoveTo(active + 1);
            $.fn.fullpage.moveSectionDown();
        });

    }

    $('body').on('click', 'button.start', function(evt) {
        evt.preventDefault();
        var name = $('.splash-screen input[name="name"]').val();
        if (!name) {
            // show error message
            return false;
        }
        $(evt.target).hide();
        loadNextScreen();
    });

    $('body').on('click', 'button.continue', function(evt) {
        evt.preventDefault();
        loadNextScreen();
    });
});