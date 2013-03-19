<?php
require_once('funcs.php');

$user = isVisitorAuth();
?>
<!DOCTYPE html>
<html>
<head>
    <title>HSG Door</title>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width">
    <link rel="apple-touch-icon" href="img/icon.png" />
    <link href="css/bootstrap.min.css?t=1363669820" rel="stylesheet" media="screen">
    <link href="css/style.css?t=1363669820" rel="stylesheet" media="screen">
</head>
<body>
<div class="container">
    <h2>Login with Account</h2>
    <?php if (!empty($user)): ?>
        <h3>Welcome back!</h3>
        <p>You are logged into <strong><?php echo $user['name']?></strong></p>
        <button class="btn-open btn btn-warning btn-large">
            <i class="icon-user icon-white"></i>
            OPEN DOOR
        </button>
        &nbsp;<a href="logout.php"><span>Sign out</span></a>
        <div id="alert-box" class="alert" style="display:none;margin-top:10px;"></div>
    <?php else: ?>
        <div id="alert-box" class="alert" style="display:none;"></div>
        <div class="keypad">
            <button role="personas_login" class="btn btn-warning btn-large">
                MOZ
            </button>
            <button class="btn btn-large btn-primary disabled">
                FB
            </button>
            <button class="btn btn-large btn-success disabled">
                TW
            </button>
        </div>
        <a href="/"><i class="icon-chevron-left"></i>Login with PIN</a>
    <?php endif; ?>
</div>
<script src="js/jquery-1.9.1.min.js"></script>
<script src="https://login.persona.org/include.js"></script>
<script src="js/app.min.js?t=1363692355"></script>
<script>
    $(document).ready(function()
    {
        var hasTouch = ("ontouchstart" in document.documentElement);
        var bindPhrase = hasTouch ? 'touchstart' : 'click';

        $('.btn-open').bind(bindPhrase, function(e)
        {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: 'open.php',
                dataType: 'json',
                data: {type: 'cookie'},
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
    <?php if (empty($user)): ?>
    navigator.id.watch({
        loggedInUser: null,
        onlogin: function(assertion)
        {
            $.ajax({
                type: 'POST',
                url: '/auth.php',
                data: {assertion: assertion, type:'personas'},
                success: function(res, status, xhr) { window.location.reload(); },
                error: function(xhr, status, err) {
                    navigator.id.logout();
                    alert("Login failure: " + err);
                }
            });
        },
        onlogout: function()
        {
            $.ajax({
                type: 'POST',
                url: '/logout.php',
                success: function(res, status, xhr) { window.location.reload(); },
                error: function(xhr, status, err) { alert("Logout failure: " + err); }
            });
        }
    });
    <?php endif; ?>
</script>

</body>
</html>