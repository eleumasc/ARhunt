$(function() {
    var stepMap = L.map('stepMap').setView(STEP_TAKE_POS, 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(stepMap);

    L.marker(STEP_TAKE_POS).addTo(stepMap);

    applyActions();
});

function applyActions() {
    $('*[data-action="add-question"]').on('click', function() {
        bootbox.prompt({
            title: 'Domanda',
            callback: function(result) {
                if (result === null) {
                    return;
                }
                $.ajax({
                    type: 'POST',
                    url: AJAX_QUESTIONS_ADD_PATH,
                    data: JSON.stringify ({
                        team: STEP_TEAM,
                        sequence: STEP_SEQUENCE,
                        text: result
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
                    if (data.status === 'invalid-text') {
                        bootbox.alert('Testo non valido (il testo non pu&ograve; essere vuoto).');
                    } else {
                        bootbox.alert('Errore generico.');
                    }
                });
            }
        });
    });

    $('*[data-action="edit-question"]').on('click', function() {
        var question = $(this).attr('data-question');
        var row = $('*[data-bind="question-' + question + '"]');
        var text = row.find($('*[data-bind="text"]'));
        bootbox.prompt({
            title: 'Domanda',
            value: text.text(),
            callback: function(result) {
                if (result === null) {
                    return;
                }
                $.ajax({
                    type: 'POST',
                    url: AJAX_QUESTIONS_EDIT_PATH,
                    data: JSON.stringify ({
                        id: question,
                        text: result
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
                    if (data.status === 'invalid-text') {
                        bootbox.alert('Testo non valido (il testo non pu&ograve; essere vuoto).');
                    } else {
                        bootbox.alert('Errore generico.');
                    }
                });
            }
        });
    });

    $('*[data-action="delete-question"]').on('click', function() {
        var question = $(this).attr('data-question');
        var row = $('*[data-bind="question-' + question + '"]');
        var text = row.find($('*[data-bind="text"]'));
        bootbox.confirm({
            message: 'Sei veramente sicuro di voler eliminare la domanda "' + text.text() + '"?',
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
                    url: AJAX_QUESTIONS_DELETE_PATH,
                    data: JSON.stringify ({
                        id: question
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

    $('*[data-action="add-choice"]').on('click', function() {
        var question = $(this).attr('data-question');
        bootbox.prompt({
            title: 'Risposta',
            callback: function(result) {
                if (result === null) {
                    return;
                }
                $.ajax({
                    type: 'POST',
                    url: AJAX_CHOICES_ADD_PATH,
                    data: JSON.stringify ({
                        question: question,
                        text: result
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
                    if (data.status === 'invalid-text') {
                        bootbox.alert('Testo non valido (il testo non pu&ograve; essere vuoto).');
                    } else {
                        bootbox.alert('Errore generico.');
                    }
                });
            }
        });
    });

    $('*[data-action="edit-choice"]').on('click', function() {
        var choice = $(this).attr('data-choice');
        var row = $('*[data-bind="choice-' + choice + '"]');
        var text = row.find($('*[data-bind="text"]'));
        bootbox.prompt({
            title: 'Risposta',
            value: text.text(),
            callback: function(result) {
                if (result === null) {
                    return;
                }
                $.ajax({
                    type: 'POST',
                    url: AJAX_CHOICES_EDIT_PATH,
                    data: JSON.stringify ({
                        id: choice,
                        text: result
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
                    if (data.status === 'invalid-text') {
                        bootbox.alert('Testo non valido (il testo non pu&ograve; essere vuoto).');
                    } else {
                        bootbox.alert('Errore generico.');
                    }
                });
            }
        });
    });

    $('*[data-action="delete-choice"]').on('click', function() {
        var choice = $(this).attr('data-choice');
        var row = $('*[data-bind="choice-' + choice + '"]');
        var text = row.find($('*[data-bind="text"]'));
        bootbox.confirm({
            message: 'Sei veramente sicuro di voler eliminare la risposta "' + text.text() + '"?',
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
                    url: AJAX_CHOICES_DELETE_PATH,
                    data: JSON.stringify ({
                        id: choice
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

    $('*[data-action="toggle-choice"]').on('click', function() {
        var choice = $(this).attr('data-choice');
        $.ajax({
            type: 'POST',
            url: AJAX_CHOICES_TOGGLE_PATH,
            data: JSON.stringify ({
                id: choice
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
    });
}