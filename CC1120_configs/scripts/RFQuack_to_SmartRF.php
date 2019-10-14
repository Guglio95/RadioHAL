#!/usr/bin/php
<?php
# This script converts the output of CC1120:printRegisters() to SmartRF format.
include("register_names.php");


if (!isset($argv[1]) || !file_exists($argv[1]))
die("This script converts the output of CC1120:printRegisters() to SmartRF format.\n
Usage ./script.php rfquack_export.txt > SmartRF_output.xml\n");

$filename = $argv[1];
?>
<?xml version="1.0" encoding="ISO-8859-1"?>
<!DOCTYPE configuration SYSTEM "C:/Program Files (x86)/Texas Instruments/SmartRF Tools/SmartRF Studio 7/config/xml/configdata.dtd"[]>
<dcpanelconfiguration>
    <Devicename>CC1200</Devicename>
    <Description>Saved configuration data</Description>
    <registersettings>
<?php
  $content = file_get_contents($filename);
  foreach (file($filename) as $fileRow) {
    $regNumber = intval(("0x".explode(": ",$fileRow)[0]),0);
    $regContent = "0x".preg_replace("/\r\n|\r|\n/", "", explode(": ",$fileRow)[1]);
    $regName = array_search($regNumber, $reg);
    //echo "Reg number $regNumber is named $regName; content is $regContent\n";
    ?>
    <Register>
        <Name><?php echo $regName; ?></Name>
        <Value><?php echo $regContent; ?></Value>
    </Register>
    <?php
  }
?>
</registersettings>
</dcpanelconfiguration>
