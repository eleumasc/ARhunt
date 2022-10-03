$(function() {
    applyActions();
});

function applyActions() {
    $('*[data-action="edit"]').on('click', function() {
        var media = $(this).attr('data-media');
        var row = $('*[data-bind="' + media + '"]');
        var name = row.find($('*[data-bind="name"]'));
        bootbox.prompt({
            title: 'Nome',
            value: name.text(),
            callback: function(result) {
                if (result === null) {
                    return;
                }
                $.ajax({
                    type: 'POST',
                    url: AJAX_MEDIA_EDIT_PATH,
                    data: JSON.stringify ({
                        id: media,
                        name: result
                    }),
                    contentType: 'application/json',
                    dataType: 'json'
                })
                .done(function(data) {
                    if (data.status === 'ok') {
                        window.location.reload();
                    }
                })
                .fail(function(xhr) {
                    var data = xhr.responseJSON;
                    if (data.status === 'invalid-name') {
                        bootbox.alert('Nome non valido (il nome non pu&ograve; essere vuoto e deve essere composto da max. 120 caratteri).');
                    } else {
                        bootbox.alert('Errore generico.');
                    }
                });
            }
        });
    });

    $('*[data-action="delete"]').on('click', function() {
        var media = $(this).attr('data-media');
        var row = $('*[data-bind="' + media + '"]');
        var name = row.find($('*[data-bind="name"]'));
        bootbox.confirm({
            message: 'Sei veramente sicuro di voler eliminare il file "' + name.text() + '"?',
            buttons: {
                confirm: {
                    label: 'Si',
                    className: 'btn-danger'
                },
                cancel: {
                    label: 'No',
                    className: 'btn-default'
                }
            },
            callback: function (result) {
                if (!result) {
                    return;
                }
                $.ajax({
                    type: 'POST',
                    url: AJAX_MEDIA_DELETE_PATH,
                    data: JSON.stringify ({
                        id: media
                    }),
                    contentType: 'application/json',
                    dataType: 'json'
                })
                .done(function(data) {
                    if (data.status === 'ok') {
                        window.location.reload();
                    }
                })
                .fail(function(xhr) {
                    bootbox.alert('Errore generico.');
                });
            }
        });
    });
}