<?php

$file = realpath($_SERVER['argv'][1]);

$tmp_file = tempnam(null, null);
file_put_contents($tmp_file, shell_exec('hindent -sct 0 -i2 ' . $file));
file_put_contents($tmp_file, shell_exec('tidy -iqc -omit -asxhtml ' . $tmp_file));

readfile($tmp_file);

?>
