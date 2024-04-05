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
                $fileData = file_get_contents($_FILES["image"]["tmp_name"]);
            
                // Verify file extension
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if (!array_key_exists($ext, $allowed)) die("Error: Please select a valid file format.");
            
                // Verify file size - 5MB maximum
                $maxsize = 5 * 1024 * 1024;
                if ($filesize > $maxsize) {
                    // Create image resource based on file type
                    switch($filetype) {
                        case 'image/jpeg':
                        case 'image/jpg':
                            $src = imagecreatefromjpeg($_FILES["image"]["tmp_name"]);
                            break;
                        case 'image/png':
                            $src = imagecreatefrompng($_FILES["image"]["tmp_name"]);
                            break;
                        case 'image/gif':
                            $src = imagecreatefromgif($_FILES["image"]["tmp_name"]);
                            break;
                        default:
                            die("Error: Unsupported image format.");
                    }
                    
                        // Resize the image
                    list($width, $height) = getimagesize($_FILES["image"]["tmp_name"]);
                    $longest = max($width, $height);
                    if ($longest > 1024){
                        $scale = (1024/$longest);
                        $newwidth = round($width * $scale);   // Resized image width
                        $newheight = round($height * $scale); // Resized image height
                    
                
                        if ($newwidth > 0 && $newheight > 0) {
                            $tmp = imagecreatetruecolor($newwidth, $newheight);
                            imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

                            // Replace $fileData with resized image
                            ob_start();
                            switch($filetype) {
                                case 'image/jpeg':
                                case 'image/jpg':
                                    imagejpeg($tmp, null, 50); // Output JPEG image data .... 0 = highest compression, smallest file size .... 100 = lowest compression, largest file size
                                    break;
                                case 'image/png':
                                    imagepng($tmp, null, 3); // Output PNG image data .... 0 = largest file size .... 9 = smallest file size
                                    break;
                                case 'image/gif':
                                    imagegif($tmp); // Output GIF image data
                                    break;
                            }
                            $fileData = ob_get_clean(); // Get captured image data
                            // Clean up resources
                            imagedestroy($src);
                            imagedestroy($tmp);
                        } else {
                            die("Error: Invalid image dimensions.");
                        }
                    }
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
                        $encryptedData = openssl_encrypt($fileData, 'aes-256-cbc', $password, 1, $iv);
                        $decryptedData = openssl_decrypt($encryptedData, 'aes-256-cbc', $password, 1, $iv);

                        //Get file size
                        //Save File into local storage
                        $file_encrypted = 'result' . rand(4, 999999999999999999); // Path to your text file
                        $handle_encrypted = fopen($file_encrypted, 'w');
                        fwrite($handle_encrypted, $fileData);
                        fclose($handle_encrypted);
                        //check if the file is saved
                        if (file_exists($file_encrypted)) {
                            // Get the size of the file
                            $size_encrypted = filesize($file_encrypted);
                        } else {
                            die ("File does not exist.");
                        }

                        //Get De-crypted image file
                        $image_decrypted = 'DecryptedImage' . rand(4, 999999999999999999) . '.jpg'; // Path to your text file
                        $handle_decrypted = fopen($image_decrypted, 'w');
                        fwrite($handle_decrypted, $decryptedData);
                        fclose($handle_decrypted);
                        //check if the file is saved
                        if (file_exists($image_decrypted)) {
                            // Display the image
                            echo "File is already encrypted and decrypted." . "<br>";
                        } else {
                            echo "Image not found.";
                        }

                        //Print all variables
                        echo "Filesize: " . $filesize . "Bytes.";
                        echo '<br>';
                        echo "Remote address: " . $_SERVER['REMOTE_ADDR'];
                        echo '<br>';
                        echo "Salt: " . $salt;
                        echo '<br>';
                        echo "Filesize after encryption: " . $size_encrypted . "Bytes.";
                        echo '<br>';
                        echo "<div class = 'container'> <img src='" . $image_decrypted . "' alt='Example Image'> </div>";
                        echo '<br>';
                        echo "<div class = 'container'>your file was uploaded and encryptedsuccessfully.  <textarea name='result' rows='200' cols='200'>$encryptedData</textarea> </div>";
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
                        echo '<br>';
                        echo "<div class = 'container'> your file was uploaded and decrypted successfully. <textarea name='result' rows='200' cols='200'>  $decryptedData</textarea></div>";
                        
                        // echo "Your file was uploaded and encrypted successfully." . $decryptedData;
                
                    } else {
                        echo "Error: " . $_FILES["image"]["error"];
                    }
            } else {
                echo "Error: " . $_FILES["image"]["error"];
        }
        ?>
    </div>  

</body>
</html>