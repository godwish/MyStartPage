<?php
    if(!isset($_REQUEST['data'])){
        echo '2';
        exit();
    }
    $file = fopen("site_data.json", "w") or die("1");
    fwrite($file, $_REQUEST['data']);
    fclose($file);
    echo "0";
?>