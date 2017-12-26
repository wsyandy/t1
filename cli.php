<?php
require __DIR__ . '/vendor/autoload.php';
$request_start_at = time();
$di = Phalcon\Di::getDefault();
//Create a console application
$console = new Phalcon\CLI\Console();
$console->setDI($di);


/**
 * Process the console arguments
 */
$arguments = array();
/**
 * 默认触发帮助action
 */
if ($argc < 2) {
    echoLine('arguments error');
    echoLine('usage: php cli.php taskName actionName');
    echoLine("task list:");
    $task_files = glob(APP_ROOT . 'vendor/sh_server/xphalcon/app/tasks/*.php');
    foreach ($task_files as $file) {
        $task_name = preg_replace('/task\.php$/i', '', basename($file));
        $task_name = strtolower($task_name);
        echoLine($task_name);
    }
    $task_files = glob(APP_ROOT . '/app/tasks/*.php');
    foreach ($task_files as $file) {
        $task_name = preg_replace('/task\.php$/i', '', basename($file));
        $task_name = strtolower($task_name);
        echoLine($task_name);
    }
    return;
}
if ($argc < 3) {
    $arguments['task'] = $argv[1];
    $arguments['action'] = 'help';
} else {
    foreach ($argv as $k => $arg) {
        if ($k == 1) {
            $arguments['task'] = $arg;
        } elseif ($k == 2) {
            $arguments['action'] = $arg;
        } elseif ($k >= 3) {
            $arguments['params'][] = $arg;
        }
    }
}
if (isset($arguments['action'])) {
    $arguments['action'] = lcfirst(\Phalcon\Text::camelize($arguments['action']));
}
echoLine('begin execute ' . $arguments['task'] . '#' . $arguments['action']);
try {
    // handle incoming arguments
    $console->handle($arguments);


} catch (\Phalcon\Exception $e) {
    echoLine($e->getMessage());
}

echoLine('process time: ' . sprintf('%0.3f', (microtime(true) - $request_start_at)) . 's');




