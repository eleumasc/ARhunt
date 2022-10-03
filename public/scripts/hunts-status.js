var STATUS_EDITING = 0,
    STATUS_PUBLISHED = 1,
    STATUS_CALLING = 2,
    STATUS_PREPARING = 3,
    STATUS_WAITING = 4,
    STATUS_PLAYING = 5,
    STATUS_CLOSED = 6,
    STATUS_CANCELLED = 7;

var xhr,
    timeout,
    inAction = false,
    tag;

$(function() {
    updateStatus();
});

function updateStatus(action = undefined) {
    if (inAction) {
        return;
    }
    if (action !== undefined) {
        inAction = true;
        xhr.abort();
        window.clearTimeout(timeout);
    }
    xhr = $.ajax({
        type: 'POST',
        url: AJAX_HUNTS_STATUS_PATH,
        data: JSON.stringify ({
            id: HUNT_ID,
            tag: tag,
            action: action
        }),
        contentType: "application/json",
        dataType: 'json'
    })
    .done(function(data) {
        inAction = false;
        if (data.status !== 'updated') {
            tag = data.tag;
            $('#view').html(PLAY ? renderPlay(data) : renderMake(data));
            applyActions();
        }
        if (data.status === 'updated' || data.huntStatus != STATUS_PLAYING || data.huntPlayStatus !== 'timeout') {
            timeout = window.setTimeout(function() {
                updateStatus();
            }, 3000);
        } else {
            var countdown = function(secs) {
                $('*[data-countdown=""]').html(secs);
                window.setTimeout(function() {
                    if (--secs > 0) {
                        countdown(secs);
                    } else {
                        updateStatus();
                    }
                }, 1000);
            };
            countdown(data.timeout);
        }
    })
    .fail(function(xhr) {
        var data = xhr.responseJSON;
        if (data !== undefined && data.status === 'redirect') {
            window.location.replace(data.location);
        }
    });
}

