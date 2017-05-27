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

use GE\MoneyPlusAPI;
use GE\FunctionConnectManager;
use pocketmine\utils\Config;

class YamlManager implements FunctionConnectManager{

	protected $m;
	public $c;
	public $mes;
	public $money;

	public function __construct(MoneyPlusAPI $m){
		$this->m = $m;
		if(!file_exists($this->m->getDataFolder())){
			mkdir($this->m->getDataFolder(), 0744, true);
		}
			$this->money = new Config($this->m->getDataFolder() . "money.yml", Config::YAML);
			$this->c = new Config($this->m->getDataFolder() . "config.yml", Config::YAML, array(
				'config-version' => '1',
				'unit' => 'MP',
				'max-money' => '1000000000',
				'default-money' => '500',
				'ranking-op-enable' => 'false',
				'register' => 'あなたのデータを登録しました。',
				'money-received' => '%pさんから%a%b受け取りました。',
				'command-check' => 'あなたの所持金: %a%b',
				'command-view' => '%p の所持金: %a%b',
				'command-pay' => '%p に %a%b 支払いました。',
				'command-rank' => '所持金ランキング',
				'command-throw' => '%a%b 捨てました。',
				'command-give' => '%p の所持金を %a%b 増やしました。',
				'command-take' => '%p の所持金を %a%b 減らしました。',
				'command-set' => '%p の所持金を %a%b に設定しました。',
				'error-type' => '§c正しい形式で入力してください。/m help',
				'error-not' => '§cそのプレイヤーは存在しません。',
				'error-console' => '§cコンソールからは実行できません。',
				'error-money' => '§c所持金が足りません。',
				'error-per' => '§c権限者専用のコマンドです。',
				'help' => array(
					'help-check' => '/m check : 所持金確認',
					'help-view' => '/m view {プレイヤー} : プレイヤーの所持金確認',
					'help-pay' => '/m pay {プレイヤー} {金額} : プレイヤーに金額支払い',
					'help-rank' => '/m rank {ページ数} : 所持金ランキング',
					'help-throw' => '/m throw {金額} : 金額分所持金を捨てます。'
					)
			));


	}
/*所持金取得*/
	public function getMoney(String $name){
		$name = strtolower($name);
		if($this->money->exists($name)){
			return $this->money->get($name);

		}else{
			return false;
		}

	}

/*所持金増やす*/	
	public function addMoney(String $name, int $price){
		$name = strtolower($name);
		if($this->money->exists($name)){
			$hand = $this->money->get($name);
			$result = $hand + $price;

			if($this->c->get("max-money") < $result){
				$result = $this->c->get("max-money");
			}

			$this->money->set($name, $result);
			$this->money->save();

		}else{
			return false;
		}
	}

/*所持金減額wwwwwww*/
	public function takeMoney(String $name, int $price){
		$name = strtolower($name);
		if($this->money->exists($name)){
			$hand = $this->money->get($name);
			$result = $hand - $price;

			if(0 > $result){
				$result = 0;
			}

			$this->money->set($name, $result);
			$this->money->save();

		}else{
			return false;
		}
	}

/*所持金設定*/
	public function setMoney(String $name, int $price){
		$name = strtolower($name);
		if($this->money->exists($name)){

			if(0 > $price){
				$price = 0;
			}

			$this->money->set($name, $price);
			$this->money->save();

		}else{
			return false;
		}
	}

/*データがあるか確認*/
	public function exist(string $name){
		$name = strtolower($name);
	
		return $this->money->exists($name);
	}

/*初回入室時などの際に登録*/
	public function setPlayerData(String $name){
		$name = strtolower($name);
		$price = $this->c->get("default-money");
		$this->money->set($name, $price);
		$this->money->save();

	}
/*取得関係*/
	public function getData(String $key){
		if(!$this->c->exists($key)){
			return "An error occurred in config. Please review the file.";
		}
		return $this->c->get($key);
	}

	public function getAllMoney(){
		return $this->money->getAll();
	}



}