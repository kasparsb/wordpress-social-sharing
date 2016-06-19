var $ = jQuery;
var $el;
var ready = false;

function init() {
    if (!ready) {
        createEl();
        setEvents();
        ready = true;
    }
}

function createEl() {
    if (!$el) {
        $el = $('<div class="cover" />')
            .append(
                $('<div class="cover__content" />')
            )
            .appendTo($('body'));
    }
}

function setEvents() {
    $(document).on('click', '.cover', function(ev){
        if ($(ev.target).hasClass('cover')) {
            hide();    
        }
    })
}

function setContent($content) {
    $content.appendTo($el.find('.cover__content'));
}

function show() {
    $el.addClass('cover--visible');
}

function hide() {
    $el.removeClass('cover--visible');
}

module.exports = {
    show: function($content) {
        init();
        setContent($content);
        show();
    },
    hide: function() {
        hide();
    }
}