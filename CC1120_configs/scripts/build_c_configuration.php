#!/usr/bin/php
<?php

// $reg[REG_NAME] = REG_ADDRESS
include("register_names.php");

$registersToSet = array("DEVIATION_M",
"MODCFG_DEV_E", "CHAN_BW", "MDMCFG0", "SYMBOL_RATE2", "SYMBOL_RATE1",
"SYMBOL_RATE0", "FS_CFG", "PA_CFG2", "PA_CFG1", "PA_CFG0",
"FREQOFF_CFG", "FREQ2", "FREQ1", "FREQ0");


//Print Modem Configuration
echo "\n\n///RH_CC1120::setModemRegisters()\n";
foreach ($registersToSet as $value) {
  $regPosition = str_pad(strtoupper(str_replace("0x","",dechex($reg[$value]))), 4, "0", STR_PAD_LEFT);
  echo "spiWriteRegister(RH_CC1120_REG_".$regPosition."_$value, config->reg_".strtolower($regPosition).");\n";
}





//ModemConfig
echo "\n\n///ModemConfig typedef\n";
echo "  typedef struct
  {\n";
foreach ($registersToSet as $value) {
  $regPosition = str_pad(strtoupper(str_replace("0x","",dechex($reg[$value]))), 4, "0", STR_PAD_LEFT);
  echo "    uint8_t reg_".strtolower($regPosition)."; ///< RH_CC1120_REG_".$regPosition."_".$value."\n";
}
echo "  } ModemConfig;\n";





//Canned configurations
echo "\n\n///Canned configs\n";
$configs = array();

//Load each config into an array from .py
foreach (glob("../RH_*.py") as $config) {
  $re = '/RH_CC1120_REG_(.+)_(.+)\':\ *0x(.+),/mU';
  preg_match_all($re, file_get_contents($config), $matches, PREG_SET_ORDER, 0);
  $nickName = buildNickName($config);
  foreach ($matches as $group){
    $regAddress = $group[1];
    $regName = $group[2];
    $regValue = $group[3];
    $configs[$nickName][$regName] = $regValue;
  }
}


$configuredNicks = array();//Printed nicknames
//Print canned configs
echo "PROGMEM static const RH_CC1120::ModemConfig
    MODEM_CONFIG_TABLE[] =
{\n";
  foreach ($configs as $nickName => $config){
    $configuredNicks[] = $nickName;
    echo "      {";
      echo implode(", ", array_map(function ($register) use ($config) { return "0x".$config[$register]; }, $registersToSet));
      echo "}, // $nickName \n";
  }
  echo "};\n";



//Modem Config Choices:
echo "\n\n///Modem config choices\n";
echo "  typedef enum
  {\n";
    foreach ($configuredNicks as $i => $name){
      if ($i!=0) echo ",\n";
      echo "    ".$name;
      if ($i==0){
        echo " = 0";
      }

    }

echo "\n  } ModemConfigChoice;";
echo "\n\n";



function buildNickName($fileName){
  return basename($fileName, ".py");
}
