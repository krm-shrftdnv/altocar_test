let lastCoordinate;
let myMap;
let myPolyline

ymaps.ready(init);

function init() {
    myMap = new ymaps.Map("map", {
        center: [55.780213, 49.133444],
        zoom: 10
    }, {
        searchControlProvider: 'yandex#search'
    });

    initPolyline();
}

function initPolyline() {
    myPolyline = new ymaps.Polyline([], {}, {
        editorDrawingCursor: "crosshair",
        strokeColor: "#00000088",
        strokeWidth: 4,
    });

    myMap.geoObjects.add(myPolyline);

    myPolyline.editor.startDrawing();

    myPolyline.editor.events.add(['vertexadd'],
        function (event) {
            if (lastCoordinate !== undefined) {
                let coordinates = {
                    'startPoint': lastCoordinate,
                    'endPoint': event.get('globalPixels'),
                };
                coordinates['startPoint'] = convertCoordinates(coordinates['startPoint']);
                coordinates['endPoint'] = convertCoordinates(coordinates['endPoint']);

                sendCoordinates(coordinates);
            }
            lastCoordinate = event.get('globalPixels');
        });
}

function convertCoordinates(coordinates) {
    return myMap.options.get('projection').fromGlobalPixels([coordinates[0], coordinates[1]], myMap.getZoom())
}

function sendCoordinates(coordinates) {
    let xhr = new XMLHttpRequest();
    xhr.open(
        'post',
        '/index.php',
    );
    let formData = new FormData();
    formData.append('startPoint', coordinates['startPoint']);
    formData.append('endPoint', coordinates['endPoint']);
    xhr.send(formData);
    xhr.onload = function () {
        fillStatistics(JSON.parse(xhr.response));
    };
}

function getStatistics() {
    let xhr = new XMLHttpRequest();
    xhr.open('get', '/index.php?statistics=1');
    xhr.send();
    xhr.onload = function () {
        fillStatistics(JSON.parse(xhr.response));
    };
}

function fillStatistics(statistics) {
    document.getElementById('routesCountOutput').innerText = statistics['routes_count'];
    document.getElementById('averageDistanceOutput').innerText = statistics['average_distance'];
    document.getElementById('countRoutesUpTo2kmOutput').innerText = statistics['count_routes_up_to_2_km'];
    document.getElementById('countRoutesFrom2To5kmOutput').innerText = statistics['count_routes_from_2_to_5_km'];
    document.getElementById('countRoutesUpTo5kmOutput').innerText = statistics['count_routes_up_to_5_km'];
}

function resetRoutes() {
    myMap.geoObjects.remove(myPolyline);
    initPolyline();
    resetStatistics();
}

function resetStatistics() {
    let xhr = new XMLHttpRequest();
    xhr.open('get', '/index.php?reset=1');
    xhr.send();
    fillStatistics({
        'routes_count': 0,
        'average_distance': 0,
        'count_routes_up_to_2_km': 0,
        'count_routes_from_2_to_5_km': 0,
        'count_routes_up_to_5_km': 0
    })
    lastCoordinate = undefined;
}