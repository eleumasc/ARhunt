$('#form-team').submit(function() {
    $.ajax({
        type: 'POST',
        url: AJAX_ACTION_PATH,
        data: JSON.stringify ({
            id: TEAM_ID,
            hunt: HUNT_ID,
            name: $('#name').val(),
            color: $('#color').val().substring(1)
        }),
        contentType: 'application/json',
        dataType: 'json'
    })
    .done(function(data) {
        if (data.status === 'ok' || data.status == 'not-modified') {
            if (ACTION === 'add') {
                window.location.replace(data.stepsListPath);
            } else {
                window.location.href = TEAMS_LIST_PATH;
            }
        }
    })
    .fail(function() {
        var data = xhr.responseJSON;
        if (data.status === 'invalid-name') {
            bootbox.alert('Nome non valido (il nome non pu&ograve; essere vuoto e deve essere composto da max. 120 caratteri).');
        } else {
            bootbox.alert('Errore generico.');
        }
    });
    return false;
});