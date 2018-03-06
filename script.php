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


$i=0;
$now = new DateTime();

$table = "<tr><td>" . $now->format('Y-m-d') . "</td>";
$table .= "<td>" . $now->format('H:i:s') . "</td>";

$output = "<h1>WPT Runner Status</h1>";


foreach ($hostnames as $hostname) {

$output .= "<h2> " . $hostname . "</h2>";

$output .= "<p>" . "Machine uptime: " . $uptime[$i] . "</p>";
$table .= "<td>" . $uptime[$i] . "</td>";
$output .= "<p>" . "Machine disk usage: " . $diskspace[$i] . "</p>";
$output .= "<p>" . "Machine inode usage: " . $inodespace[$i] . "</p>";
$output .= "<p><span style=font-size:200%>" . $progresscount[$i] . "</span><meter title='" . $progresscount[$i] . "/"  . $totalcount[$i] . "' value='" . $progresscount[$i] . "' max='" . $totalcount[$i] . "'></meter><span style=font-size:200%>" . $totalcount[$i] . "</span></p>";
$table .= "<td>" . $progresscount[$i] . "</td>";
$table .= "<td>" . $totalcount[$i] . "</td>";

$output .= "<hr />";



$i++;
}



$output .= "<address>Last updated:" . $now->format('Y-m-d H:i:s') . "</address>";
$output .= "<a href=data.html>Data version (appends with each run)</a>";

$table .="</tr>";



file_put_contents("index.html", $output);
file_put_contents("data.html", $table, FILE_APPEND | LOCK_EX);



?>
