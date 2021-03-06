<?php

#______           _    _____           _                  
#|  _  \         | |  /  ___|         | |                 
#| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
#| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
#| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
#|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
#                             __/ |                       
#                            |___/

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\entity\Effect;
use pocketmine\entity\InstantEffect;
use pocketmine\event\TranslationContainer;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\utils\TextFormat;
use pocketmine\Server;

class EnchantCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.enchant.description",
			"%commands.enchant.usage"
		);
		$this->setPermission("pocketmine.command.enchant");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 2){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
			return true;
		}

		$player = $sender->getServer()->getPlayer($args[0]);

		if($player === null){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
			return false;
		}

		$enchantId = $args[1];
		$enchantLevel = isset($args[2]) ? (int) $args[2] : 1;

		$enchantment = Enchantment::getEnchantment($enchantId);
		if($enchantment->getId() === Enchantment::TYPE_INVALID){
			$enchantment = Enchantment::getEnchantmentByName($enchantId);
			if($enchantment->getId() === Enchantment::TYPE_INVALID){
	    		$sender->sendMessage(new TranslationContainer("commands.enchant.notFound", [$enchantment->getId()]));
				return false;
			}
		}
		$id = $enchantment->getId();
		$maxLevel = Enchantment::getEnchantMaxLevel($id);
		if($enchantLevel <= 0){
			$sender->sendMessage(new TranslationContainer("commands.enchant.maxLevel", [$maxLevel]));
			return false;
		}
		
		$enchantment->setLevel($enchantLevel);

		$item = $player->getInventory()->getItemInHand();

		if($item->getId() <= 0){
			$sender->sendMessage(new TranslationContainer("commands.enchant.noItem"));
			return false;
		}
		
		if(Enchantment::getEnchantAbility($item) === 0){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.enchant.cantEnchant"));
			return false;
		}

		$item->addEnchantment($enchantment);
		$player->getInventory()->setItemInHand($item);
		
		Command::broadcastCommandMessage($sender, new TranslationContainer("%commands.enchant.success"));
		return true;
	}
}