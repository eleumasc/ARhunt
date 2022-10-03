$('#form-signup').submit(function() {
    $('#form-signup input, #form-signup button').attr('disabled', 'disabled');
    $('#error-message').html('');
    var password = $('#password').val(),
        repeatPassword = $('#repeat-password').val();
    if (password !== repeatPassword) {
        $('#form-signup input, #form-signup button').removeAttr('disabled');
        $('#error-message').html('Le password non coincidono.');
        return false;
    }
    $.ajax({
        type: 'POST',
        url: AJAX_SIGNUP_PATH,
        data: JSON.stringify ({
            email: $('#email').val(),
            nickname: $('#nickname').val(),
            password: password
        }),
        contentType: 'application/json',
        dataType: 'json'
    })
    .done(function(data) {
        if (data.status === 'ok') {
            $('#view-form').hide();
            $('#view-done').show();
        }
    })
    .fail(function(xhr) {
        var data = xhr.responseJSON;
        console.log(data);
        $('#form-signup input, #form-signup button').removeAttr('disabled');
        if (data.status === 'invalid-email') {
            $('#error-message').html('Indirizzo email non valido.');
        } else
        if (data.status === 'email-already-exists') {
            $('#error-message').html('Esiste gi&agrave; un utente con l\'indirizzo email specificato.');
        } else
        if (data.status === 'invalid-nickname') {
            $('#error-message').html('Nickname non valido.');
        } else
        if (data.status === 'nickname-already-exists') {
            $('#error-message').html('Esiste gi&agrave; un utente con il nickname specificato.');
        } else
        if (data.status === 'invalid-password') {
            $('#error-message').html('Password non valida.');
        } else {
            $('#error-message').html('Errore generico.');
        }
    });
    return false;
});