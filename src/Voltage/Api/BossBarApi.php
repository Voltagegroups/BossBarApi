<?php

namespace Voltage\Api;

use pocketmine\plugin\PluginBase;
use Voltage\Api\listener\BossBarListener;
use Voltage\Api\manager\BossBarManager;

class BossBarApi extends PluginBase
{
    private static BossBarManager $manager;

    /**
     * @return BossBarManager
     */
    public static function getManager() : BossBarManager {
        return self::$manager;
    }

    public function onLoad(): void
    {
        self::$manager = new BossBarManager();
    }

    public function onEnable(): void
    {
        new BossBarListener($this);
    }
}