<?php


namespace sitemap\core;


abstract class Task implements TaskInterface
{

    public $taskNo;
    public $taskData;
    public $absoluteRoot;
    public $url;
    public $callable;
    public $xmlHeader;
    public $xmlFooter;
    public $filePrefix;



    public function __construct( $taskNo, $taskData, $absoluteRoot,$path,$xmlHeader, $xmlFooter){
        $this->taskNo = $taskNo;
        $this->taskData = $taskData;
        $this->absoluteRoot = $absoluteRoot;
        $this->path = $path;
        $this->xmlHeader = $xmlHeader;
        $this->xmlFooter = $xmlFooter;
    }

    public abstract function init($callable);
}