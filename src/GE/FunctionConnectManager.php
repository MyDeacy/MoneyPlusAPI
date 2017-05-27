<?php

namespace GE;

/*                                                                                 

___  ___                      ______ _           
|  \/  |                      | ___ \ |          
| .  . | ___  _ __   ___ _   _| |_/ / |_   _ ___ 
| |\/| |/ _ \| '_ \ / _ \ | | |  __/| | | | / __|
| |  | | (_) | | | |  __/ |_| | |   | | |_| \__ \
\_|  |_/\___/|_| |_|\___|\__, \_|   |_|\__,_|___/
                          __/ |                  
                         |___/                   
by gigantessbeta[みやりん]
*/

use GE\YamlManager;

interface FunctionConnectManager{

	public function getMoney(String $name);
	public function addMoney(String $name, int $price);
	public function takeMoney(String $name, int $price);
	public function setMoney(String $name, int $price);
	public function setPlayerData(String $name);
	public function exist(string $name);
	public function getData(String $key);
	public function getAllMoney();
	
}