function renderPlay(data) {
    var view = '';
    if (data.huntStatus == STATUS_CALLING) {
        view += '<div class="well">';
        view += '<p><b>Attiva la funzionalit&agrave; di geolocalizzazione <i class="glyphicon glyphicon-map-marker"></i> del dispositivo in uso.</br>Assicurati di essere nella posizione di chiamata indicata nella pagina informativa della caccia, quindi premi il pulsante "Partecipa".</b></p>';
        if (!data.playerExists) {
            view += '<div class="text-center"><button class="btn btn-default" data-action="join">Partecipa</button></div>';
        } else {
            view += '<div class="text-center"><button class="btn btn-default" data-action="leave">Abbandona</button></div>';
        }
        view += '<div>';
        data.players.forEach(function(player) {
            view += '<div class="wrap">' + player.nickname + '</div>';
        });
        view += '</div>';
        view += '</div>';
    } else
    if (data.huntStatus == STATUS_PREPARING) {
        view += '<div class="well">';
        view += '<p><b>Scegli una squadra.</br>Se non scegli alcuna squadra, verrai espulso dalla caccia.</b></p>';
        view += '</div>';
        view += '<div class="row">';
        data.teams.forEach(function(team) {
            view += '<div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">';
            view += '<div class="panel panel-default">';
            view += '<div class="panel-heading" style="background-color: #' + team.color + '; color: #' + team.textColor + ';"><h4 class="panel-title wrap">' + team.name + '</h4></div>';
            view += '<div class="panel-body">';
            view += '<div class="text-center"><button class="btn btn-default" data-action="choose-team" data-team="' + team.id + '" ' + (!team.available || (data.playerTeam !== null && team.id === data.playerTeam.id) ? 'disabled' : '') + '>' + (data.playerTeam === null || team.id !== data.playerTeam.id ? 'Unisciti' : '<i class="glyphicon glyphicon-ok"></i>') + '</button></div>';
            team.players.forEach(function(player) {
                view += '<div class="wrap">' + player.nickname + '</div>';
            });
            view += '</div>';
            view += '</div>';
            view += '</div>';
        });
        view += '</div>';
        view += '<div class="panel panel-default">';
        view += '<div class="panel-body">';
        view += '<div class="text-center"><button class="btn btn-default" data-action="choose-team" ' + (data.playerTeam === null ? 'disabled' : '') + '>Annulla</button></div>';
        data.remainingPlayers.forEach(function (player) {
            view += '<div class="wrap">' + player.nickname + '</div>';
        });
        view += '</div>';
        view += '</div>';
    } else
    if (data.huntStatus == STATUS_WAITING) {
        view += '<div class="well">';
        view += '<p><b>Preparati!</br>La caccia sta per cominciare...</b></p>';
        view += '</div>';
        view += '<div class="row">';
        data.teams.forEach(function(team) {
            view += '<div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">';
            view += '<div class="panel panel-default">';
            view += '<div class="panel-heading" style="background-color: #' + team.color + '; color: #' + team.textColor + ';"><h4 class="panel-title wrap">' + team.name + '</h4></div>';
            view += '<div class="panel-body">';
            team.players.forEach(function(player) {
                view += '<div>' + player.nickname + '</div>';
            });
            view += '</div>';
            view += '</div>';
            view += '</div>';
        });
        view += '</div>';
    } else
    if (data.huntStatus == STATUS_PLAYING) {
        view += '<div class="panel panel-default"><div class="panel-body" style="background-color: #' + data.playerTeam.color + '; color: #' + data.playerTeam.textColor + ';"><h4 class="wrap">' + data.playerTeam.name + '</h4></div></div>';
        if (data.huntPlayStatus === 'step') {
            view += '<div class="text-center"><button class="btn btn-lg btn-default" data-action="take-step">Penso di essere arrivato!</button></div>';
            view += '<h4 class="pre-wrap">' + data.step.text + '</h4>';
            if (data.media !== null) {
                if (data.media.type === 'image') {
                    view += '<img src="' + data.media.path + '" style="max-width: 100%;">';
                } else
                if (data.media.type === 'audio') {
                    view += '<audio controls style="width: 100%;"><source src="' + data.media.path + '" type="audio/' + data.media.subtype + '"><div class="text-danger">Il tuo browser non supporta l\'elemento audio.</div></audio>';
                } else
                if (data.media.type === 'video') {
                    view += '<video controls style="max-width: 100%;"><source src="' + data.media.path + '" type="video/' + data.media.subtype + '"><div class="text-danger">Il tuo browser non supporta l\'elemento video.</div></video>';
                }
            }
        } else
        if (data.huntPlayStatus === 'questions') {
            data.questions.forEach(function(question) {
                view += '<div>';
                view += '<h4 class="wrap">' + question.text + '</h4>';
                question.choices.forEach(function(choice) {
                    if (choice.picked || question.answered) {
                        if (choice.picked) {
                            btnStyle = (choice.right ? 'btn-success' : 'btn-danger');
                        } else {
                            btnStyle = 'btn-default';
                        }
                        btnStyle += ' disabled';
                    } else {
                        btnStyle = 'btn-default';
                    }
                    view += '<button type="button" class="choice btn btn-block btn-lg ' + btnStyle + ' wrap" data-action="pick-choice" data-choice="' + choice.id + '">' + choice.text + '</button>';
                });
                view += '</div>';
            });
        } else
        if (data.huntPlayStatus === 'timeout') {
            view += '<div class="text-center">';
            view += '<h3>Un compagno di squadra ha scelto una risposta sbagliata!</h3>';
            view += '<p>La squadra &egrave; bloccata per <b><span data-countdown=""></span></b> secondi.</p>';
            view += '</div>';
        } else
        if (data.huntPlayStatus === 'closed') {
            view += '<div class="text-center">';
            view += '<h3>Fine!</h3>';
            view += '<p>La squadra ha chiuso la caccia.</p>';
            view += '<p>Rimani in attesa delle altre squadre...</p>';
            view += '</div>';
        }
    }
    return view;
}

