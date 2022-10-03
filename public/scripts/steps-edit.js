var takePosMap,
    marker;

$(function () {
    takePosMap = L.map('takePosMap').setView(STEP_TAKE_POS, 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(takePosMap);

    marker = L.marker(STEP_TAKE_POS, {
        draggable: true
    }).addTo(takePosMap);

    $('#takePosMap').data('marker', marker);
});

$('#form-step').submit(function() {
    var media = $('#media').val();
    $.ajax({
        type: 'POST',
        url: AJAX_ACTION_PATH,
        data: JSON.stringify ({
            team: TEAM_ID,
            sequence: STEP_SEQUENCE,
            text: $('#text').val(),
            media: (media ? media : null),
            takeLat: $('#takePosMap').data('marker').getLatLng().lat,
            takeLng: $('#takePosMap').data('marker').getLatLng().lng
        }),
        contentType: 'application/json',
        dataType: 'json'
    })
    .done(function(data) {
        if (data.status === 'ok' || data.status == 'not-modified') {
            if (ACTION === 'add') {
                window.location.replace(data.questionsListPath);
            } else {
                window.location.href = STEPS_LIST_PATH;
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

$('#btn-current-position').on('click', function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(gl) {
            marker.setLatLng([gl.coords.latitude, gl.coords.longitude]);
            $('#current-position-accuracy').html(gl.coords.accuracy);
            takePosMap.setView(marker.getLatLng(), 16);
        });
    }
});