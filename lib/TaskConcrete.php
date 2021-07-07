<?php


namespace sitemap;


use sitemap\core\Task;

class TaskConcrete extends Task
{
     public function init( $callable ){

          $this->callable = $callable;

     }

}