<?php

if(isset($_GET['pd']) && $_GET['pd'] === '65ed49199a28be05361830fedf14aad6f9b70a51') {
    $variant = isset($_GET['variant']) ? intval($_GET['variant']) : 'TRUE';
    file_put_contents(__DIR__ . '/.maintenance.php', "<?php\n\nreturn " . $variant . ";\n");
}
