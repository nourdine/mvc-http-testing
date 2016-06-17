<?php

namespace mvcHttpTesting;

abstract class Action {

   const REALM = "mvc-http-speaking-libs-testing";

   /**
    * @var Request;
    */
   protected $request = null;
   protected $receivedHeaders;

   public function __construct(Request $r) {
      $this->request = $r;
      $this->receivedHeaders = getallheaders();
   }

   /**
    * @return array
    */
   public function getReceivedHeaders() {
      return $this->receivedHeaders;
   }

   abstract public function du();
}