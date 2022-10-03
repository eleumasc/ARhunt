$(function() {
    var callPosMap = L.map('callPosMap').setView(HUNT_CALL_POS, 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(callPosMap);

    L.marker(HUNT_CALL_POS).addTo(callPosMap);
});