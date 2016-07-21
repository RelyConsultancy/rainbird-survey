function getSlide(slides, callback) {
    $.ajax({
        method: "POST",
        url: "/process",
        data: $('#data').serialize(),
        async: false
    })
        .always(function(content) {
            $('#data').attr('id', '');
            slides.push(content);
        });
    callback();
}