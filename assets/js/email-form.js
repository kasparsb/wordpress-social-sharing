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