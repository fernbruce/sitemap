<?php


namespace sitemap\core;


use yii\db\Exception;

class Scheduler
{
    protected static $list = [];

    public static function addTask(\Generator $gen)
    {
        array_push(self::$list, $gen);
    }

    public static function getTask()
    {

        return self::$list;

    }

    public static function run()
    {

        while (isset(self::$list) && !empty(self::$list)) {
            $gen = array_shift(self::$list);
            $gen->send(null);
            if ($gen->valid()) {

                array_push(self::$list, $gen);
            }
        }

    }

    //to do task
    public static function task(TaskInterface $task)
    {

        $taskData = $task->taskData;//二位数组
        $taskNo = $task->taskNo;//任务号
        $absoluteRoot = $task->absoluteRoot;//根路径
        $path = $task->path;//中间目录路径
        $filePrefix = $task->filePrefix;
        $xmlHeader = $task->xmlHeader;
        $xmlFooter = $task->xmlFooter;

        if (empty($filePrefix)) {
            $filePrefix = "sitemaps_";
        }

        $fileName = $filePrefix . sprintf("%03s", $taskNo) . '.xml';//文件名称
        $fullPath = $absoluteRoot . $path . $fileName;

        file_put_contents($fullPath, $xmlHeader, FILE_APPEND);
        yield;
        foreach ($taskData as $key => $val) {

            $xml = ($task->callable)($val);

            file_put_contents($fullPath, $xml, FILE_APPEND);
            yield;
        }

        file_put_contents($fullPath, $xmlFooter, FILE_APPEND);
        yield;

    }
}