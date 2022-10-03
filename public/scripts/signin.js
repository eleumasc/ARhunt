$('#form-signin').submit(function() {
    $('#form-signin input, #form-signin button').attr('disabled', 'disabled');
    $('#error-message').html('');
    $.ajax({
        type: 'POST',
        url: AJAX_SIGNIN_PATH,
        data: JSON.stringify ({
            nickname: $('#nickname').val(),
            password: $('#password').val()
        }),
        contentType: 'application/json',
        dataType: 'json'
    })
    .done(function(data) {
        if (data.status === 'ok') {
            window.location.replace(HOME_PATH);
        }
    })
    .fail(function(xhr) {
        var data = xhr.responseJSON;
        $('#form-signin input, #form-signin button').removeAttr('disabled');
        $('#password').val('').focus();
        if (data.status === 'user-not-found') {
            $('#error-message').html('L\'utente non esiste.');
        } else
        if (data.status === 'email-not-verified') {
            $('#error-message').html('L\'indirizzo email non &egrave; ancora stato verificato per questo utente.')
        } else
        if (data.status === 'wrong-password') {
            $('#error-message').html('La password non &egrave; valida.');
        } else {
            $('#error-message').html('Errore generico.');
        }
    });
    return false;
});