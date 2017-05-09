var $ = jQuery;
var Cover = require('./cover');
var EmailForm = require('./email-form');

var windowProps = 'location=1,status=1,scrollbars=0,resizable=0,width=530,height=400';

var unformatted = {
    title: '',
    link: '',
    user: '', // twitter
    prefix: '', // draugiem,
    description: '',

    fbdescription: '',
    fbtitle: ''
}

var title, link, user, prefix, description, name, fbtitle, fbdescription;

function setEvents() {
    $(document).on('click', '.socialsharing__share', function(ev){
        // Ja ir metode share, tad pārtraucam uzlikot href
        if ($(this).parents('.socialsharing').hasClass('socialsharing--share')) {
            ev.preventDefault();

            // Nolasām uz sharing elementu uzliktos title, link, user parametrus. Ja tādi ir
            readParameters($(this).parents('.socialsharing'));

            var t = $(this).data('type');
            if (sharing[t] != 'undefined') {
                trackShareHit(t);
                sharing[t]($(this));
            }
        }
    })
}

function readParameters($el) {
    unformatted.title = $el.data('title') ? $el.data('title') : document.title;
    unformatted.link = $el.data('link') ? $el.data('link') : window.location.href;
    unformatted.user = $el.data('user') ? $el.data('user') : '';
    unformatted.prefix = $el.data('prefix') ? $el.data('prefix') : '';
    unformatted.description = $el.data('description') ? $el.data('description') : '';

    unformatted.fbtitle = $el.data('fbtitle') ? $el.data('fbtitle') : '';
    unformatted.fbdescription = $el.data('fbdescription') ? $el.data('fbdescription') : '';
    

    title = encodeURIComponent(unformatted.title);
    link = encodeURIComponent(unformatted.link);
    user = encodeURIComponent(unformatted.user);
    prefix = encodeURIComponent(unformatted.prefix);
    description = encodeURIComponent(unformatted.description);

    fbtitle = encodeURIComponent(unformatted.fbtitle);
    fbdescription = encodeURIComponent(unformatted.fbdescription);
}

function trackShareHit(share, postId) {
    $.post(socialsharing.ajaxUrl, {
        action: 'socialsharing_hit',
        post_id: socialsharing.postId,
        share: share
    });
}

var sharing = {
    draugiem: function( $el ) {
        window.open( 
            'http://www.draugiem.lv/say/ext/add.php?title='+title+'&link='+link+'&titlePrefix='+prefix,
            'draugiem', 
            windowProps
        );
    },

    facebook: function( $el ) {
        var params = [];
        // Link
        params.push('u='+link);

        if (fbtitle) {
            params.push('title='+fbtitle);
        }
        else if (title) {
            params.push('title='+title);
        }

        if (fbdescription) {
            params.push('description='+fbdescription);
        }
        else if (description) {
            params.push('description='+description);
        }

        window.open(
            'http://www.facebook.com/sharer.php?'+params.join('&'),
            'facebook',
            windowProps
        );
    },

    twitter: function( $el ) {
        var via = '';
        if (user) {
            via = ' via @'+user;
        }

        window.open(
            'http://twitter.com/home/?status='+title+' '+link+via,
            'twitter',
            windowProps
        );
    },

    whatsapp: function( $el ) {
        window.location = 'whatsapp://send?text='+title+'%20'+link;
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