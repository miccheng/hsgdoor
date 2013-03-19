<?php
session_start();
$user = !empty($_SESSION['user']) ? $_SESSION['user'] : null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>HSG Door</title>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width">
    <link rel="apple-touch-icon" href="http://dl.dropbox.com/u/3361521/hackerspace/icon.png" />
    <link href="css/bootstrap.min.css?t=1363669820" rel="stylesheet" media="screen">
    <link href="css/style.css?t=1363669820" rel="stylesheet" media="screen">
</head>
<body>
<div class="container">
    <h2>Login with Account</h2>
    <?php if (!empty($user)): ?>
        <h3>Welcome back!</h3>
        <p>You are logged into <strong><?php echo $user?></strong></p>
        <button class="btn-open btn btn-warning btn-large">
            <i class="icon-user icon-white"></i>
            OPEN DOOR
        </button>
        &nbsp;<a href="#" class="persona-signout"><span>Sign out</span></a>
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
        <a href="index.php"><i class="icon-chevron-left"></i>Login with PIN</a>
    <?php endif; ?>
</div>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="https://login.persona.org/include.js"></script>
<script src="js/app.min.js?t=1363673200"></script>
<script>
    $(document).ready(function()
    {
        $('a.persona-signout').click(function(e) {
            navigator.id.logout();
            e.preventDefault();
        });
    });
    var currentUser = <?php echo (!empty($user)) ? sprintf("'%s'", $user) : 'null'; ?>;
    navigator.id.watch({
        loggedInUser: currentUser,
        onlogin: function(assertion)
        {
            $.ajax({
                type: 'POST',
                url: '/auth.php',
                data: {assertion: assertion},
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
</script>

</body>
</html>