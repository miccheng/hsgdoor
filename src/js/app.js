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

function addNum(num)
{
    if (lockKeyPad) return;
    var currPin = $('.pin-div').val();
    var newPin = currPin + num;
    $('.pin-div').val(newPin);
    if (newPin.length == pinLength)
    {
        triggerOpen();
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
            addNum($(this).text());
        }
        else if ($(this).attr('role') == 'clear')
        {
            $('.pin-div').val('');
        }
        else if ($(this).attr('role') == 'other_logins')
        {
            window.location = 'account.php';
        }
        else if ($(this).attr('role') == 'personas_login')
        {
            navigator.id.request();
        }
    });

    $(document).keyup(function(event){
        if (event.keyCode == 49) addNum('1');
        if (event.keyCode == 50) addNum('2');
        if (event.keyCode == 51) addNum('3');
        if (event.keyCode == 52) addNum('4');
        if (event.keyCode == 53) addNum('5');
        if (event.keyCode == 54) addNum('6');
        if (event.keyCode == 55) addNum('7');
        if (event.keyCode == 56) addNum('8');
        if (event.keyCode == 57) addNum('9');
        if (event.keyCode == 48) addNum('0');
    }).keydown(function(event){
        if (event.which == 13) {
            event.preventDefault();
        }
        if (event.keyCode == 8)
        {
            event.preventDefault();
            var currPin = $('.pin-div').val();
            var newPin = currPin.substring(0, currPin.length - 1);
            $('.pin-div').val(newPin);
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
                    setTimeout(function(){
                        window.location = 'account.php';
                    }, 2100);
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