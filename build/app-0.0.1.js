(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
var $ = jQuery;

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
    $(document).on('click', '.socialsharing__sharing', function(ev){
        // Ja ir metode share, tad pārtraucam uzlikot href
        if ($(this).parents('socialsharing').hasClass('.socialsharing--share')) {
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
        shareViaEmail(unformatted.title, unformatted.link);
    }
}

setEvents();
},{}]},{},[1]);
