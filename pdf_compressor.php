<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Image Upload</title>
    <style>
        img {
            max-width: 70vw;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <div style="width:400px">
        <form action="pdf_compressor.php" method="post" enctype="multipart/form-data">
            <label for="pdf">Choose PDF to upload:</label>
            <input type="file" id="pdf" name="pdf" accept="application/pdf">
            <button type="submit">Upload PDF</button>
        </form>

        <?php
        // Check if the form was submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Check if file was uploaded without errors
            if (isset($_FILES["pdf"]) && $_FILES["pdf"]["error"] == 0) {
                $allowed = ["pdf" => "application/pdf"];
                $filename = $_FILES["pdf"]["name"];
                $filetype = $_FILES["pdf"]["type"];
                $filesize = $_FILES["pdf"]["size"];
                $fileData = $_FILES["pdf"]["tmp_name"];
                $filePDF = file_get_contents($_FILES["pdf"]["tmp_name"]);

                //Save raw PDF file
                $file_saved = "/Applications/MAMP/htdocs/File Encryption/shrinkpdf-master/kenneth.pdf"; // Hardcoded filepath to make it easier
                $handle_saved = fopen($file_saved, 'w');
                fwrite($handle_saved, $filePDF);
                fclose($handle_saved);
            
                // Verify file extension
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if (!array_key_exists($ext, $allowed)) die("Error: Please select a valid file format.");

                // print(shell_exec('./shrinkpdf-master/shrinkpdf.sh && ls'));
                print(shell_exec('echo LLLLLLLL'));
                shell_exec("./shrinkpdf-master/shrinkpdf.sh -r 90 -o ./shrinkpdf-master/out.pdf ./shrinkpdf-master/kenneth.pdf");
                $compressedPdfData = file_get_contents('/Applications/MAMP/htdocs/File Encryption/shrinkpdf-master/ujin.pdf');
                echo shell_exec("pwd");

                // print_r($_ENV);

                    
                // Use the salt from the environment variable
                $salt = getenv('REMOTE_ADDR');
                if (!$salt) {
                    die("Error: Encryption salt is not defined in the environment variables.");
                }
        
                // Generate an encryption key using the salt and a password
                $password = 'abcd';
                // $key = openssl_pbkdf2($password, $salt, 32, 10000, 'sha256');
        
                // Encrypt the file content
                $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
                $encryptedData = openssl_encrypt($compressedPdfData, 'aes-256-cbc', $password, 1, $iv);

                //Get file size
                //Save File into local storage
                $file_encrypted = 'result' . rand(4, 999999999999999999) . ".pdf"; // Path to your text file
                $handle_encrypted = fopen($file_encrypted, 'w');
                fwrite($handle_encrypted, $encryptedData);
                fclose($handle_encrypted);
                //check if the file is saved
                if (file_exists($file_encrypted)) {
                    // Get the size of the file
                    $size_encrypted = filesize($file_encrypted);
                } else {
                    die ("File does not exist.");
                }

                //handle image decryption without saving
                $decryptedData = openssl_decrypt($encryptedData, 'aes-256-cbc', $password, 1, $iv);
                $base64Data = base64_encode($compressedPdfData); // Adjust content type based on your image type
                $dataUri = 'data:' . $filetype . ';base64,' . $base64Data;

                //Print all variables
                echo "Original file size: " . $filesize . "Bytes.";
                echo '<br>';
                echo "Remote address: " . $_SERVER['REMOTE_ADDR'];
                echo '<br>';
                echo "Salt: " . $salt;
                echo '<br>';
                echo "File size after encryption: " . $size_encrypted . "Bytes.";
                echo '<br>';
                echo ' <embed src="data:application/pdf;base64,' . $base64Data . '" type="application/pdf" width="150%" height="100%">';
                echo '<br>';
                echo '<br>';
                echo '<br>';
                echo "<div class = 'container'><b>your file was uploaded and encrypted successfully.</b><textarea name='result' rows='50' cols='150'>$encryptedData</textarea> </div>";
                echo '<br>';
                echo '<br>';
                echo '<br>';
                echo '<br>';
                echo '<br>';
                echo '<br>';
                echo '<br>';
                echo '<br>';
                echo '<br>';
                echo '<br>';
                echo "<div class = 'container'> <b>your file was uploaded and decrypted successfully.</b> <textarea name='result' rows='50' cols='150'>  $decryptedData</textarea></div>";
                
                // echo "Your file was uploaded and encrypted successfully." . $decryptedData;
        
            } else {
                echo "Error: " . $_FILES["pdf"]["error"];
            }
    }

    
?>
</div>  

</body>
</html>