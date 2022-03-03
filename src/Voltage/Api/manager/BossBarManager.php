<?php

namespace Voltage\Api\manager;

use Voltage\Api\module\BossBar;

final class BossBarManager
{
    private int $id = 0;
    private array $bossbars = [];

    /**
     * @param int|null $id
     * @param string|null $title
     * @param string|null $subtitle
     * @param float|null $percentage
     * @param int|null $color
     * @param array|null $players
     * @param bool $send
     * @return int
     */
    public function createBossBar(?int $id = null, ?string $title = null, ?string $subtitle = null, ?float $percentage = null, ?int $color= null, ?array $players = null, bool $send = false) : int {
        if (is_null($id)) {
            $id = $this->id++;
        }

        if (!$this->issetBossBar($id)) {
            $this->bossbars[$id] = new BossBar($title, $subtitle, $percentage, $color, $players, $send);
        }
        return $id;
    }

    /**
     * @param int $id
     */
    public function removeBossBar(int $id) : void {
        if ($this->issetBossBar($id)) {
            unset($this->bossbars[$id]);
        }
    }

    /**
     * @param int $id
     * @return BossBar|null
     */
    public function getBossBar(int $id) : ?BossBar {
        if ($this->issetBossBar($id)) {
            return $this->bossbars[$id];
        }
        return null;
    }

    /**
     * @param int $id
     * @return bool
     */
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