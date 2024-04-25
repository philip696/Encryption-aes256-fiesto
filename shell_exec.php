<?php        

$CompressedFile = //Filepath to compressed file
$UncompressedFile = //Filepath to uncompressed file

shell_exec("./shrinkpdf-master/shrinkpdf.sh -r 90 -o ./shrinkpdf-master/$CompressedFile ./shrinkpdf-master/$UncompressedFile");
// can run using terminal $ php shell_exec.php
?>

