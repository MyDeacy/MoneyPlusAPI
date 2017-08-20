<?php
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
namespace MP;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Utils;

use MP\event\MoneyChangeEvent;
use MP\event\UserRegisterEvent;
use MP\event\UserUnregisterEvent;

use MP\DataAccess\FunctionConnectManager;
use MP\DataAccess\YamlManager;

class MoneyPlusAPI extends PluginBase implements Listener{

/*const の部分はいじらないでください。*/

	const Prefix = "§7[§bMP§7]§f ";

	const Cver = 2;

	Const Version = "2.0.9";

	private static $instance = null;


	public function onLoad(){
		$this->getServer()->getPluginManager()->enablePlugin($this);
	}

	public function onEnable(){
		$this->getLogger()->info("\n\n [§6========== §b MoneyPlus §6 ==========§f]\n§aThank you for using MoneyPlusAPI.\n§cIt is distributed under GNU General Public License v3.0.\n§eAuthor: gigantessbeta[MiYaRiN] §btwitter @gigantessbeta\n");

		self::$instance = $this;

		$this->y = new YamlManager($this);
		
		$newver = $this->checkUpdate();
		if($newver == false){
			$this->getLogger()->alert("A communication error occurred and the update could not be confirmed.\n\n");

		}elseif($newver != MoneyPlusAPI::Version){
			$this->getLogger()->emergency("The latest version has been released!");
			$this->getLogger()->emergency("Please update this old plugin!  Latest version:".$newver."\n\n");

		}

		if($this->y->getData("config-version") != MoneyPlusAPI::Cver){
			$this->getLogger()->emergency(MoneyPlusAPI::Prefix."§c You need to renew the version of Config. Delete the existing Config file, restart it and update it.\n");
		}

		$this->unit = $this->getUnit();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function Join(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		if(!$this->y->exist($name)){
			$player->sendMessage($this->getMessage("register")."");
			$this->y->setPlayerData($name, "this-plugin");
		}
	}

	private function checkUpdate(){
		$result = Utils::getURL("https://raw.githubusercontent.com/gigantessbeta/VersionList/master/MoneyPlusAPI.txt");
		if($result == false){
			return $result;
		}
		return rtrim($result, "\n");
	}

	public function sendHelp($player){
		$a = $this->y->getData("help");
		$player->sendMessage("§l§e[ §bMoneyPlus's help §e]");
		foreach($a as $aho => $b){
			$player->sendMessage("§r§f".$b."");
		}
	}
	public function getMessage($key){
		return "".MoneyPlusAPI::Prefix.$this->y->getData($key);
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		switch(strtolower($command->getName())){
			case "m":
				if(!isset($args[0])){
					$this->sendHelp($sender);
					return true;
				}
				if($sender instanceof Player){
					$smoney = $this->y->getMoney($sender->getName());
					$check = true;
				}else{
					$check = false;
				}
		
				switch($args[0]){
					case "check":
						if($check === true){
							$message = $this->getMessage("command-check");
							$sender->sendMessage(str_replace(array('%a', '%b'), array($smoney, $this->unit), $message));
						}else{
							$sender->sendMessage($this->getMessage("error-console")."");
						}
						return true;
						break;

					case "view":
						if(isset($args[1])){
							if($this->y->exist($args[1])){
								$message = $this->getMessage("command-view");
								$money = $this->y->getMoney($args[1]);
								$sender->sendMessage(str_replace(array('%p', '%a', '%b'), array($args[1], $money, $this->unit),  $message));
							}else{
								$sender->sendMessage($this->getMessage("error-not")."");
							}
						}else{
							$sender->sendMessage($this->getMessage("error-type")."");

						}
						return true;
						break;

					case "pay":
						if($check === true){
							if(isset($args[2]) && is_numeric($args[2])){
								if($this->y->exist($args[1])){
									$message = $this->getMessage("command-pay");
									$result = $smoney - $args[2];

									if($result > 0){
		
										$this->y->takeMoney($sender->getName(), $args[2], "command-pay-send");
								 		$sender->sendMessage(str_replace(array('%p', '%a', '%b'), array($args[1], $args[2], $this->unit),  $message));

										$this->y->addMoney($args[1], $args[2], "command-pay-receive");
										if($this->getServer()->getPlayer($args[0]) != null){
											$message2 = $this->getMessage("money-received");
											$sender->sendMessage(str_replace(array('%p', '%a', '%b'), array($sender->getName(), $args[2], $this->unit),  $message2));
										}
									}else{
										$sender->sendMessage($this->getMessage("error-money")."");
									}
								}else{
									$sender->sendMessage($this->getMessage("error-not")."");
								}

							}else{
								$sender->sendMessage($this->getMessage("error-type")."");
							}
						}else{
							$sender->sendMessage($this->getMessage("error-console")."");
						}
						return true;
						break;

					case "rank":
						if(!isset($args[1])){
							$args[1] = 1;
						}
						if(is_numeric($args[1])){

							$all = $this->y->getAllMoney();
							$max = 0;
							foreach($all as $c){
								$max += count($c);
							}
							$max = ceil(($max / 5));
							$page = max(1, $args[1]);
							$page = min($max, $page);
							$page = (int) $page;
							$sender->sendMessage("===[".$this->y->getData("command-rank")."".$page."/".$max."]===");
							arsort($all);
							$oprank = $this->y->getData("ranking-op-enable");
							$i = 0;
							foreach($all as $a => $b){
								$a = strtolower($a);
								if(isset($this->getServer()->getOps()->getAll()[$a]) && $oprank == "false"){
									continue;
								}
								if(($page - 1) * 5 <= $i && $i <= ($page - 1) * 5 + 4){
									$i1 = $i + 1;
									$sender->sendMessage("".$i1."> ".$a." → ".$b."".$this->unit);
								}
								$i++;
							}
						}else{
							$sender->sendMessage($this->getMessage("error-type")."");
						}					
						return true;
						break;

					case "rankme":
						if($check === true){

							$all = $this->y->getAllMoney();
							arsort($all);
							$oprank = $this->y->getData("ranking-op-enable");
								$i = 0;

							foreach($all as $a => $b){
								$a = strtolower($a);
								if(isset($this->getServer()->getOps()->getAll()[$a]) && $oprank == "false"){
									continue;
								}
									$i1 = $i + 1;
									if($a == strtolower($sender->getName())){
										$sender->sendMessage(str_replace('%l', $i1, $this->getMessage("command-rankme").""));
										return true;
									}
								$i++;
							}
							$sender->sendMessage($this->getMessage("error-rankmeop")."");

						}else{
							$sender->sendMessage($this->getMessage("error-console")."");
						}
						return true;
						break;

					case "throw":
						if($check === true){
							if(isset($args[1]) || is_numeric($args[1])){

								$message = $this->getMessage("command-throw");
								$sender->sendMessage(str_replace(array('%a', '%b'), array($args[1], $this->unit),  $message));
								$this->y->takeMoney($sender->getName(), $args[1], "command-throw");
							}else{
								$sender->sendMessage($this->getMessage("error-type")."");
							}
						}else{
							$sender->sendMessage($this->getMessage("error-console")."");
						}
						return true;
						break;

					case "give":
						if($sender->hasPermission('opcommand')){
							if(isset($args[2]) && is_numeric($args[2])){
								if($this->y->exist($args[1])){
			
									$message = $this->getMessage("command-give");
									$sender->sendMessage(str_replace(array('%p', '%a', '%b'), array($args[1], $args[2], $this->unit),  $message));
									$this->y->addMoney($args[1], $args[2], "command-give");
								}else{
									$sender->sendMessage($this->getMessage("error-not")."");
								}
							}else{
								$sender->sendMessage($this->getMessage("error-type")."");
							}
						}else{
							$sender->sendMessage($this->getMessage("error-per")."");
						}
						return true;
						break;

					case "take":
						if($sender->hasPermission('opcommand')){
							if(isset($args[2]) && is_numeric($args[2])){
								if($this->y->exist($args[1])){
			
									$message = $this->getMessage("command-take");
									$sender->sendMessage(str_replace(array('%p', '%a', '%b'), array($args[1], $args[2], $this->unit),  $message));
									$this->y->takeMoney($args[1], $args[2], "command-take");
								}else{
									$sender->sendMessage($this->getMessage("error-not")."");
								}
							}else{
								$sender->sendMessage($this->getMessage("error-type")."");
							}
						}else{
							$sender->sendMessage($this->getMessage("error-per")."");
						}
						return true;
						break;

					case "set":
						if($sender->hasPermission('opcommand')){
							if(isset($args[2]) && is_numeric($args[2])){
								if($this->y->exist($args[1])){
			
									$message = $this->getMessage("command-set");
									$sender->sendMessage(str_replace(array('%p', '%a', '%b'), array($args[1], $args[2], $this->unit),  $message));
									$this->y->setMoney($args[1], $args[2], "command-set");
								}else{
									$sender->sendMessage($this->getMessage("error-not")."");
								}
							}else{
								$sender->sendMessage($this->getMessage("error-type")."");
							}
						}else{
							$sender->sendMessage($this->getMessage("error-per")."");
						}
						return true;
						break;

					case "help":
						$this->sendHelp($sender);
						return true;
						break;

					default:
						$this->sendHelp($sender);
						return true;
						break;
				}//subcommand switch
			break;
		}//command switch
	}//command function
	

/*------END Main------*/



/*------ API使用用関数群 ------*/

/*MoneyPlusAPIを返す*/
	public static function getInstance(){
		return self::$instance;
	}


/*所持金取得*/
	public function getMoney(String $name){
		return $this->y->getMoney($name);
	}

/*所持金増やす*/	
	public function addMoney(String $name, int $price){
		$this->y->addMoney($name, $price, "Outside");
		
	}

/*所持金減額wwwwwww*/
	public function takeMoney(String $name, int $price){
		$this->y->takeMoney($name, $price, "Outside");
	}

/*所持金設定*/
	public function setMoney(String $name, int $price){
		$this->y->setMoney($name, $price, "Outside");
	}

/*データがあるか確認*/
	public function exist(string $name){
		return $this->y->exist($name);
	}

/*初回入室時などの際に登録*/
	public function setPlayerData(String $name){
		$this->y->setPlayerData($name, "Outside");
	}

	public function removePlayerData(String $name){
			return $this->y->removePlayerData($name, "Outside");
	}
	
/*通貨の単位を取得*/
	public function getUnit(){
		return $this->y->getData("unit");
	}

/*初期所持金を取得*/
	public function getDefaultMoney(){
		return $this->y->getData("default-money");
	}

/*全ユーザーの所持金を一括取得(array)*/
	public function getAllMoney(){
		return $this->y->getAllMoney();
	}
}