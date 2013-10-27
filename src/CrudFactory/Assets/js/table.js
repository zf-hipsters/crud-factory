$(document).ready(function() {
    $('.edit').on('click', function(e) {
        e.preventDefault();
        var id = $(this).parents('tr:first').attr('id');
        real_id = id.split('_');

        window.location.href = '/' + module + '/update/' + real_id[1];
    });

    $('.delete').on('click', function(e) {
        e.preventDefault();
        var id = $(this).parents('tr:first').attr('id');
        real_id = id.split('_');

        if (confirm($(this).data('confirm'))) {
            window.location.href = '/' + module + '/delete/' + real_id[1];
        }

    });
});