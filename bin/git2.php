<?php

require __DIR__ . '/../vendor/autoload.php';

$unsorted = [];
$files = \Nette\Utils\Finder::findFiles('*.php', '*.latte')->from(__DIR__ . '/../app');

foreach($files as $file) {
    /** @var SplFileInfo $file */
    $path = str_replace('Z:\\UniServer7\\www\\vcd2\\', '', $file->getRealPath());

    $output = [];
    chdir(__DIR__ . '/..');
    exec("git log -1 --date=short --format=%cd " . $path, $output);

    if(!isset($output[0])) {
        continue;
    }

    $date = new \DateTimeImmutable($output[0]);
    $unsorted[$path] = $date;
}

uasort($unsorted, function(\DateTimeImmutable $a, \DateTimeImmutable $b) {
    return $a <=> $b;
});

$sorted = [];
foreach($unsorted as $key => $val) {
    $sorted[$key] = $val->format('Y-m-d');
}

\Tracy\Debugger::dump($sorted);
