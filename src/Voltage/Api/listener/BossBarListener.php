<?php

namespace Voltage\Api\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\utils\SingletonTrait;
use Voltage\Api\BossBarApi;

class BossBarListener implements Listener
{
    use SingletonTrait;

    private static $pg;

    public function __construct(BossBarApi $pg){
        self::$pg = $pg;
        $pg->getServer()->getPluginManager()->registerEvents($this,$pg);
    }

    public function getPlugin() : BossBarApi {
        return self::$pg;
    }

    //CHANGE LEVEL

    public function onQuit(PlayerQuitEvent $event) : void {
        $player = $event->getPlayer();
        foreach (BossBarApi::getManager()->getAllBossBar() as $bossbar) {
            if ($bossbar->hasPlayer($player)) {
                $bossbar->removePlayer($player);
            }
        }
    }

}