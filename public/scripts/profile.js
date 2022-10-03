$('#form-search').submit(function() {
    var search = $('#search').val()
    $.ajax({
        type: 'POST',
        url: AJAX_USER_EXISTS_PATH,
        data: JSON.stringify ({
            nickname: search
        }),
        contentType: 'application/json',
        dataType: 'json'
    })
    .done(function(data) {
        $('#search').val('');
        if (data.status === 'ok') {
            window.location.href = data.profilePath;
        }
    })
    .fail(function(xhr) {
        var data = xhr.responseJSON;
        $('#search').val('');
        if (data.status === 'not-found') {
            bootbox.alert('L\'utente "' + search + '" non esiste.');
        } else {
            bootbox.alert('Errore generico.');;
        }
    });
    return false;
});