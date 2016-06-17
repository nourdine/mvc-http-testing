<?php

namespace mvcHttpTesting;

use \RuntimeException;

class FrontController {

   private $path;
   private $request;
   private $action;

   function __construct($actionsPath, Request $req) {
      $this->path = $actionsPath;
      $this->request = $req;
      $this->action = $this->request->getParamFromGET("action") . "Action";
   }

   public function act() {
      $actionClass = ucfirst($this->action);
      $actionFile = $this->path . "/" . $actionClass . ".php";
      if (file_exists($actionFile)) {
         include_once $actionFile;
         $act = new $actionClass($this->request);
         return json_encode($act->du());
      }
      throw new RuntimeException("Action not found!");
   }
}