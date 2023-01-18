<?php

namespace Voltage\Api\listener;

use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use Voltage\Api\BossBarApi;

class BossBarListener implements Listener
{
    use SingletonTrait;

    /** @var BossBarApi */
    private static BossBarApi $pg;

    /**
     * @param BossBarApi $pg
     */
    public function __construct(BossBarApi $pg){
        self::$pg = $pg;
        $pg->getServer()->getPluginManager()->registerEvents($this,$pg);
    }

    /** @return BossBarApi */
    public function getPlugin() : BossBarApi {
        return self::$pg;
    }

    public function onChange(EntityTeleportEvent $event) : void {
        if (!$event->isCancelled() and $event->getEntity() instanceof Player) {
            $player = $event->getEntity();
            if ($event->getFrom()->getWorld()->getId() !== $event->getTo()->getWorld()->getId()) {
                foreach (BossBarApi::getManager()->getAllBossBar() as $bossbar) {
                    if ($bossbar->hasPlayer($player)) {
                        $bossbar->showTo($player)->sendTo($player);
                    }
                }
            }
        }
    }

    public function onQuit(PlayerQuitEvent $event) : void {
        $player = $event->getPlayer();
        foreach (BossBarApi::getManager()->getAllBossBar() as $bossbar) {
            if ($bossbar->hasPlayer($player)) {
                $bossbar->removePlayer($player);
            }
        }
    }
}