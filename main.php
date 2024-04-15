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
        <form action="main.php" method="post" enctype="multipart/form-data">
            <label for="image">Choose image to upload:</label>
            <input type="file" id="image" name="image" accept="image/*">
            <button type="submit">Upload Image</button>
            
        </form>
        <?php

        // Check if the form was submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Check if file was uploaded without errors
            if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
                $allowed = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
                $filename = $_FILES["image"]["name"];
                $filetype = $_FILES["image"]["type"];
                $filesize = $_FILES["image"]["size"];
                $fileData = $_FILES["image"]["tmp_name"];
            
                // Verify file extension
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if (!array_key_exists($ext, $allowed)) die("Error: Please select a valid file format.");
            
                    // Create image resource based on file type
                switch($filetype) {
                    case 'image/jpeg':
                    case 'image/jpg':
                        $src = imagecreatefromjpeg($fileData);
                        break;
                    case 'image/png':
                        $src = imagecreatefrompng($fileData);
                        break;
                    case 'image/gif':
                        $src = imagecreatefromgif($fileData);
                        break;
                    default:
                        die("Error: Unsupported image format.");
                }

                        // Resize the image
                    list($width, $height) = getimagesize($fileData);
                    $longest = max($width, $height);
                    
                    if ($longest > 1024){
                        $scale = (1024/$longest);
                        $newwidth = round($width * $scale);   // Resized image width
                        $newheight = round($height * $scale); // Resized image height
                
                        if ($newwidth > 0 && $newheight > 0) {
                            $tmp = imagecreatetruecolor($newwidth, $newheight);
                            imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

                            // Convert all image type (except GIF) to jpeg to save space
                            ob_start();
                            imagejpeg($tmp, null, 80); // Output JPEG image data .... 0 = highest compression, smallest file size .... 100 = lowest compression, largest file size
                            $compressed_resizedImage = ob_get_clean(); // Get captured compressed image data
                            // Clean up resources
                            imagedestroy($src);
                            imagedestroy($tmp);
                        } else {
                            die("Error: Invalid image dimensions.");
                        }
                        
                        // Bypass resize
                    } elseif ($longest > 0 && $longest <= 1024) {
                        ob_start();
                        imagejpeg($src, null, 80);
                        $compressed_resizedImage = ob_get_clean();
                    } else {
                        die("Error: Image not found.");
                    }
                    
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
                        $encryptedData = openssl_encrypt($compressed_resizedImage, 'aes-256-cbc', $password, 1, $iv);

                        //Get file size
                        //Save File into local storage
                        $file_encrypted = 'result' . rand(4, 999999999999999999) . ".jpg"; // Path to your text file
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
                        $base64Image = base64_encode($decryptedData); // Adjust content type based on your image type
                        $dataUri = 'data:' . $filetype . ';base64,' . $base64Image;

                        //Print all variables
                        echo "Original file size: " . $filesize . "Bytes.";
                        echo '<br>';
                        echo "Remote address: " . $_SERVER['REMOTE_ADDR'];
                        echo '<br>';
                        echo "Salt: " . $salt;
                        echo '<br>';
                        echo "File size after encryption: " . $size_encrypted . "Bytes.";
                        echo '<br>';
                        echo "<div class = 'container'> <img src='$dataUri'; alt='Decrypted Image'> </div>";
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
                        echo "Error: " . $_FILES["image"]["error"];
                    }
            }
        ?>
    </div>  

</body>
</html>
