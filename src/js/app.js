var lockKeyPad = false;
var pinLength = 6;

function showAlert(aClass, msg)
{
    $('#alert-box').removeClass('alert-error').removeClass('alert-success').addClass(aClass).text(msg).show();
    if ($('.pin-div').length > 0)
    {
        $('.pin-div').val('').removeClass('loading').hide();
    }
    lockKeyPad = true;
    setTimeout(function(){
        $('#alert-box').hide();
        if ($('.pin-div').length > 0)
        {
            $('.pin-div').show();
        }
        lockKeyPad = false;
    }, 2000);
}

function triggerOpen()
{
    lockKeyPad = true;
    var pin = $('.pin-div').val();
    $('.pin-div').addClass('loading');
    if (pin.length != pinLength)
    {
        showAlert('alert-error', 'Door PIN is too short. Try again.');
    }
    else
    {
        $.ajax({
            type: 'POST',
            url: 'open.php',
            dataType: 'json',
            data: {pin: pin},
            success: function(res, status, xhr) {
                if (res.status == 'okay')
                {
                    showAlert('alert-success', res.msg);
                }
                else
                {
                    showAlert('alert-error', res.msg);
                }
            },
            error: function(xhr, status, err) {
                showAlert('alert-error', err);
            }
        });
    }
}

$(document).ready(function()
{
    var hasTouch = ("ontouchstart" in document.documentElement);
    var bindPhrase = hasTouch ? 'touchstart' : 'click';

    $('.keypad .btn').bind(bindPhrase, function(e)
    {
        e.preventDefault();
        if (lockKeyPad) return;

        if ($(this).attr('role') == 'num')
        {
            var currPin = $('.pin-div').val();
            var newPin = currPin + $(this).text();
            $('.pin-div').val(newPin);
            if (newPin.length == pinLength)
            {
                triggerOpen();
            }
        }
        else if ($(this).attr('role') == 'clear')
        {
            $('.pin-div').val('');
        }
        else if ($(this).attr('role') == 'other_logins')
        {
            window.location = 'others.php';
        }
        else if ($(this).attr('role') == 'personas_login')
        {
            navigator.id.request();
        }
    });

    $('.btn-open').bind(bindPhrase, function(e)
    {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'open.php',
            dataType: 'json',
            data: {pin: ''},
            success: function(res, status, xhr) {
                if (res.status == 'okay')
                {
                    showAlert('alert-success', res.msg);
                }
                else
                {
                    showAlert('alert-error', res.msg);
                }
            },
            error: function(xhr, status, err) {
                showAlert('alert-error', err);
            }
        });
    });
});