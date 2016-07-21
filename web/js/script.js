$(document).ready(function() {
    $('#container').on('click', '#continue',function() {
        $.ajax({
            method: "POST",
            url: "/process",
            data: $('#data').serialize()
        })
            .always(function(content) {
                $('#data').attr('id', '');
                $('#data_container').append(content);
            });
    });
});