#!/usr/bin/php
<?php
# This script is helpful to compare 2 .xml representation from SmartRF in order to identify differences

if (!isset($argv[1]) || !isset($argv[2]) || !file_exists($argv[1]) || !file_exists($argv[2]))
die("Tool used to compare 2 xml representation obtained from SmartRF\nusage ./script.php file1.xml file2.xml [print RFQuack commands]\n");

$filename1 = $argv[1];
$filename2 = $argv[2];

//Get content of a tag:  <TAG>content</TAG>
function tagContent($tag, $str) {
  $re = '/<'.$tag.'>(.+)<\/'.$tag.'>/sU';
  preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
  return array_map(function($value){
    return $value[1];
  }, $matches);
}

//Represent content of SmartRF xml as array
function registersAsArray($str){
  $out = array();
  foreach (tagContent("Register", $str) as $register) {
    $name = (tagContent("Name", $register))[0];
    $value = intval((tagContent("Value", $register)[0]),0);
    $out[$name] = $value;
  }
  return $out;
}

function findDifferences($a1, $a2){
  $differences = array();
  foreach ($a1 as $key => $value){
    if (!isset($a2[$key]) || $a2[$key] !== $a1[$key]){
      $differences[$key] = true;
    }
  }

  foreach ($a2 as $key => $value){
    if (!isset($a1[$key]) || $a2[$key] !== $a1[$key]){
      $differences[$key] = true;
    }
  }
  return $differences;
}

$file1 = registersAsArray(file_get_contents($filename1));
$file2 = registersAsArray(file_get_contents($filename2));
$differences = findDifferences($file1, $file2);

if (count($differences) == 0) exit("There are no differences.\n");
$maxLen = min(max(strlen($filename1), strlen($filename2), 20),50);

echo "Differences in files:\n\n";

echo "\e[0;31;42m" . str_pad("Reg Name",$maxLen) . "\t" . str_pad($filename1,$maxLen)."\t".str_pad($filename2,$maxLen)." \e[0m \n";
foreach ($differences as $regName => $value) {
  echo str_pad($regName,$maxLen)."\t";

  if (isset($file1[$regName])){
    echo str_pad(dechex($file1[$regName]),$maxLen);
  }else{
    echo str_pad("missing",$maxLen);
  }
    echo "\t";

  if (isset($file2[$regName])){
    echo str_pad(dechex($file2[$regName]),$maxLen);
  }else{
    echo str_pad("missing",$maxLen);
  }
  echo "\n";
}
echo "\n";

if (isset($argv[3])){
  include("register_names.php");

  echo "RFQuack commands in order to apply $filename1 starting from $filename2\n\n";
  foreach ($differences as $regName => $value) {
    if (isset($file1[$regName])){
      echo "q.set_register(0x".dechex($reg[$regName]).", 0x".dechex($file1[$regName])."); time.sleep(0.4); #".$regName."\n";
    }
  }
}else{
  echo "If you need RFQuack commands in order to apply this configuration look at help.\n";
}
echo "\n";
