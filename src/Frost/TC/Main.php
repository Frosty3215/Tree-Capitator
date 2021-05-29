<?php
namespace FrostyRaptor995\FrostTreeCapitator;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;

use pocketmine\event\player\PlayerQuitEvent;
use FrostyRaptor995\FrostTreeCapitator\common\mc;

class Main extends PluginBase implements Listener {
	protected $state;
	protected $modules;

	public function onEnable(){
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		mc::plugin_init($this,$this->getFile());

		$defaults = [
			"version" => $this->getDescription()->getVersion(),
			"modules" => [
				"treecapitator" => true,
			],

			],
			"treecapitator" => [
				"ItemIDs" => [
					"IRON_AXE","WOODEN_AXE", "STONE_AXE",
					"DIAMOND_AXE","GOLD_AXE"
				],
				"need-item" => true,
				"break-leaves" => true,
				"item-wear" => 1,
				"broadcast-use" => true,
				"creative" => true,
			
		];
		$cnt = 0;
		$cfg=(new Config($this->getDataFolder()."config.yml",
									  Config::YAML,$defaults))->getAll();
		if ($cfg["modules"]["treecapitator"])
			$this->modules[]= new TreeCapitator($this,$cfg["treecapitator"]);
		
		if (count($this->modules)) {
			$this->state = [];
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
		}
		$this->getLogger()->info(mc::_("enabled %1% modules",count($this->modules)));
	}

	public function onPlayerQuit(PlayerQuitEvent $ev) {
		$n = strtolower($ev->getPlayer()->getName());
		if (isset($this->state[$n])) unset($this->state[$n]);
	}
	public function getState($label,$player,$default) {
		if ($player instanceof CommandSender) $player = $player->getName();
		$player = strtolower($player);
		if (!isset($this->state[$player])) return $default;
		if (!isset($this->state[$player][$label])) return $default;
		return $this->state[$player][$label];
	}
	public function setState($label,$player,$val) {
		if ($player instanceof CommandSender) $player = $player->getName();
		$player = strtolower($player);
		if (!isset($this->state[$player])) $this->state[$player] = [];
		$this->state[$player][$label] = $val;
	}
	public function unsetState($label,$player) {
		if ($player instanceof CommandSender) $player = $player->getName();
		$player = strtolower($player);
		if (!isset($this->state[$player])) return;
		if (!isset($this->state[$player][$label])) return;
		unset($this->state[$player][$label]);
	}
	public function getItem($txt,$default=0,$msg="") {
		$r = explode(":",$txt);
		if (count($r)) {
			if (!isset($r[1])) $r[1] = 0;
			$item = Item::fromString($r[0].":".$r[1]);
			if (isset($r[2])) $item->setCount(intval($r[2]));
			if ($item->getId() != Item::AIR) {
				return $item;
			}
		}
		if ($default) {
			if ($msg != "")
				$this->getLogger()->warning(mc::_("%1%: Invalid item %2%, using default",$msg,$txt));
			$item = Item::fromString($default.":0");
			$item->setCount(1);
			return $item;
		}
		if ($msg != "")
			$this->getLogger()->warning(mc::_("%1%: Invalid item %2%, ignoring",$msg,$txt));
		return null;
	}
}