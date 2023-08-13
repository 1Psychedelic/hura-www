<?php

$output = [];
chdir(__DIR__ . '/..');
exec("git log --all",$output);
$history = [];
$commit = [
    'message' => ''
];
foreach($output as $line){
    //echo $line . "\n";
    if(strpos($line, 'commit') === 0) {
        if(!empty($commit['message'])) {
            $commit['message'] = trim($commit['message']);
            $history[] = $commit;
            $commit = [
                'message' => ''
            ];
        }
        $commit['hash'] = trim(substr($line, 7));
    } else if(strpos($line, 'Author')===0){
        $commit['author'] = trim(substr($line, 8));
    } else if(strpos($line, 'Date')===0){
        $commit['date']   = new \DateTime(trim(substr($line, 6)));
    } else{
        $commit['message']  .= $line;
    }
}
$history[] = $commit;
$history = array_reverse($history);
setlocale(\LC_ALL , 'Czech_Czech Republic.1250');



$begin = $history[0]['date'];
$end = (new \DateTime)->modify('+1 day');
//$end = $history[count($history)-1]['date'];//->modify('+1 day');

$interval = DateInterval::createFromDateString('1 day');
$period = new DatePeriod($begin, $interval, $end);

$year = NULL;
$month = NULL;
$temp = NULL;
$counts = 0;
foreach($period as $dt) {
    $counts = 0;
    /** @var \DateTime $dt */
    if($dt->format('Y') !== $year) {
        $year = $dt->format('Y');
        echo '<h3>' . $year . '</h3>';
    }
    if($dt->format('m') !== $month) {
        $month = $dt->format('m');
        echo '<div style="width:16.6%;display:inline-block;text-align:center">';
        echo '<h4>' . (int)$month . '</h4>';
        echo '<table cellpadding="2" border="1" cellspacing="0">';
        echo '<tr>';
        if((int)$dt->format('N') !== 1) {
            foreach(range(1, (int)$dt->format('N')-1) as $dayInWeek) {
                echo '<td></td>';
            }
        }
    } else {
        if((int)$dt->format('N') === 1) {
            echo '<tr>';
        }
    }

    foreach($history as $commit) {
        if($commit['date']->format('Y-m-d') === $dt->format('Y-m-d')) {
            $counts++;
        }
    }

    $color = max(0, ceil(9 - ($counts/3)));
    echo '<td' . ($counts > 0 ? ' style="background-color:#' . $color . $color . $color . ';color:#fff"' : '') . '>' . (int)$dt->format('d') . '</td>';

    if((int)$dt->format('N') === 7) {
        echo '</tr>';
    }
    $temp = clone $dt;
    if($temp->modify('last day of this month')->format('d') === $dt->format('d')) {
        if($dt->format('N') !== 7) {
            foreach(range((int)$dt->format('N') + 1, 7) as $dayInWekk) {
                echo '<td></td>';
            }
        }
        echo '</tr></table></div>';
    }
}
