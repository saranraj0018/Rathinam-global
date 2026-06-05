
document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
});


document.addEventListener('keydown', function(e) {
    if (e.key === "F12") {
        e.preventDefault();
    }
    if (e.ctrlKey && e.shiftKey && ['I','J','C'].includes(e.key)) {
        e.preventDefault();
    }
    if (e.ctrlKey && e.key === 'u') {
        e.preventDefault();
    }
});


setInterval(function() {
    const devtoolsOpen = window.outerWidth - window.innerWidth > 160 || window.outerHeight - window.innerHeight > 160;
    
    if (devtoolsOpen) {
        alert("DevTools is not allowed!");
        window.location.reload();
    }
}, 1000);


console.log = function() {};
console.warn = function() {};
console.error = function() {};



