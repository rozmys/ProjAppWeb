//Metoda updateClock() - wyświetla aktualny czas w elemencie o id="clock" w formacie hh:mm:ss
function updateClock() {
    var now = new Date();
    var hours = now.getHours();
    var minutes = now.getMinutes();
    var seconds = now.getSeconds();

    document.getElementById('clock').innerHTML = hours + ':' + minutes + ':' + seconds;

    setTimeout(updateClock, 1000);
}

updateClock();
