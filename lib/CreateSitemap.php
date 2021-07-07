<?php

namespace sitemap;

use sitemap\core\Scheduler;
use sitemap\core\SitemapInterface;
use sitemap\core\Task;
use sitemap\core\TaskInterface;
use sitemap\helper\Utils;

class CreateSitemap implements SitemapInterface, \ArrayAccess
{
    protected $data;
    protected $num;
    protected $chunkedData;
    protected $absoluteRoot;
    protected $path;
    protected $fullPath;
    protected $taskCount;
    protected $scheduler;
    protected $callable;
    protected $config;
    protected $xmlHeader;
    protected $xmlFooter;
    protected $xmlCollectionFile;
    protected $lock;

    public function __construct($data, $callable)
    {

        $this->data = $data;
        $this->callable = $callable;
        $this->config = $this['config'];

        $this->boot();
        $this->register();
    }


    public function run()
    {
        $this->scheduler->run();

    }

    protected function boot()
    {


        $this->num = $this->getNum();
        $this->taskCount = $this->getTaskCount();
        $this->chunkedData = $this->getChunkedData();
        $this->absoluteRoot = $this->getAbsoluteRoot();
        $this->path = $this->getPath();
        $this->xmlHeader = $this->getXmlHeader();
        $this->xmlFooter = $this->getXmlFooter();
        $this->lockFile = $this->getLockFile();

        $this->lock();

    }

    protected function register()
    {


        $scheduler = new Scheduler();


        for ($i = 0; $i < $this->taskCount; $i++) {
            $taskNo = $i + 1;
            $taskDataObj = new TaskConcrete($taskNo, $this->chunkedData[$i], $this->absoluteRoot, $this->path, $this->xmlHeader, $this->xmlFooter);
            $taskDataObj->init($this->callable);

            $taskObj = $scheduler->task($taskDataObj);
            $scheduler->addTask($taskObj);

        }

        $this->scheduler = $scheduler;


    }

    protected function lock()
    {

        $lockFile = $this->lockFile ?: $this->getLockFile();

        file_put_contents($this->lockFile, '1');
    }

    protected function unlock()
    {

        $lockFile = $this->lockFile ?: $this->getLockFile();
        if (file_exists($lockFile)) {
            unlink($lockFile);
        }
    }

    protected function getFullPath()
    {

        if (empty($this->fullPath)) {
            $absoluteRoot = str_replace('\\', '/', rtrim($this->absoluteRoot, '\\/'));
            $path = str_replace('\\', '/', rtrim($this->path, '\\/'));
            $this->fullPath = $absoluteRoot . DIRECTORY_SEPARATOR . $path;
        }
        return $this->fullPath;


    }

    protected function getLockFile()
    {
        $fullPath = $this->fullPath ?: $this->getFullPath();
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777, true);
        }
        $this->lockFile = $fullPath . DIRECTORY_SEPARATOR . 'runtime.lock';
        return $this->lockFile;
    }

    protected function getTaskCount()
    {
        return ceil(count($this->data) / $this->num);
    }

    protected function getChunkedData()
    {
        return array_chunk($this->data, $this->num);
    }

    protected function getAbsoluteRoot()
    {
        return dirname(dirname(dirname(__DIR__))) . "/";
    }

    protected function getPath()
    {
        return $this->config['path'] ?: "/sitemap/";
    }

    protected function getNum()
    {

        return $this->config['num'] ?: 30000;
    }


    protected function getXmlHeader()
    {

        //配置文件  属性值
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . '<urlset>';
        return $this->xmlHeader ?: $this->config['xmlHeader'] ?: $xml;

    }

    protected function getXmlFooter()
    {
        $xml = PHP_EOL . '</urlset>';
        return $this->xmlFooter ?: $this->config['xmlFooter'] ?: $xml;

    }

    protected function getXmlCollectionFile()
    {
        if (empty($this->xmlCollectionFile)) {
            $this->xmlCollectionFile = rtrim($this->fullPath, '\\/') . DIRECTORY_SEPARATOR . 'sitemaps.xml';
        }
        return $this->xmlCollectionFile;
    }

    protected function generateXmlCollection()
    {
        $fullPath = $this->getFullPath();

        $files = Utils::getSitemapFiles($fullPath);
        $sitemapsXml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . '<sitemapindex>';
        $uriDir = "http://www.jobuy.com/sitemap/";
        $date = date('Y-m-d');
        foreach ($files as $file) {
            $sitemapsXml .= PHP_EOL . "<sitemap><loc>{$uriDir}{$file}</loc><lastmod>{$date}</lastmod></sitemap>";
        }
        $sitemapsXml .= PHP_EOL . "</sitemapindex>";
        $xmlCollectionFile = $this->getXmlCollectionFile();
        if (file_exists($xmlCollectionFile)) {
            unlink($xmlCollectionFile);
        }
        file_put_contents($xmlCollectionFile, $sitemapsXml);

    }

    public function offsetExists($offset)
    {
    }

    public function offsetGet($offset)
    {
        $config = require(__DIR__ . "/config.php");
        return $config;
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }


    public function __destruct()
    {
           $this->generateXmlCollection();
           $this->unlock();
    }

}