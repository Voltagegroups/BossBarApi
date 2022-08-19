<?php

namespace Voltage\Api\module;

use GlobalLogger;
use JetBrains\PhpStorm\Pure;
use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\types\BossBarColor;
use pocketmine\network\mcpe\protocol\types\entity\Attribute as ProtocolAttribute;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;
use pocketmine\Server;

final class BossBar
{
    /** @var Player[] */
    private array $players = [];

    /** @var string */
    private string $title = "";

    /** @var string */
    private string $subTitle = "";

    /** @var int */
    private int $entityId;

    /** @var float */
    private float $percentage = 1.0;

    /** @var int */
    private int $color = BossBarColor::PURPLE;

    /** @var EntityMetadataCollection */
    private EntityMetadataCollection $metadata;

    /** @var BossEventPacket[] */
    private array $packets = [];

    /**
     * @param string|null $title
     * @param string|null $subtitle
     * @param float|null $percentage
     * @param int|null $color
     * @param array|null $players
     * @param bool $send
     */
    public function __construct(?string $title = null, ?string $subtitle = null, ?float $percentage = null, ?int $color = null, ?array $players = null, bool $send = false)
    {
        $this->entityId = Entity::nextRuntimeId();
        $metadata = new EntityMetadataCollection();
        $metadata->setGenericFlag(EntityMetadataFlags::FIRE_IMMUNE, true);
        $metadata->setGenericFlag(EntityMetadataFlags::SILENT, true);
        $metadata->setGenericFlag(EntityMetadataFlags::INVISIBLE, true);
        $metadata->setGenericFlag(EntityMetadataFlags::NO_AI, true);
        $metadata->setString(EntityMetadataProperties::NAMETAG, '');
        $metadata->setFloat(EntityMetadataProperties::SCALE, 0.0);
        $metadata->setLong(EntityMetadataProperties::LEAD_HOLDER_EID, -1);
        $metadata->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, 0.0);
        $metadata->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0.0);
        $this->metadata = $metadata;
        if (!is_null($title)) {
            $this->setTitleToAll($title);
        }
        if (!is_null($subtitle)) {
            $this->setSubTitleToAll($subtitle);
        }
        if (!is_null($percentage)) {
            $this->setPercentageToAll($percentage);
        }
        if (!is_null($color)) {
            $this->setColorToAll($color);
        }
        if (!is_null($players)) {
            $this->addPlayers($players);
        }
        if ($send) {
            $this->sendToAll();
        }
    }

    /**
     * @param Player[] $players
     * @return $this
     */
    public function reloadPlayers(array $players): self
    {
        $this->hideFrom($players);
        $this->showTo($players);
        return $this;
    }

    /**
     * @return Player[]
     */
    public function getPlayers(): array
    {
        return $this->players;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function hasPlayer(Player $player): bool
    {
        return isset($this->players[$player->getId()]);
    }

    /**
     * @param Player[] $players
     * @return self
     */
    public function addPlayers(array $players): self
    {
        foreach ($players as $player) {
            $this->addPlayer($player);
        }
        return $this;
    }

    /**
     * @param Player $player
     * @return $this
     */
    public function addPlayer(Player $player): self
    {
        if ($this->hasPlayer($player)) {
            GlobalLogger::get()->error("Adding the player who is already added to the boss bar [use ->hasPlayer() if you ->addPlayer()] (" . $this . ")");
            return $this;
        }
        if (!$player->spawned) {
            GlobalLogger::get()->error("You want to send a boss bar while your player is not spawning (" . $this . ")");
            return $this;
        }
        if (!$this->getEntity() instanceof Entity) {
            $this->sendSpawnPacket([$player]);
        }
        $this->showTo([$player]);
        $this->players[$player->getId()] = $player;
        $this->sendToPlayers([$player]);
        return $this;
    }

    /**
     * @param Player $player
     * @return $this
     */
    public function removePlayer(Player $player): self
    {
        if (!$this->hasPlayer($player)) {
            GlobalLogger::get()->error("removal of the player who is not in the boss bar [use ->hasPlayer() if you ->removePlayer()] (" . $this . ")");
            return $this;
        }
        $this->hideFrom([$player]);
        $this->sendToPlayers([$player]);
        unset($this->players[$player->getId()]);
        unset($this->packets[$player->getId()]);
        return $this;
    }

    /**
     * @param Player[] $players
     * @return $this
     */
    public function removePlayers(array $players): self
    {
        foreach ($players as $player) {
            $this->removePlayer($player);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function removeAllPlayers(): self
    {
        $this->removePlayers($this->getPlayers());
        return $this;
    }

    /**
     * @return int
     */
    public function getColor(): int
    {
        return $this->color;
    }

    /**
     * @param int $color
     * @return $this
     */
    public function setColorToAll(int $color = BossBarColor::PURPLE): self
    {
        if ($color < BossBarColor::PINK or $color > BossBarColor::WHITE) {
            GlobalLogger::get()->error("Your color identifier is not correct please choose a color between " . BossBarColor::PINK . "-" . BossBarColor::WHITE . " (" . $this . ")");
            return $this;
        }
        $this->color = $color;
        $this->sendColorToAll();
        return $this;
    }

    /**
     * @param Player[] $players
     * @param int $color
     * @return $this
     */
    public function setColorToPlayers(array $players, int $color = BossBarColor::PURPLE): self
    {
        if ($color < BossBarColor::PINK or $color > BossBarColor::WHITE) {
            GlobalLogger::get()->error("Your color identifier is not correct please choose a color between " . BossBarColor::PINK . "-" . BossBarColor::WHITE . " (" . $this . ")");
            return $this;
        }
        $this->color = $color;
        $this->sendColor($players);
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitleToAll(string $title = ""): self
    {
        $this->title = $title;
        $this->sendFullTitleToAll();
        return $this;
    }

    /**
     * @param Player[] $players
     * @param string $title
     * @return $this
     */
    public function setTitleToPlayers(array $players, string $title = ""): self
    {
        $this->title = $title;
        $this->sendFullTitle($players);
        return $this;
    }

    /**
     * @return string
     */
    public function getSubTitle(): string
    {
        return $this->subTitle;
    }

    /**
     * @param string $subTitle
     * @return $this
     */
    public function setSubTitleToAll(string $subTitle = ""): self
    {
        $this->subTitle = $subTitle;
        $this->sendFullTitleToAll();
        return $this;
    }

    /**
     * @param Player[] $players
     * @param string $subTitle
     * @return $this
     */
    public function setSubTitleToPlayers(array $players, string $subTitle = ""): self
    {
        $this->subTitle = $subTitle;
        $this->sendFullTitle($players);
        return $this;
    }

    /**
     * @return string
     */
    public function getFullTitle(): string
    {
        $text = $this->title;
        if (!empty($this->subTitle)) {
            $text .= "\n\n" . $this->subTitle;
        }
        return mb_convert_encoding($text, 'UTF-8');
    }

    /**
     * @param float $percentage
     * @return $this
     */
    public function setPercentageToAll(float $percentage = 0.00): self
    {
        $this->percentage = min(1, $percentage);
        $this->sendHealthToAll();
        return $this;
    }

    /**
     * @param Player[] $players
     * @param float $percentage
     * @return $this
     */
    public function setPercentageToPlayers(array $players, float $percentage = 0.00): self
    {
        $this->percentage = min(1, $percentage);
        $this->sendHealth($players);
        return $this;
    }

    /**
     * @return float
     */
    public function getPercentage(): float
    {
        return $this->percentage;
    }

    /**
     * @param Player[] $players
     * @return $this
     */
    public function hideFrom(array $players): self
    {
        $this->addPlayersPacket($players, BossEventPacket::hide($this->entityId));
        return $this;
    }

    /**
     * @return $this
     */
    public function hideFromAll(): self
    {
        $this->hideFrom($this->getPlayers());
        return $this;
    }

    /**
     * @param Player[] $players
     * @return $this
     */
    public function showTo(array $players): self
    {
        $pk = BossEventPacket::show($this->entityId, $this->getFullTitle(), $this->getPercentage());
        $pk->color = $this->getColor();
        $this->addPlayersPacket($players, $pk);
        return $this;
    }

    /**
     * @return $this
     */
    public function showToAll(): self
    {
        $this->showTo($this->getPlayers());
        return $this;
    }

    /**
     * @param Player[] $players
     * @return $this
     */
    public function sendFullTitle(array $players): self
    {
        $this->addPlayersPacket($players, BossEventPacket::title($this->entityId, $this->getFullTitle()));
        return $this;
    }

    /**
     * @return $this
     */
    public function sendFullTitleToAll(): self
    {
        $this->sendFullTitle($this->getPlayers());
        return $this;
    }

    /**
     * @param Player[] $players
     * @return $this
     */
    public function sendHealth(array $players): self
    {
        $this->addPlayersPacket($players, BossEventPacket::healthPercent($this->entityId, $this->getPercentage()));
        return $this;
    }

    public function sendHealthToAll(): void
    {
        $this->sendHealth($this->getPlayers());
    }

    /**
     * @param Player[] $players
     * @return $this
     */
    public function sendColor(array $players): self
    {
        $this->reloadPlayers($players);
        return $this;
        //I can't change the color

        /* $pk = new BossEventPacket();
         $pk->bossActorUniqueId = $this->entityId;
         $pk->eventType = BossEventPacket::TYPE_TEXTURE;
         $this->color = $this->getColor();
         $this->addPlayersPacket($players, $pk);*/
    }

    public function sendColorToAll(): void
    {
        $this->sendColor($this->getPlayers());
    }

    /**
     * @return Entity|null
     */
    public function getEntity(): ?Entity
    {
        return Server::getInstance()->getWorldManager()->findEntity($this->entityId);
    }


    /**
     * @param Player[] $players
     * @return $this
     */
    public function sendToPlayers(array $players): self
    {
        foreach ($players as $player) {
            $id = $player->getId();

            if (isset($this->packets[$id])) {
                if (isset($this->players[$id])) {
                    $player = $this->players[$id];

                    if ($player instanceof Player) {
                        Server::getInstance()->broadcastPackets([$player], $this->packets[$id]);
                    }
                }
                unset($this->packets[$id]);
            }
        }
        return $this;
    }

    public function sendToAll(): void
    {
        $this->sendToPlayers($this->getPlayers());
    }

    /**
     * @param Player[] $players
     */
    protected function sendSpawnPacket(array $players): void
    {
        $pk = AddActorPacket::create(
            $this->entityId,
            $this->entityId,
            EntityIds::SLIME,
            new Vector3(0, 0, 0),
            null,
            0.0,
            0.0,
            0.0,
            0.0,
            [new ProtocolAttribute(Attribute::HEALTH, 0.0, 100.0, 100.0, 100.0, [])],
            $this->getMetadata()->getAll(),
            []
        );
        foreach ($players as $player) {
            $pkc = clone $pk;
            $pkc->position = $player->getPosition()->asVector3();
            $player->getNetworkSession()->sendDataPacket($pkc);
        }
    }

    /**
     * @param Player[] $players
     * @param BossEventPacket $pk
     */
    private function addPlayersPacket(array $players, BossEventPacket $pk)
    {
        foreach ($players as $player) {
            if ($player instanceof Player) {
                if ($player->isConnected()) {
                    $pk->playerActorUniqueId = $player->getId();
                }
            }
            if (!isset($this->packets[$player->getId()])) {
                $this->packets[$player->getId()] = [];
            }
            $this->packets[$player->getId()][$pk->eventType] = $pk;
        }
    }

    public function getMetadata(): EntityMetadataCollection
    {
        return $this->metadata;
    }

    #[Pure] public function __toString(): string
    {
        return
            __CLASS__ .
            " ID: $this->entityId, " .
            "Players(" . count($this->players) . "): " . implode(", ", array_keys($this->players)) . ", " .
            "Title: '" . $this->getTitle() . "', " .
            "Subtitle: '" . $this->getSubTitle() . "', " .
            "Percentage: '" . $this->getPercentage() . "', " .
            "Color: '" . $this->getColor() . "'";
    }

}