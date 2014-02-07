<?php
/**
 * @file
 * moreInfoService class.
 */

class moreInfoService {

  private $wsdlUrl;
  private $username;
  private $group;
  private $password;

  /**
    * Instantiate the moreInfo client.
    */
  public function __construct($wsdlUrl, $username, $group, $password) {
    $this->wsdlUrl  = $wsdlUrl;
    $this->username = $username;
    $this->group    = $group;
    $this->password = $password;
  }


  /**
   * Get information by ISBN.
   *
   * @param mixed $isbn
   *   Expects either a single ISBN, or an array of them, for looking up
   *   multiple materials at a time.
   *
   * @return array
   *   Array of the images that were found.
   */
  public function getByIsbn($isbn) {
    $isbn = str_replace('-', '', $isbn);

    $identifiers = $this->collectIdentifiers('isbn', $isbn);
    $response    = $this->sendRequest($identifiers);
    return $this->extractAdditionalInformation('isbn', $response);
  }


  /**
   * Get information by FAUST number.
   *
   * @param mixed $faust_number
   *   Expects either a single FAUST number, or an array of them, for looking
   *   up multiple materials at a time.
   *
   * @return array
   *   Array of the images that were found.
   */
  public function getByFaustNumber($faustNumber) {

   //quickfix for test only
    if (isset($faustNumber[0]) && $faustNumber[0] == '12345678'){
      return unserialize('a:1:{i:12345678;O:21:"AdditionalInformation":2:{s:12:"thumbnailUrl";s:101:"http://moreinfo.addi.dk/2.1/more_info_get.php?id=12345678&type=forside_lille&key=e2319192f842be20cbe1";s:9:"detailUrl";s:100:"http://moreinfo.addi.dk/2.1/more_info_get.php?id=12345678&type=forside_stor&key=b3059e12e14a20f6cddf";}}');
    }

    $identifiers = $this->collectIdentifiers('faust', $faustNumber);

    $response    = $this->sendRequest($identifiers);

    return $this->extractAdditionalInformation('faust', $response);
  }


  /**
   * Get information by local ID and library code.
   *
   * @param mixed $local_id
   *   Expects either a single object with localIdentifier and libraryCode
   *   attributes, or an array of such objects.
   *
   * @return array
   *   Array of the images that were found.
   */
  public function getByLocalIdentifier($local_id) {
    $identifiers = $this->collectIdentifiers('localIdentifier', $local_id);
    $response    = $this->sendRequest($identifiers);
    return $this->extractAdditionalInformation('localIdentifier', $response);
  }


  /**
   * Expand the provided IDs into the array structure used in sendRequest.
   */
  protected function collectIdentifiers($id_type, $ids) {
    if ( !is_array($ids) ) {
      $ids = array($ids);
    }

    $identifiers = array();
    foreach ($ids as $id) {
      // If we're passed objects from getByLocalIdentifier, convert them
      // to arrays.
      if (is_object($id)) {
        $identifiers[] = (array) $id;
      }
      // Otherwise, just map the ID type to the ID number.
      else {
        $identifiers[] = array($id_type => $id);
      }
    }

    return $identifiers;
  }

  /**
   * Send request to the moreInfo server, returning the data response.
   */
  protected function sendRequest($identifiers) {
    $authInfo = array('authenticationUser' => $this->username,
                      'authenticationGroup' => $this->group,
                      'authenticationPassword' => $this->password);

    // New moreinfo service.
    $client = new SoapClient($this->wsdlUrl);

    // Record the start time, so we can calculate the difference, once
    // the moreInfo service responds.
    $startTime = explode(' ', microtime());

    // Start on the responce object.
    $response = new stdClass();
    $response->identifierInformation = array();

    // Try to get covers 40 at the time as the service has a limit.
    try {
      $offset = 0;
      $ids = array_slice($identifiers, $offset, 40);
      while (!empty($ids)) {
        $data = $client->moreInfo(array(
          'authentication' => $authInfo,
          'identifier' => $ids,
        ));

        // Check if the request went through.
        if ($data->requestStatus->statusEnum != 'ok') {
          throw new moreInfoServiceException($response->requestStatus->statusEnum . ': ' . $response->requestStatus->errorText);
        }

        // Move result into the responce object.
        $response->requestStatus = $data->requestStatus;
        if (is_array($data->identifierInformation)) {
          // If more than one element have been found an array is returned.
          $response->identifierInformation = array_merge($response->identifierInformation, $data->identifierInformation);
        }
        else {
          // If only one "cover" have been request, we need to wrap the data in
          // an array.
          $response->identifierInformation = array_merge($response->identifierInformation, array($data->identifierInformation));
        }

        // Single image... not array but object.

        $offset += 40;
        $ids = array_splice($identifiers, $offset, 40);
      }
    }
    catch (Exception $e) {
      // Re-throw Addi specific exception.
      throw new moreInfoServiceException($e->getMessage());
    }

    $stopTime = explode(' ', microtime());
    $time = floatval(($stopTime[1] + $stopTime[0]) - ($startTime[1] + $startTime[0]));

    //Drupal specific code - consider moving this elsewhere
    if (variable_get('moreinfo_enable_logging', false)) {
      watchdog('moreInfo', 'Completed request (' . round($time, 3) . 's): Ids: %ids', array('%ids' => implode(', ', $ids)), WATCHDOG_DEBUG, 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    }

    if ( !is_array($response->identifierInformation) ) {
      $response->identifierInformation = array($response->identifierInformation);
    }

    return $response;

  }

  /**
   * Extract the data we need from the server response.
   */
  protected function extractAdditionalInformation($idName, $response) {

    $moreInfos = array();

    foreach ( $response->identifierInformation as $info ) {

      $thumbnailUrl = $detailUrl = $backpagePdfUrl = $netarchivePdfUrl = NULL;

      if ( isset($info->identifierKnown) && $info->identifierKnown ) {

        if ( isset($info->coverImage) && $info->coverImage ) {

          if ( !is_array($info->coverImage) ) {
            $info->coverImage = array($info->coverImage);
          }

          foreach ( $info->coverImage as $image ) {
            switch ($image->imageSize) {
              case 'thumbnail':
                $thumbnailUrl = $image->_;
                break;
              case 'detail':
                $detailUrl = $image->_;
                break;
              default:
                // Do nothing other image sizes may appear but ignore them for now
            }
          }

        }

        // just pick the first back cover PDF, if there's several
        if ( !$backpagePdfUrl && isset($info->backPage->_) && $info->backPage->_ ) {
          $backpagePdfUrl = $info->backPage->_;
        }

        // just pick the netarchive PDF URL, if there's several
        if ( !$netarchivePdfUrl && isset($info->netArchive->_) && $info->netArchive->_ ) {
          $netarchivePdfUrl = $info->netArchive->_;
        }

        $moreInfo = new moreInfo($thumbnailUrl, $detailUrl, $backpagePdfUrl, $netarchivePdfUrl);

        $moreInfos[$info->identifier->$idName] = $moreInfo;

      }
    }

    return $moreInfos;

  }

}
