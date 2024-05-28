<?php
require __DIR__ . '/vendor/autoload.php';
use Google\Client;
use Google\Service\Drive;

$driver = new DriverApi();
$client = $driver->getClient();


/*$service = new Drive($client);
// Print the names and IDs for up to 10 files.
$optParams = array(
    'pageSize' => 10,
    'fields' => 'nextPageToken, files(id, name)'
);
$results = $service->files->listFiles($optParams);

if (count($results->getFiles()) == 0) {
    print "No files found.\n";
} else {
    print "Files:\n";
    foreach ($results->getFiles() as $file) {
        printf("%s (%s)\n", $file->getName(), $file->getId());
    }
}*/

class DriverApi
{

    function getClient()
    {
        $client = new Client();
        $client->setApplicationName('Google Drive API PHP Quickstart');
        $client->setScopes('https://www.googleapis.com/auth/drive');
        $client->setAuthConfig('credentials2.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        $client->setRedirectUri("http://localhost/googleApi/callback.php");

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = 'token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        try{
            if ($client->isAccessTokenExpired()) {
                // Refresh the token if possible, else fetch a new one.
                if ($client->getRefreshToken()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                } else {
                    // Request authorization from the user.
                    $authUrl = $client->createAuthUrl();
                    printf("Open the following link in your browser:\n%s\n", $authUrl);
                    print 'Enter verification code: ';
                    $authCode = trim(fgets(STDIN));

                    // Exchange authorization code for an access token.
                    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                    $client->setAccessToken($accessToken);

                    // Check to see if there was an error.
                    if (array_key_exists('error', $accessToken)) {
                        throw new Exception(join(', ', $accessToken));
                    }
                }
                // Save the token to a file.
                if (!file_exists(dirname($tokenPath))) {
                    mkdir(dirname($tokenPath), 0700, true);
                }
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            }
        }
        catch(Exception $e) {
            // TODO(developer) - handle error appropriately
            echo 'Some error occured: '.$e->getMessage();
        }
        return $client;
    }

    function upload_file($client, $folderId)
    {
        try {
            $client->addScope(Drive::DRIVE);
            $driveService = new Drive($client);
            $fileMetadata = new Drive\DriveFile(array(
                'name' => 'photo.jpg'
            ));
            $content = file_get_contents('photo.jpg');
            $file = $driveService->files->create($fileMetadata, array([
                'data' => $content,
                'mimeType' => 'image/jpeg',
                'uploadType' => 'multipart'
                ]));
            printf("File ID: %s\n", $file->id);
        } catch(Exception $e) {
            echo "Error Message: ".$e;
        }

    }

    function searchFiles($client)
    {
        try {
            $client->addScope(Drive::DRIVE);
            $driveService = new Drive($client);
            $files = array();
            $pageToken = null;
            do {
                $response = $driveService->files->listFiles(array([
                    'q' => "mimeType='image/jpeg'",
                    'spaces' => 'drive',
                    'pageToken' => $pageToken,
                    'fields' => 'nextPageToken, files(id, name)',
                ]));
                foreach ($response->files as $file) {
                    printf("Found file: %s (%s)\n", $file->name, $file->id);
                }
                array_push($files, $response->files);

                $pageToken = $response->pageToken;
            } while ($pageToken != null);
            return $files;
        } catch(Exception $e) {
            echo "Error Message: ".$e;
        }
    }

    function createFolder($client)
    {
        try {
            $client->addScope(Drive::DRIVE);
            $driveService = new Drive($client);
            $fileMetadata = new Drive\DriveFile(array([
                'name' => 'Prueba',
                'mimeType' => 'application/vnd.google-apps.folder']));
            $file = $driveService->files->create($fileMetadata, array([
                'fields' => 'id']));
            printf("Folder ID: %s\n", $file->id);
            return $file->id;

        }catch(Exception $e) {
            echo "Error Message: ".$e;
        }
    }


}