function renderMake(data) {
    var view = '';
    view += '<div class="well">';
    view += '<p>Stato: <b>' + data.huntStatusName + '</b></p>';
    if (data.huntStatus == STATUS_EDITING) {
        view += '<p>';
        view += 'La caccia &egrave; in redazione ed &egrave; <b>visibile solamente a te</b>.</br>';
        view += 'In questo stato, puoi <b>modificare</b> ed <b>eliminare</b> la caccia.';
        view += '</p>';
        view += '<button type="button" class="btn btn-primary" data-action="change-status" data-status="' + STATUS_PUBLISHED + '">PUBBLICA <i class="glyphicon glyphicon-chevron-right"></i></button>';
        view += '<p>';
        view += 'Pubblica la caccia, rendendola <b>visibile a tutti</b>.</br>';
        view += 'In questo stato, potrai ancora <b>modificare</b> ed <b>eliminare</b> la caccia.';
        view += '</p>';
    } else
    if (data.huntStatus == STATUS_PUBLISHED) {
        view += '<p>';
        view += 'La caccia &egrave; stata pubblicata ed &egrave; <b>visibile a tutti</b>.</br>';
        view += 'In questo stato, puoi ancora <b>modificare</b> ed <b>eliminare</b> la caccia.';
        view += '</p>';
        view += '<button type="button" class="btn btn-primary" data-action="change-status" data-status="' + STATUS_EDITING + '"><i class="glyphicon glyphicon-chevron-left"></i> REDAZIONE</button>';
        view += '<p>';
        view += 'Torna allo stato di redazione.';
        view += '</p>';
        view += '<button type="button" class="btn btn-primary" data-action="change-status" data-status="' + STATUS_CALLING + '">CHIAMATA <i class="glyphicon glyphicon-chevron-right"></i></button>';
        view += '<p>';
        view += '<b>Conferma le modifiche</b> della caccia ed inizia la <b>chiamata dei giocatori</b>.</br>';
        view += 'In questo stato, <b>NON</b> potrai pi&ugrave; <b>modificare</b> o <b>eliminare</b> la caccia, ma al pi&ugrave; <b>annullarla</b>.</br>';
        view += 'I requisiti per il cambio di stato sono: <b>almeno due squadre</b>, <b>almeno un passo per ogni squadra</b> e <b>almeno un\'opzione corretta e una sbagliata per ogni domanda</b>.</br>';
        view += '&Egrave; fortemente consigliato l\'utilizzo della <b>funzione di verifica</b> della caccia prima di procedere al cambio di stato.';
        view += '</p>';
    } else
    if (data.huntStatus == STATUS_CALLING) {
        view += '<p>';
        view += 'La caccia &egrave; in <b>chiamata dei giocatori</b>.</br>';
        view += 'In questo stato, <b>NON</b> puoi pi&ugrave; <b>modificare</b> o <b>eliminare</b> la caccia, ma al pi&ugrave; <b>annullarla</b>.';
        view += '</p>';
        view += '<button type="button" class="btn btn-primary" data-action="change-status" data-status="' + STATUS_PREPARING + '">PREPARAZIONE <i class="glyphicon glyphicon-chevron-right"></i></button>';
        view += '<p>';
        view += '<b>Conclude la chiamata dei giocatori</b> ed <b>inizia la preparazione delle squadre</b>.</br>';
        view += 'In questo stato, potrai al pi&ugrave; <b>annullare</b> la caccia e <b>retrocedere allo stato di chiamata</b> per permettere ad altri giocatori di partecipare oppure abbandonare la caccia.</br>';
        view += 'I requisiti per il cambio di stato sono: <b>un numero di giocatori maggiore o uguale al numero di squadre</b>.';
        view += '</p>';
    } else
    if (data.huntStatus == STATUS_PREPARING) {
        view += '<p>';
        view += 'La caccia &egrave; in <b>preparazione delle squadre</b>.</br>';
        view += 'In questo stato, puoi al pi&ugrave; <b>annullare</b> la caccia.';
        view += '</p>';
        view += '<button type="button" class="btn btn-primary" data-action="change-status" data-status="' + STATUS_CALLING + '"><i class="glyphicon glyphicon-chevron-left"></i> CHIAMATA</button>';
        view += '<p>';
        view += 'Torna allo stato di chiamata.';
        view += '</p>';
        view += '<button type="button" class="btn btn-primary" data-action="change-status" data-status="' + STATUS_WAITING + '">ATTESA <i class="glyphicon glyphicon-chevron-right"></i></button>';
        view += '<p>';
        view += '<b>Conclude la preparazione</b> ed <b>inizia l\'attesa dell\'apertura</b> della caccia.</br>';
        view += 'In questo stato, potrai al pi&ugrave; <b>annullare</b> la caccia e <b>retrocedere allo stato di preparazione</b> per permettere ai giocatori di cambiare la squadra oppure abbandonare la caccia.</br>';
        view += 'I requisiti per il cambio di stato sono: <b>almeno un giocatore per ogni squadra</b>.</br>';
        view += 'I giocatori che non hanno scelto alcuna squadra <b>verranno espulsi dalla caccia</b>.';
        view += '</p>';
    } else
    if (data.huntStatus == STATUS_WAITING) {
        view += '<p>';
        view += 'La caccia &egrave; in <b>attesa di apertura</b>.</br>';
        view += 'In questo stato, puoi al pi&ugrave; <b>annullare</b> la caccia.';
        view += '</p>';
        view += '<button type="button" class="btn btn-primary" data-action="change-status" data-status="' + STATUS_PREPARING + '"><i class="glyphicon glyphicon-chevron-left"></i> PREPARAZIONE</button>';
        view += '<p>';
        view += 'Torna allo stato di preparazione.';
        view += '</p>';
        view += '<button type="button" class="btn btn-primary" data-action="change-status" data-status="' + STATUS_PLAYING + '">PARTENZA! <i class="glyphicon glyphicon-chevron-right"></i></button>';
        view += '<p>';
        view += '<b>Apri la caccia</b>.</br>';
        view += 'In questo stato, potrai al pi&ugrave; <b>annullare</b> la caccia.';
        view += '</p>';
    } else
    if (data.huntStatus == STATUS_PLAYING) {
        view += '<p>';
        view += '<b>La caccia &egrave; aperta!</b></br>';
        view += 'In questo stato, puoi al pi&ugrave; <b>annullare</b> la caccia.';
        view += '</p>';
    }
    if (data.huntStatus == STATUS_EDITING || data.huntStatus == STATUS_PUBLISHED) {
        view += '<button type="button" class="btn btn-primary" data-action="verify">Verifica</button>';
    }
    if (data.huntStatus == STATUS_CALLING || data.huntStatus == STATUS_PREPARING || data.huntStatus == STATUS_WAITING || data.huntStatus == STATUS_PLAYING) {
        view += '<button type="button" class="btn btn-danger" data-action="change-status" data-status="' + STATUS_CANCELLED + '"><i class="glyphicon glyphicon-remove"></i> ANNULLA</button>';
        view += '<p>';
        view += '<b>Annulla la caccia</b>.</br>';
        view += 'Questo cambio di stato <b>NON pu&ograve; essere annullato!</b>';
        view += '</p>';
    }
    view += '</div>';
    if (data.huntStatus == STATUS_CALLING) {
        view += '<div class="well">';
        data.players.forEach(function(player) {
            view += '<div class="wrap">' + player.nickname + '</div>';
        });
        view += '</div>';
    } else
    if (data.huntStatus == STATUS_PREPARING) {
        view += '<div class="row">';
        data.teams.forEach(function(team) {
            view += '<div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">';
            view += '<div class="panel panel-default">';
            view += '<div class="panel-heading" style="background-color: #' + team.color + '; color: #' + team.textColor + ';"><h4 class="panel-title wrap">' + team.name + '</h4></div>';
            view += '<div class="panel-body">';
            team.players.forEach(function(player) {
                view += '<div class="wrap">' + player.nickname + '</div>';
            });
            view += '</div>';
            view += '</div>';
            view += '</div>';
        });
        view += '</div>';
        view += '<div class="panel panel-default">';
        view += '<div class="panel-body">';
        data.remainingPlayers.forEach(function (player) {
            view += '<div class="wrap">' + player.nickname + '</div>';
        });
        view += '</div>';
        view += '</div>';
    } else
    if (data.huntStatus == STATUS_WAITING) {
        view += '<div class="row">';
        data.teams.forEach(function(team) {
            view += '<div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">';
            view += '<div class="panel panel-default">';
            view += '<div class="panel-heading" style="background-color: #' + team.color + '; color: #' + team.textColor + ';"><h4 class="panel-title wrap">' + team.name + '</h4></div>';
            view += '<div class="panel-body">';
            team.players.forEach(function(player) {
                view += '<div class="wrap">' + player.nickname + '</div>';
            });
            view += '</div>';
            view += '</div>';
            view += '</div>';
        });
        view += '</div>';
    }
    return view;
}

