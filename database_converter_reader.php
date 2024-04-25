<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MySQL Database Encoder and Decoder</title>
    <style>
        img {
            max-width: 70vw;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <div style="width:400px">
        <?php
        require_once "PDO.php";

        $file_database_name = "";
        //get data from SQL query
        $stmt = $pdo->prepare(" SELECT
        m_student_documents.id,
        m_student_documents.student, 
        m_students.birthdate,
        m_students.name,
        m_student_documents.student_document_type, 
        m_student_documents.filename,
        m_student_document_types.id as document_type_id,
        m_student_document_types.folder as document_type_folder,
        m_student_document_types.name as document_type_name
    FROM 
        m_student_documents
    INNER JOIN
        m_students ON m_student_documents.student = m_students.id
    INNER JOIN
        m_student_document_types ON m_student_document_types.id = m_student_documents.student_document_type
    WHERE
        m_student_documents.deleted = 0 
        AND m_student_documents.id != 0
        AND m_student_documents.filename LIKE '%.pdf';");
                $stmt->execute(); // Execute the prepared statement
                $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($row as $rows) {
                    // Get salt from birthdate in database query
                    $salt = $rows['birthdate'];
                            if (!$salt) {
                                die("Error: Encryption salt is not defined in the environment variables.");
                            }
                    // Generate an encryption key using the salt and a password
                    $password = 'abcd'; //setting sendiri
                    $key = openssl_pbkdf2($password, $salt, 32, 10000, 'sha256'); //setting sendiri
                    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc')); //setting sendiri
                    
                    // Check if file was uploaded without errors
                    if (isset($rows['id']) && isset($rows['document_type_folder']) && isset($rows['filename']) && isset($rows['birthdate'])) {
                        $allowed = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png", "pdf" => "application/pdf"]; // allowed extensions
                        $filename = $rows['filename'];
                        $filefolder = $rows['document_type_folder'];
                        $filepath = "/Users/philipdewanto/Documents/photos/students//$filefolder/$filename"; //hardcoded filepath to make it easier.
                        $fileData = file_get_contents($filepath);
                        $outputFileName = $rows['name'] . "_" . $filefolder . ".pdf";

                        print $fileData;
                    
                    // // Verify file extension
                        $ext = pathinfo($filename, PATHINFO_EXTENSION);
                        if (!array_key_exists($ext, $allowed)) print("Error: Please select a valid file format.");
                    // If file format is pdf, encrypt pdf
                        if($ext == "pdf"){
                            $encryptedData = openssl_encrypt($fileData, 'aes-256-cbc', $key, 1, $iv);
                        } else {
                     // Create image resource based on file type
                        switch(strtolower($ext)) {
                            case 'jpeg':
                            case 'jpg':
                                $src = imagecreatefromjpeg($filepath);
                                break;
                            case 'png':
                                $src = imagecreatefrompng($filepath);
                                break;
                            default:
                                print("Error: Unsupported image format.");
                        }

                            // calculate dimensions of image
                            list($width, $height) = getimagesize($filepath);
                            $longest = max($width, $height);
                            // Resize the image if dimension > 1024
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
                                    print("Error: Invalid image dimensions.");
                                }
                                
                                // Bypass resize if dimension < 1024
                            } elseif ($longest > 0 && $longest <= 1024) {
                                ob_start();
                                imagejpeg($src, null, 80);
                                $compressed_resizedImage = ob_get_clean();
                            } else {
                                print("Error: Image not found.");
                           }
                            // Encrypt the file content
                            $encryptedData = openssl_encrypt($compressed_resizedImage, 'aes-256-cbc', $key, 1, $iv);
                        }
                            // Handle file save format and path
                            $file_encrypted = "/Users/philipdewanto/Documents/photos copy/students/$filefolder/$filename"; // Hardcoded filepath to make it easier
                            $handle_encrypted = fopen($file_encrypted, 'w');
                            fwrite($handle_encrypted, $encryptedData);
                            fclose($handle_encrypted);

                            //handle image decryption without saving
                            $decryptedData = openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 1, $iv);
                            $base64Data = base64_encode($decryptedData);
                            $dataUri = 'data:' . $ext . ';base64,' . $base64Data;
                            
                            //handle output if pdf
                            if($ext == "pdf"){
                                echo '<embed src="data:application/pdf;base64,' . $base64Data . '" type="application/pdf" width="150%" height="100%">';
                                echo '<a href="data:application/pdf;base64,' . $base64Data . '" download="' . $outputFileName . '">Download PDF</a>';
                                echo "File Name: $outputFileName";
                            } else {
                            //handle output if non pdf
                                echo "<div class = 'container'> <img src='$dataUri'; alt='Decrypted Image'> </div>";
                            }
                            echo "ID: {$rows['id']}, Birthdate: {$rows['birthdate']}, Filename: {$rows['filename']}, File path = $filepath<br> Key = $key, Salt = $salt,  Password = $password<br><br>";
                    }
                        
                }
        
            
        ?>
    </div>  

</body>
</html>