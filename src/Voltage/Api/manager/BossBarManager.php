<?php

namespace Voltage\Api\manager;

use Voltage\Api\module\BossBar;

final class BossBarManager
{
    private int $id = 0;
    private array $bossbars = [];

    public function createBossBar(?int $id = null) : int {
        if (is_null($id)) {
            $id = $this->id++;
        }

        if (!$this->issetBossBar($id)) {
            $this->bossbars[$id] = new BossBar();
        }
        return $id;
    }

    public function removeBossBar(int $id) : void {
        if ($this->issetBossBar($id)) {
            unset($this->bossbars[$id]);
        }
    }

    public function getBossBar(int $id) : ?BossBar {
        if ($this->issetBossBar($id)) {
            return $this->bossbars[$id];
        }
        return null;
    }

    public function issetBossBar(int $id) : bool {
        return isset($this->bossbars[$id]);
    }

    /**
     * @return BossBar[]
     */
    public function getAllBossBar() : array {
        return $this->bossbars;
    }

}