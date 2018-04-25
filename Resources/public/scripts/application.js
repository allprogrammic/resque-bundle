document.addEventListener("DOMContentLoaded", function() {
    asyncAction();
    toggleAction();
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