<?php
require_once('funcs.php');
writeLog('Logging out of: %s', $_SESSION['user']);
session_destroy();