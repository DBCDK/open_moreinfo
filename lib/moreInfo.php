<?php

class moreInfo {

  public $thumbnailUrl;
  public $detailUrl;
  public $backpagePdfUrl;
  public $netarchivePdfUrl;

  private $types;

  public function __construct($thumbnailUrl = '', $detailUrl = '', $backpagePdfUrl = '', $netarchivePdfUrl = '') {

    $this->thumbnailUrl = $thumbnailUrl;
    $this->detailUrl = $detailUrl;
    $this->backpagePdfUrl = $backpagePdfUrl;
    $this->netarchivePdfUrl = $netarchivePdfUrl;

    $this->types = array('thumbnailUrl', 'detailUrl', 'backpagePdfUrl', 'netarchivePdfUrl');

  }

  /**
   * @return array
   *   Array of the types that the webservice supports.
   */
  public function getTypes() {
    return $this->types;
  }

  /**
   * @return string
   *   String: filetype.
   */
  public function getFileType($type = NULL) {

    switch ( $type ) {
      case 'backpagePdfUrl':
      case 'netarchivePdfUrl':
        $fileType = '.pdf';
        break;
      default:
        $fileType = '.jpg';
    }

    return $fileType;

  }


}