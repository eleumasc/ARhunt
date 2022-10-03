$(function() {
    applyActions();
});

function applyActions() {
    $('*[data-action="delete"]').on('click', function() {
        var team = $(this).attr('data-team');
        var row = $('*[data-bind="' + team + '"]');
        var name = row.find($('*[data-bind="name"]'));
        bootbox.confirm({
            message: 'Sei veramente sicuro di voler eliminare la squadra "' + name.text() + '"?</br>L\'operazione non pu&ograve; essere annullata!',
            buttons: {
                confirm: {
                    label: 'Elimina',
                    className: 'btn-danger'
                },
                cancel: {
                    label: 'Annulla',
                    className: 'btn-default'
                }
            },
            callback: function (result) {
                if (result) {
                    $.ajax({
                        type: 'POST',
                        url: AJAX_TEAMS_DELETE_PATH,
                        data: JSON.stringify ({
                            id: team
                        }),
                        contentType: 'application/json',
                        dataType: 'json'
                    })
                    .done(function(data) {
                        if (data.status === 'ok') {
                            window.location.reload();
                        }
                    })
                    .fail(function() {
                        bootbox.alert('Errore generico.');
                    });
                }
            }
        });
    });
}