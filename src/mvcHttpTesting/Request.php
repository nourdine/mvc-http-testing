<?php

namespace mvcHttpTesting;

use \RuntimeException;

class Request {

   private $rawBody = "";
   private $multipart = null;

   private function checkMultipartAndThrow() {
      if ($this->multipart === null) {
         throw new RuntimeException("This is not a multipart request");
      }
   }

   /**
    * @param string $input
    * @return array
    */
   private function parseMultipartedRawRequestBody($input) {

      $mp = array();
      $matches = array();

      // grab multipart boundary from content type header
      preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
      $boundary = $matches[1];

      // split content by boundary and get rid of last -- element
      $a_blocks = preg_split("/-+$boundary/", $input);
      array_pop($a_blocks);

      // loop data blocks
      foreach ($a_blocks as $id => $block) {
         if (empty($block))
            continue;

         // you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char
         // parse uploaded files
         if (strpos($block, 'application/octet-stream') !== false) {
            // match "name", then everything after "stream" (optional) except for prepending newlines 
            preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
         }
         // parse all other fields
         else {
            // match "name" and optional value in between newline sequences
            preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
         }
         $mp[$matches[1]] = $matches[2];
      }
      return $mp;
   }

   public function __construct() {
      // @link http://php.net/manual/en/wrappers.php.php
      $this->rawBody = file_get_contents('php://input');
      // if it is a multipart request then parse it accordingly
      if (array_key_exists('CONTENT_TYPE', $_SERVER) &&
         strstr($_SERVER['CONTENT_TYPE'], "multipart") !== false) {
         /**
          * The following line will never work as PHP set 'php://input' to an empty string
          * when Content-type is set to multipart.
          * The actual parts are chuck in $_POST and $_FILE depending on their nature (string vs binary)
          * @link http://stackoverflow.com/questions/1361673/get-raw-post-data
          */
         $this->multipart = $this->parseMultipartedRawRequestBody($this->rawBody);
         // workaround
         $this->multipart = array_merge($_POST, $_FILES);
      }
   }

   public function getHTTPVerb() {
      return $_SERVER["REQUEST_METHOD"];
   }

   public function getParamFromGET($k) {
      if (array_key_exists($k, $_GET)) {
         return $_GET[$k];
      }
   }

   public function getParamFromPOST($k) {
      if (array_key_exists($k, $_POST)) {
         return $_POST[$k];
      }
   }

   public function getParamFromFILES($k) {
      if (array_key_exists($k, $_FILES)) {
         return $_FILES[$k];
      }
   }

   /**
    * When data are put in the request's body in a urlencoded format and 
    * the verb is set to something different from POST then $_POST superglobal array is empty!
    * In such a scenario one needs to parse the row request body stream (php://input) by hand 
    * in order to obtain the data he or she is after.
    * 
    * @param string $key
    * @return string
    */
   public function getUrlencodedDatum($key) {
      $tmp = array();
      parse_str($this->rawBody, $tmp);
      if (array_key_exists($key, $tmp)) {
         return $tmp[$key];
      }
   }

   /**
    * Return the url encoded data in array format.
    * 
    * @return array
    */
   public function getUrlencodedData() {
      $tmp = array();
      parse_str($this->rawBody, $tmp);
      return $tmp;
   }

   /**
    * @param string $key
    * @return string
    */
   public function getMultipartDatum($key) {
      $this->checkMultipartAndThrow();
      if (array_key_exists($key, $this->multipart)) {
         return $this->multipart[$key];
      }
   }

   /**
    * @return array
    */
   public function getMultipart() {
      $this->checkMultipartAndThrow();
      return $this->multipart;
   }

   /**
    * @return string
    */
   public function getRawBody() {
      return $this->rawBody;
   }
}