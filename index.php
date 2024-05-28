<?php
require __DIR__ . '/google-api/vendor/autoload.php';


$driver = new DriverApi();
$client = $driver->getClient();
$driver->upload_file($client, "13Auy4KLifZvCQhijxk6HBsjUc7DqF_w2");

class DriverApi
{

    function getClient()
    {
        putenv("GOOGLE_APPLICATION_CREDENTIALS=credentials.json");

        $client = new Google_Client();

        $client->useApplicationDefaultCredentials();
        $client->setScopes(Google\Service\Drive::DRIVE);
        $client->setAccessType("offline");

        return $client;

    }

    function upload_file($client, $folderId)
    {
        try {
            $service = new Google_Service_Drive($client);
            $file_path = "photo.jpg";
            $file = new Google_Service_Drive_DriveFile();
            $file->setName($file_path);
            $file->setParents(array($folderId));
            $file->setDescription("Cargar Imagen");
            $file->setMimeType("image/jpg");

            $result = $service->files->create(
                $file,
                array(
                    'data' => file_get_contents($file_path),
                    'mimeType' => 'image/jpg',
                    'uploadType' => 'media'
                )
            );

            echo "File Id:" . $result->id;
        } catch(Exception $e) {
            echo "Error Message: ".$e;
        }

    }
}