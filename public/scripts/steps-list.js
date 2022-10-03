$(function() {
    var stepsMap = L.map('stepsMap');

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(stepsMap);

    L.polyline(PATH, { color: 'red' }).addTo(stepsMap);

    var markerArray = [];
    PATH.forEach(function(pos, i) {
        var marker = L.marker(pos).bindTooltip(i ? i.toString() : '<b>P</b>', { direction: 'right', permanent: true, opacity: 1 });
        markerArray.push(marker);
    });

    var markerGroup = L.featureGroup(markerArray).addTo(stepsMap);
    stepsMap.fitBounds(markerGroup.getBounds());

    applyActions();
});

function applyActions() {
    $('*[data-action="delete"]').on('click', function() {
        var sequence = $(this).attr('data-sequence');
        bootbox.confirm({
            message: 'Sei veramente sicuro di voler eliminare il passo ' + sequence + '?</br>L\'operazione non pu&ograve; essere annullata!',
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
                        url: AJAX_STEPS_DELETE_PATH,
                        data: JSON.stringify ({
                            team: TEAM_ID,
                            sequence: sequence
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

    $('*[data-action="move-up"], *[data-action="move-down"]').on('click', function() {
        var action = $(this).attr('data-action'),
            sequence = $(this).attr('data-sequence');
        $.ajax({
            type: 'POST',
            url: AJAX_STEPS_MOVE_PATH,
            data: JSON.stringify ({
                team: TEAM_ID,
                sequence: sequence,
                dir: (action === 'move-down' ? 1 : -1)
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
    });
}