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