function applyActions() {
    $('*[data-action="change-status"]').on('click', function() {
        var element = $(this);
        bootbox.confirm({
            message: "Sei veramente sicuro di voler cambiare lo stato della caccia?</br>L'operazione potrebbe essere irreversibile!",
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
                if (result) {
                    updateStatus({
                        'type': 'change-status',
                        'status': element.attr('data-status')
                    });
                }
            }
        });
    });

    $('*[data-action="join"]').on('click', function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(gl) {
                updateStatus({
                    'type': 'join',
                    'lat': gl.coords.latitude,
                    'lng': gl.coords.longitude,
                    'acc': gl.coords.accuracy
                });
            });
        }
    });

    $('*[data-action="leave"]').on('click', function() {
        updateStatus({
            'type': 'leave'
        });
    });

    $('*[data-action="choose-team"]').on('click', function() {
        updateStatus({
            'type': 'choose-team',
            'team': $(this).attr('data-team')
        });
    });

    $('*[data-action="take-step"]').on('click', function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(gl) {
                updateStatus({
                    'type': 'take-step',
                    'lat': gl.coords.latitude,
                    'lng': gl.coords.longitude,
                    'acc': gl.coords.accuracy
                });
            });
        }
    });

    $('*[data-action="pick-choice"]').on('click', function() {
        updateStatus({
            'type': 'pick-choice',
            'choice': $(this).attr('data-choice')
        });
    });

    $('*[data-action="verify"]').on('click', function() {
        $.ajax({
            type: 'POST',
            url: AJAX_HUNTS_VERIFY_PATH,
            data: JSON.stringify ({
                id: HUNT_ID
            }),
            contentType: "application/json",
            dataType: 'json'
        })
        .done(function(data) {
            bootbox.alert(data.status === 'ok' ? 'La caccia &egrave; pronta!': 'La caccia non &egrave; pronta! Controlla che tutti i requisiti siano soddisfatti.');
        })
        .fail(function(xhr) {
            bootbox.alert('Errore generico.');
        });
    });
}