<?php

if(isset($_GET['cron']) && $_GET['cron'] === '65ed49199a28be05361830fedf14aad6f9b70a51') {
    require __DIR__ . '/app/bootstrap-cron.php';
}
