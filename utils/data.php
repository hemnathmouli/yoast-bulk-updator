<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include YBSU_DIR . '/vendor/autoload.php';

class GSql {
    function getClient( $_token = '' ) {
        $client = new Google_Client();
        $client->setApplicationName('Yoast SEO');
        $client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
        $client->setAuthConfig(YBSU_DIR . '/utils/credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        $authFile = YBSU_DIR . '/utils/token.json';

        if ( $this->isTokenExists() ) {
            $accessToken = $this->getExistingToken();
            $client->setAccessToken($accessToken);
        } else {
            if ( $_token == "" ) {
                $authUrl = $client->createAuthUrl();
                echo "Open this <a href='$authUrl'>link</a>, authenticate and paste the code here.";
            } else {
                $authCode = trim($_token);
                if ($client->getRefreshToken()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                } else {
                    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                    // Check to see if there was an error.
                    if (array_key_exists('error', $accessToken)) {
                        echo join(', ', $accessToken);
                    } else {
                        if (!file_exists(dirname($authFile))) {
                            mkdir(dirname($authFile), 0700, true);
                        }
                        file_put_contents($authFile, json_encode($client->getAccessToken()));
                        $client->setAccessToken($accessToken); 
                    }
                }
            }
        }

        return $client;
    }

    function isTokenExists( $authFile = YBSU_DIR . '/utils/token.json' ) {
        return file_exists( $authFile );
    }

    function getExistingToken( $authFile = YBSU_DIR . '/utils/token.json' ) {
        return json_decode(file_get_contents($authFile), true);
    }

    function query( $spreadsheet_url = '', $_token = '' ) {
        // Get the API client and construct the service object.
        $client = $this->getClient( $_token );
        $service = new Google_Service_Sheets($client);

        // Prints the names and majors of students in a sample spreadsheet:
        $spreadsheetId = $this->getIdFromUrl( $spreadsheet_url );

        if ( !$spreadsheetId ) return false;

        $range = 'A2:C';
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        if (empty($values)) {
            echo "No data found.";
        } else {
            foreach ($values as $row) {
                // Print columns A and E, which correspond to indices 0 and 4.
                if ( $row[0] != "" ) {
                    printf("<b>%s:</b> &nbsp;&nbsp;", $row[0]);
                    $post_id = url_to_postid( $row[0] );
                    if ( $post_id ) {
                        if ( ybsu_update( $post_id, "_yoast_wpseo_title", $row[1] )
                            && ybsu_update( $post_id, "_yoast_wpseo_metadesc", $row[2] ) ) {
                            echo "<span class='dashicons dashicons-yes' style='color: green;'></span>";
                        } else {
                            echo '<span class="dashicons dashicons-no-alt" style="color: red;"></span>';
                        }
                    } else {
                        echo '<span class="dashicons dashicons-no-alt" style="color: red;"></span>';
                    }

                    echo "<br>";
                }
            }
        }
    }

    function getIdFromUrl( $spreadsheet_url = '' ) {
        if ( $spreadsheet_url == '' ) return false;
        
        $match = array();
        preg_match( '/(d\/)(.*)(\/edit)/', $spreadsheet_url, $match );
        
        return $match[2];
    }

    function runPatch( $url ='', $_token = '' ) {
        $this->query($url, $_token);
    }
}
