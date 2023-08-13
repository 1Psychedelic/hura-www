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

$days = [];
$months = [];
$hours = [];
foreach($history as $commit) {
    $d = $commit['date']->format('l');
    if(!isset($days[$d])) {
        $days[$d] = 0;
    }
    $days[$d]++;
    $m = $commit['date']->format('F');
    if(!isset($months[$m])) {
        $months[$m] = 0;
    }
    $months[$m]++;
    $h = $commit['date']->format('H');
    if(!isset($hours[$h])) {
        $hours[$h] = 0;
    }
    $hours[$h]++;
}
arsort($days, \SORT_NUMERIC);
arsort($months, \SORT_NUMERIC);
arsort($hours, \SORT_NUMERIC);
echo '<pre>';
print_r($days);
print_r($months);
print_r($hours);
echo '</pre>';

//print_r($history);

//die;

$changelog = include __DIR__ . '/../app/changelog.php';

$begin = $history[0]['date'];
$end = (new \DateTime)->modify('+1 day');
//$end = $history[count($history)-1]['date'];//->modify('+1 day');

$interval = DateInterval::createFromDateString('1 day');
$period = new DatePeriod($begin, $interval, $end);

$i = 0;
$hours = 0;
$freeHours = 0;
$freesubtract = 0;
$streak = 0;
$longestStreak = 0;
echo '<table cellpadding="2" border="1" cellspacing="0">';
echo '<tr><td>Den/Hodina</td>';
foreach(range(0, 23) as $hour) {
    echo '<td>' . (strlen($hour) === 1 ? '0' . $hour : $hour) . '</td>';
}
echo '</tr>';
foreach($period as $dt) {
    echo '<tr';
    if(in_array($dt->format('l'), ['Saturday', 'Sunday'])) {
        echo ' style="background-color:#ddf"';
    }
    echo '>';
    echo '<td>' . $dt->format('Y-m-d') . '</td>';
    foreach(range(0, 23) as $hour) {
        $cnt = 0;
        $hourCounted = FALSE;
        $freesubtract++;
        foreach($history as $commit) {
            if($commit['date']->format('Y-m-d') === $dt->format('Y-m-d')) {
                if($commit['date']->format('G') === (string)$hour) {

                    /*$output = [];
                    exec("git diff-tree --name-only --no-commit-id -r " . $commit['hash'], $output);
                    $cnt += count($output);*/

                    $cnt++;
                    if(!$hourCounted) {
                        $hours++;
                        $hourCounted = TRUE;
                        $freesubtract = 0;
                        $streak++;
                    }
                }
            }
        }
        if(!$hourCounted && $hours > 0) {
            $freeHours++;
            if($streak > $longestStreak) {
                $longestStreak = $streak;
            }
            $streak = 0;
        }
        echo '<td style="';
        $color = max(0, 9 - $cnt);
        if($cnt > 0) {
            echo 'background-color:#' . $color . $color . $color;
            //if($cnt > 4) {
            echo ';color:#fff';
            //}
        }
        echo '">' . ($cnt > 0 ? $cnt : '') . '</td>';
    }

    foreach($changelog as $day => $changes) {
        if($day === $dt->format('j. n. Y')) {
            foreach(new RecursiveIteratorIterator(new RecursiveArrayIterator($changes), RecursiveIteratorIterator::LEAVES_ONLY) as $value) {
                $change = strip_tags($value);
                $unchanged = $change;
                if(mb_strlen($change) > 40) {
                    $change = mb_substr($change, 0, 40) . '&hellip;';
                }
                echo '<td style="white-space: nowrap;overflow:hidden;" title="' . htmlspecialchars($unchanged) . '">' . $change . '</td>';
            }
        }
    }

    //echo $dt->format( "l Y-m-d H:i:s\n" );
    echo '</tr>';
}
echo '</table>';

echo '<p>' . $hours . ' hodin práce / ' . ($freeHours - $freesubtract) . ' hodin pauzy/spánku / Rekord ' . $longestStreak . ' hodin v kuse</p>';