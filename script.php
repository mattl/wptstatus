<?php

$hosts = array("ubuntu@54.90.215.143", "ubuntu@52.87.229.158","root@46.43.3.116", "root@46.43.14.27");
$hostnames = array("Chrome", "Firefox", "Safari", "Edge");

$pidscript = "ps aux | grep 'run.p[y]' | head -n 1 | awk '{print $2}'";
$runscript = "ps -o etime= -p ";

$uptime = [];

foreach ($hosts as $host) {

  $output = trim(shell_exec("ssh $host " . $pidscript));
  $output = trim(shell_exec("ssh $host " . $runscript . $output));  
  $uptime[] = $output;

}

$diskspace = [];

foreach ($hosts as $host) {

  $output = trim(shell_exec("ssh $host df --output='pcent' / | tail -n 1"));
  $diskspace[] = $output;

}



$inodespace = [];

foreach ($hosts as $host) {

  $output = trim(shell_exec("ssh $host df --output='ipcent' / | tail -n 1"));
  $inodespace[] = $output;

}


$progress = "ps aux | grep --only-matching -E '\--this-chunk\s*[0-9]+' | grep --only-matching -E '[0-9]+' |     head -n 1";

$progresscount = [];

foreach ($hosts as $host) {

  $output = trim(shell_exec("ssh $host " . $progress));
  $progresscount[] = $output;

}

$total = "ps aux | grep --only-matching -E '\--total-chunks\s*[0-9]+' | grep --only-matching -E '[0-9]+' |     head -n 1";

$totalcount = [];

foreach ($hosts as $host) {

  $output = trim(shell_exec("ssh $host " . $total));
  $totalcount[] = $output;

}


function checkuptime($foo) {

if (substr($foo, 1,1) == "-") {

$foo = "<span style='color:red; font-weight: bold;'>" . $foo . "</span>";

}

return $foo;

}

function runcheck($foo) {

$lastrun = new DateTime($foo);
$now = new DateTime("now");
$interval = $lastrun->diff($now);

$days = $interval->format('%R%a');

if ($days > 2) {

$foo = "<span style='color:red; font-weight: bold;'>" . $foo . "</span>";

}

return $foo;

}

$i=0;
$now = new DateTime();

$output = "<meta http-equiv='refresh' content='45'>";



$table = "<tr><td>" . $now->format('Y-m-d') . "</td>";
$table .= "<td>" . $now->format('H:i:s') . "</td>";

$output .= "<h1>WPT Runner Status (Last updated:" . $now->format('Y-m-d H:i:s') . ")</h1>";

foreach ($hostnames as $hostname) {

$json = file_get_contents("https://wpt.fyi/api/runs?sha=latest&browser=" . strtolower($hostname));

$obj = json_decode($json);
$lastrun = $obj[0];
$lastrun = $lastrun->{'created_at'};
#$lastrun = substr($lastrun,0,10);

$output .= "<h2> " . $hostname . "</h2>";

$output .="<ul>";

$output .= "<li>" . "Last run: " . "<span title='" . $lastrun . "'>" . runcheck(substr($lastrun,0,10)) . "</span></li>";
$output .= "<li>" . "Machine uptime: " . checkuptime($uptime[$i]) . "</li>";
$table .= "<td>" . $uptime[$i] . "</td>";
$output .= "<li>" . "Machine disk usage: " . $diskspace[$i] . "</li>";
$output .= "<li>" . "Machine inode usage: " . $inodespace[$i] . "</li>";
$output .= "</ul>";
$output .= "<p><span style=font-size:200%>" . $progresscount[$i] . "</span><meter title='" . $progresscount[$i] . "/"  . $totalcount[$i] . "' value='" . $progresscount[$i] . "' max='" . $totalcount[$i] . "'></meter><span style=font-size:200%>" . $totalcount[$i] . "</span></p>";
$table .= "<td>" . $progresscount[$i] . "</td>";
$table .= "<td>" . $totalcount[$i] . "</td>";

$output .= "<hr />";



$i++;
}



$output .= "<a href=data.html>Data version (appends with each run)</a>";

$table .="</tr>";



file_put_contents("index.html", $output);
file_put_contents("data.html", $table, FILE_APPEND | LOCK_EX);



?>
