(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
var $ = jQuery;
var Cover = require('./cover');
var EmailForm = require('./email-form');

var windowProps = 'location=1,status=1,scrollbars=0,resizable=0,width=530,height=400';

var unformatted = {
    title: document.title,
    link: window.location.href
}

var title = encodeURIComponent(unformatted.title);
var link = encodeURIComponent(unformatted.link);

/**
 * @todo Vecajā versijā bija ovverride share variables
 * Noskaidrot ko tie darīja
 */
//overrideShareVariables();

function setEvents() {
    $(document).on('click', '.socialsharing__share', function(ev){
        // Ja ir metode share, tad pārtraucam uzlikot href
        if ($(this).parents('.socialsharing').hasClass('socialsharing--share')) {
            ev.preventDefault();

            var t = $(this).data('type');
            if (sharing[t] != 'undefined') {
                sharing[t]($(this));
            }
        }
    })
}

var sharing = {
    draugiem: function( $el ) {
        window.open( 
            'http://www.draugiem.lv/say/ext/add.php?title='+title+'&link='+link+'&titlePrefix='+encodeURIComponent( $el.data('prefix') ),
            'draugiem', 
            windowProps
        );
    },

    /**
     * Facebook
     */
    facebook: function( $el ) {
        window.open(
            'http://www.facebook.com/sharer.php?u='+link+'&t='+title,
            'facebook',
            windowProps
        );
    },

    /**
     * Twitter
     */
    twitter: function( $el ) {
        window.open(
            'http://twitter.com/home/?status='+title+' '+link+' via @'+encodeURIComponent( $el.data('user') ),
            'twitter',
            windowProps
        );
    },

    email: function() {
        if (typeof shareViaEmail != 'undefined') {
            shareViaEmail(unformatted.title, unformatted.link);
        }
        else {
            Cover.show(
                EmailForm.get(
                    unformatted.title, 
                    unformatted.link,
                    {
                        postId: socialsharing.postId,
                        ajaxUrl: socialsharing.ajaxUrl,
                        action: 'socialsharing_sendtoemail'
                    },
                    function() {
                        Cover.hide();
                    }
                )
            );
        }
    }
}

setEvents();
},{"./cover":2,"./email-form":3}],2:[function(require,module,exports){
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
},{}],3:[function(require,module,exports){
var $ = jQuery;
var $panel = null;
var backendConfig;
var successCb;

function init() {
    if (!$panel) {
        createPanel();
        setEvents();
    }
}

function createPanel() {
    if (!$panel) {
        $panel = $('<div class="email-form" />').append(
            $('<header class="email-form__heading" />'),
            $('<form class="email-form__fields" />').attr('method', 'post').append(
                fieldTextHtml('Saņēmēja e-pasts:', 'reciever-email', 'email', true),
                fieldTextareaHtml('Tavs komentārs:', 'comment'),
                fieldTextHtml('Tava e-pasts:', 'email', 'email', true),
                fieldTextHtml('Tavs vārds:', 'name', 'text'),
                $('<div class="email-form__error" />'),
                $('<div class="email-form__buttons" />').append(
                    $('<button type="submit" class="email-form__button">Nosūtīt</button>')
                )
            ),
            $('<div class="email-form__loading" />').append(
                $('<div class="email-form__loading-ico" />')
            )
        );
    }
}

function showError(message) {
    $panel.addClass('email-form--error');
    $panel.find('.email-form__error').html(message);
}

function hideError() {
    $panel.removeClass('email-form--error')
    $panel.find('.email-form__error').html('');
}

function fieldTextHtml(caption, name, type, required) {
    return $('<div class="email-form__field" />')
        .addClass(required ? 'email-form__field--required' : '')
        .append(
            $('<label class="email-form__label" />').html(caption),
            $('<input class="email-form__input" />').attr({
                name: name,
                type: type
            })
        );
}

function fieldTextareaHtml(caption, name) {
    return $('<div class="email-form__field email-form__field--textarea" />').append(
        $('<label class="email-form__label" />').html(caption),
        $('<textarea class="email-form__textarea" />').attr('name', name)
    );
}

function showLoading() {
    $panel.addClass('email-form--busy');
}

function hideLoading() {
    $panel.removeClass('email-form--busy');
}

function setEvents() {       
    $panel.on('submit', 'form', function(ev){
        ev.preventDefault();

        hideError();
        showLoading();

        var data = {
            action: backendConfig.action,
            recieveremail: $panel.find('[name=reciever-email]').val(),
            comment: $panel.find('[name=comment]').val(),
            email: $panel.find('[name=email]').val(),
            name: $panel.find('[name=name]').val(),

            post_id: backendConfig.postId
        }

        $.post(backendConfig.ajaxUrl, data, function(r){
            hideLoading();
            if (!r.success) {
                showError(r.message);
            }
            else {
                successCb();
            }
        }, 'json')
    })
}

module.exports = {
    get: function(title, link, conf, success) {
        successCb = success;
        backendConfig = conf;
        init();
        $panel.find('.email-form__heading').html(title);
        return $panel;
    }
}
},{}]},{},[1]);
