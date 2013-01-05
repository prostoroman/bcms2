<?php

abstract class Controller
{
   protected $app;
   protected $bcms;

   public function __construct(Pimple $di) {
      $this->app = $di['app'];
      $this->bcms = $di;
      $this->init($di);
   }

   public abstract function init(Pimple $di);
}
