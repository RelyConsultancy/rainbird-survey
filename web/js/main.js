$(document).ready(function() {
    var $slider = $('#fullpage');
    var slides = [];

    function init() {
        $slider.fullpage({
            onLeave: function(index, nextIndex, direction) {
                var $screen = $('.section:eq(' + (nextIndex - 1) + ')');
                var is_results_screen = $screen.hasClass('results-screen');
                $('header').toggleClass('visible', nextIndex !== 1 && !is_results_screen);
            }
        });
    }

    init();

    function loadNextScreen() {
        getSlide(slides, function() {
            var active = $('.section.active').index();
            var $content = $(slides[active]);
            $slider.append( $content );

            // rebuild not working right..
            // destroy and re-init plugin
            // move to last index
            $.fn.fullpage.destroy('all');
            init();
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

    $('body').on('change', '.option', function() {
        $('.option').each(function() {
            $(this).closest('li').removeClass('answer');
        });
        $(this).closest('li').addClass('answer');
    });
});