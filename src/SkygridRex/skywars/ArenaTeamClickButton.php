<?php

namespace SkygridRex\skywars;

use pocketmine\math\Vector3 as Vector3;
use pocketmine\level\Position;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\block\Chest;
use pocketmine\item\Item;

class ArenaTeamClickButton {
	public $pgin;
	public $name;
	public $spawnLocation;
	public $arenaName;
	public $type = "join";
	public $action = "teleport";
	public function __construct($pg, $name, $pos, $arena) {
		$this->pgin = $pg;
		$this->name = $name;
		$this->spawnLocation = $pos;
		$this->arenaName = $arena;
	}
	public function save() {
		$path = $this->pgin->getDataFolder () . "arena/teamclickbutton/";
		if (! file_exists ( $path )) {
			// @mkdir ($this->pgin->getDataFolder());
			@mkdir ( $path );
		}
		$data = new Config ( $path . $this->arenaName . "_" . "$this->name.yml", Config::YAML );
		// this should not happen
		$data->set ( "name", $this->name );
		$data->set ( "arenaName", $this->arenaName );
		$data->set ( "type", $this->type );
		$data->set ( "action", $this->action );
		if ($this->spawnLocation != null) {
			$data->set ( "spawnX", $this->spawnLocation->x );
			$data->set ( "spawnY", $this->spawnLocation->y );
			$data->set ( "spawnZ", $this->spawnLocation->z );
		}
		$data->save ();
		$this->log ( " saved - " . $path . "$this->name.yml" );
	}
	public function testSave() {
		$this->name = "TeamClickableButton_1";
		$this->arenaName = "skywarsbase1";
		$this->type = "join";
		$this->action = "teleport";
		$this->spawnLocation = new Vector3 ( 128, 128, 128 );		
		$this->save ();
		$this->log ( "-saved single click button " );
	}
	public function delete() {
		$path = $this->pgin->getDataFolder () . "arena/teamclickbutton/";
		$name = $this->name;
		@unlink ( $path . "$name.yml" );
	}
	public static function loadTeamClickButton($plugin) {
		$path = $plugin->getDataFolder () . "arena/teamclickbutton/";
		if (! file_exists ( $path )) {
			@mkdir ( $this->pgin->getDataFolder () );
			@mkdir ( $path );
			// nothing to load
			return;
		}
		$plugin->getLogger()->info ( "loading team click button on " . $path );
		$teamClickButtons = [ ];
		$handler = opendir ( $path );
		while ( ($filename = readdir ( $handler )) !== false ) {
			//$plugin->getLogger()->info ( "file - " . $filename );			
			//skip sub folders
			if (is_dir($filename)) {
				continue;
			}
							
			if ($filename != "." && $filename != "..") {
				$plugin->getLogger()->info ( "file - " . $filename );				
				$data = new Config ( $path . $filename, Config::YAML );				
				$xname = $data->get ( "name" );
				$name = str_replace ( ".yml", "", $filename );
				$spawnLocation = null;
				if ($data->get ( "spawnX" ) != null) {
					$spawnLocation = new Position ( $data->get ( "spawnX" ), $data->get ( "spawnY" ), $data->get ( "spawnZ" ));
				}				
				$arenaName = $data->get ( "arenaName" );
				$type = $data->get ( "type" );
				$action = $data->get ( "action" );
				
				$clickButton = new ArenaTeamClickButton ( $plugin, $name, $spawnLocation, $arenaName );
				$clickButton->type = $type;
				$clickButton->action = $action;
				$clickButton->arenaName = $arenaName;
				//save key
				$key=$data->get ( "spawnX" )." ".$data->get ( "spawnY" )." ".$data->get ( "spawnZ" );
				$plugin->getLogger()->info($key);
				$teamClickButtons [$key] = $clickButton;
				$plugin->getLogger()->info ( "team click buttons: " . count ( $teamClickButtons ) );
			}
		}
		closedir ( $handler );		
		return $teamClickButtons;
	}
	
	private function log($msg) {
		$this->pgin->getLogger ()->info ( $msg );
	}
}