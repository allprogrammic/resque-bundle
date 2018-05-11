document.addEventListener("DOMContentLoaded", function() {
    asyncAction();
    toggleAction();
    chartsAction();
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

var chartsAction = function() {
    [].forEach.call(document.querySelectorAll('div[data-charts]'), function(el) {
        var data   = JSON.parse(el.getAttribute('data-charts'));
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
            }
        }
    };

    request.open("GET", action);
    request.send();
}