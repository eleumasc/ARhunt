$(function () {
    $('#callTimeDatetimepicker').datetimepicker({
        locale: 'it',
        defaultDate: ACTION === 'add' ? moment().add(1, 'days') : moment(HUNT_CALL_TIME, 'YYYY-MM-DD HH:mm:00')
    });

    var callPosMap = L.map('callPosMap').setView(HUNT_CALL_POS, 1);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(callPosMap);

    var marker = L.marker(HUNT_CALL_POS, {
        draggable: true
    }).addTo(callPosMap);

    $('#callPosMap').data('marker', marker);
});

$('#form-hunt').submit(function() {
    $.ajax({
        type: 'POST',
        url: AJAX_ACTION_PATH,
        data: JSON.stringify ({
            id: HUNT_ID,
            name: $('#name').val(),
            description: $('#description').val(),
            callTime: $('#callTimeDatetimepicker').data("DateTimePicker").date().format('YYYY-MM-DD HH:mm:00'),
            callLat: $('#callPosMap').data('marker').getLatLng().lat,
            callLng: $('#callPosMap').data('marker').getLatLng().lng
        }),
        contentType: 'application/json',
        dataType: 'json'
    })
    .done(function(data) {
        if (data.status === 'ok' || data.status == 'not-modified') {
            if (ACTION === 'add') {
                window.location.replace(data.huntsViewPath);
            } else {
                window.location.href = data.huntsViewPath;
            }
        }
    })
    .fail(function() {
        bootbox.alert('Errore generico.');
    });
    return false;
});

$('#delete').on('click', function() {
    bootbox.confirm({
        message: 'Sei veramente sicuro di voler eliminare questa caccia?</br>L\'operazione non pu&ograve; essere annullata!',
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
                    url: AJAX_HUNTS_DELETE_PATH,
                    data: JSON.stringify ({
                        id: HUNT_ID
                    }),
                    contentType: 'application/json',
                    dataType: 'json'
                })
                .done(function(data) {
                    if (data.status === 'ok') {
                        window.location.replace(HUNTS_LIST_MAKE_PATH);
                    }
                })
                .fail(function() {
                    bootbox.alert('Errore generico.');
                });
            }
        }
    });
});