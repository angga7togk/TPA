<?php

namespace angga7togk\tpa;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class TPA extends PluginBase implements Listener
{

    public $listTpa = [];
    public $listTpaHere = [];

    public Config $cfg;

    const prefix = "§6[TPA] §r";

    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveResource("config.yml");
        $this->cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML, []);

        $this->listTpa = [];
        $this->listTpaHere = [];
    }

    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        if (isset($this->listTpa[$player->getName()])) {
            unset($this->listTpa[$player->getName()]);
        }
        if (isset($this->listTpaHere[$player->getName()])) {
            unset($this->listTpaHere[$player->getName()]);
        }
    }

    public function requestTpa(Player $player, $target)
    {
        $targetPlayers = $this->getServer()->getPlayerExact($target);
        if (!isset($targetPlayers)) {
            $player->sendMessage(self::prefix . "§cPlayer is not found!");
            return;
        }

        $this->listTpa[$targetPlayers->getName()] = $player->getName();
        $player->sendMessage(self::prefix . "§aSuccessfully sent a teleport request to " . $targetPlayers->getName());

        $targetPlayers->sendMessage(self::prefix . "§e" . $player->getName() . " Sent a teleport request to you!");  
        $targetPlayers->sendMessage(self::prefix . "§ePlease run command /tpaccept or /tpadeny");
    }

    public function requestTpaHere(Player $player, $target)
    {
        $targetPlayers = $this->getServer()->getPlayerExact($target);
        if (!isset($targetPlayers)) {
            $player->sendMessage(self::prefix . "§cPlayer is not found!");
            return;
        }

        $this->listTpaHere[$targetPlayers->getName()] = $player->getName();
        $player->sendMessage(self::prefix . "§aSuccessfully sent tpahere request to" . $targetPlayers->getName());

        $targetPlayers->sendMessage(self::prefix . "§e" . $player->getName() . " Sent a request to teleport you to  " . $player->getName());
        $targetPlayers->sendMessage(self::prefix . "§ePlease run command /tpaccept or /tpadeny");
    }

    public function getTpaccept(Player $player)
    {   
        if (isset($this->listTpa[$player->getName()])) {
            $targetPlayers = $this->getServer()->getPlayerExact($this->listTpa[$player->getName()]);
            if (!isset($targetPlayers)) {
                return;
            }

            $targetPlayers->teleport($player->getPosition());
            $targetPlayers->sendMessage(self::prefix . "§aSuccesfuly Teleport To " . $player->getName());
            unset($this->listTpa[$player->getName()]);

        } else if (isset($this->listTpaHere[$player->getName()])) {
            $targetPlayers = $this->getServer()->getPlayerExact($this->listTpaHere[$player->getName()]);
            if (!isset($targetPlayers)) {
                return;
            }

            $player->teleport($targetPlayers->getPosition());
            $targetPlayers->sendMessage(self::prefix . "§aSuccesfuly Teleport " . $player->getName() . " to you!");
            unset($this->listTpaHere[$player->getName()]);
        } else {
            $player->sendMessage(self::prefix . "§cPlayer is not found!");
        }
    }

    public function getTpadeny(Player $player)
    {
        if (isset($this->listTpa[$player->getName()])) {
            $targetPlayers = $this->getServer()->getPlayerExact($this->listTpa[$player->getName()]);
            if (!isset($targetPlayers)) {
                return;
            }

            $targetPlayers->sendMessage(self::prefix . "§c" . $player->getName() . " rejected request your tpa!");
            unset($this->listTpa[$player->getName()]);
        } else if (isset($this->listTpaHere[$player->getName()])) {
            $targetPlayers = $this->getServer()->getPlayerExact($this->listTpaHere[$player->getName()]);
            if (!isset($targetPlayers)) {
                return;
            }

            $targetPlayers->sendMessage(self::prefix . "§c" . $player->getName() . " rejected request your tpaHere!");
            unset($this->listTpaHere[$player->getName()]);
        } else {
            $player->sendMessage(self::prefix . "§cPlayer is not found!");
        }
    }

    public function onCommand(CommandSender $sender, Command $cmd, String $label, array $args): bool
    {

        if ($sender instanceof ConsoleCommandSender) {
            return false;
        }

        switch ($cmd->getName()) {
            case "tpa":
                if (!isset($args[0])) {
                    $sender->sendMessage(self::prefix . "§rusage: /tpa <NamePlayer>");
                    return false;
                }
                $this->requestTpa($sender, $args[0]);
                break;
            case "tpahere":
                if (!isset($args[0])) {
                    $sender->sendMessage(self::prefix . "§rusage: /tpahere <NamePlayer>");
                    return false;
                }
                $this->requestTpaHere($sender, $args[0]);
                break;
            case "tpaccept":
                $this->getTpaccept($sender);
                break;
            case "tpadeny":
                $this->getTpadeny($sender);
                break;
        }

        return true;
    }
}
