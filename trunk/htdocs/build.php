<?php

$data_dir = 'data/';

ob_start();

if (isset($_GET['page'])) {

    dump_data($data_dir . '/' . $_GET['page']);
    
    $tmp = preg_replace('#<li><a href="(\w+)\.html">([\w ]+)</a></li>#', '<li><a href="build.php?page=\1.html">\2</a></li>', ob_get_contents());
    ob_end_clean();
    echo $tmp;
    exit;

}

foreach(glob(chop($data_dir, '/') . '/*.html') as $file) {
    
    dump_data($file);
    file_put_contents(basename($file), ob_get_contents());
    ob_clean();

}

function dump_data($file) {

     readfile('inc/header.html');
     readfile($file);
     readfile('inc/footer.html');

}

?>
