document.addEventListener("DOMContentLoaded", function() {
    chartsAction();
    asyncAction();
    toggleAction();
    MicroModal.init();

    [].forEach.call(document.querySelectorAll('div[data-charts-async]'), function(el) {
        createChart(el, '{}');
    });
});

var toggleAction = function() {
    [].forEach.call(document.getElementsByClassName('toggle'), function(el) {
        el.onclick = function() {
            el.classList.toggle('active');
        }
    });
};

/**
 * Async action
 */
var asyncAction = function() {
    [].forEach.call(document.querySelectorAll('div[async-reload]'), function(el) {
        reloadContent(el);
        setInterval(function(){ reloadContent(el); }, el.getAttribute('async-interval'));
    });
};

var reloadCharts = function(el, duration) {
    var action = el.getAttribute('data-charts-action');
    var request = new XMLHttpRequest();

    if (typeof duration === 'undefined') {
        duration = 1000;
    }

    request.onreadystatechange = function(data) {
        if(request.readyState === 4) {
            if (request.status === 200) {
                createChart(el, request.responseText, duration);
            }
        }
    };

    request.open("GET", action);
    request.send();
}

var chartsAction = function() {
    [].forEach.call(document.querySelectorAll('div[data-charts-async]'), function(el) {
        reloadCharts(el);
        setInterval(function() { reloadCharts(el, 0); }, el.getAttribute('data-charts-interval'));
    });
}

var createChart = function(el, content, duration) {
    var data   = JSON.parse(content);
    var div    = document.createElement('div');
    var canvas = document.createElement('canvas');

    div.classList.add('chart-container');
    div.appendChild(canvas);
    el.innerHTML = '';
    el.appendChild(div);

    new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: {
            labels: Object.keys(data),
            datasets: [{
                label: el.getAttribute('data-label'),
                borderColor: 'rgb(206, 18, 18)',
                backgroundColor: 'rgb(206, 18, 18)',
                data: Object.values(data),
                fill: false,
            }]
        },
        options: {
            legend: false,
            responsive: true,
            animation: {
                duration: duration
            },
            title: {
                display: true,
                text: el.getAttribute('data-title'),
            },
            tooltips: {
                mode: 'index',
                intersect: false,
            },
        }
    });
}

/**
 * Reload content
 *
 * @param el
 */
var reloadContent = function(el) {
    var action = el.getAttribute('async-action');
    var request = new XMLHttpRequest();

    request.onreadystatechange = function(data) {
        if(request.readyState === 4) {
            if (request.status === 200) {
                el.innerHTML = request.responseText;
                MicroModal.init();
            }
        }
    };

    request.open("GET", action);
    request.send();
}