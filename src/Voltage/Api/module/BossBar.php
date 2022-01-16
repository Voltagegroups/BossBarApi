<?php

namespace Voltage\Api\module;

use GlobalLogger;
use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\types\entity\Attribute as ProtocolAttribute;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;
use pocketmine\Server;

final class BossBar
{
    public const COLOR_PINK = 0;
    public const COLOR_BLUE = 1;
    public const COLOR_RED = 2;
    public const COLOR_GREEN = 3 ;
    public const COLOR_YELLOW = 4;
    public const COLOR_PURPLE = 5;
    public const COLOR_WHITE = 6;

    /** @var Player[] */
    private array $players = [];

    private string $title = "";
    private string $subTitle = "";

    private int $entityId;
    private float $percentage = 1.0;
    private int $color = self::COLOR_PURPLE;

    private EntityMetadataCollection $metadata;

    private array $packets = [];

    public function __construct()
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
    }

    /**
     * @return Player[]
     */
    public function getPlayers(): array
    {
        return $this->players;
    }

    public function hasPlayer(Player $player) : bool {
        return isset($this->players[$player->getId()]);
    }

    /**
     * @param Player[] $players
     * @return self
     */
    public function addPlayers(array $players) : self
    {
        foreach ($players as $player) {
            $this->addPlayer($player);
        }
        return $this;
    }

    public function addPlayer(Player $player): self
    {
        if ($this->hasPlayer($player)) {
            GlobalLogger::get()->error("Adding the player who is already added to the boss bar (" . $this . ")");
            return $this;
        }
        if (!$this->getEntity() instanceof Player) {
            $this->sendSpawnPacket([$player]);
        }
        $this->showTo([$player]);
        $this->players[$player->getId()] = $player;
        $this->sendToPlayers([$player]);
        return $this;
    }

    public function removePlayer(Player $player): self
    {
        if (!$this->hasPlayer($player)) {
            GlobalLogger::get()->error("removal of the player who is not in the boss bar (" . $this . ")");
            return $this;
        }
        $this->hideFrom([$player]);
        unset($this->players[$player->getId()]);
        unset($this->packets[$player->getId()]);
        $this->sendToPlayers([$player]);
        return $this;
    }

    public function removePlayers(array $players): self
    {
        foreach ($players as $player) {
            $this->removePlayer($player);
        }
        return $this;
    }

    public function removeAllPlayers(): self
    {
        foreach ($this->getPlayers() as $player) $this->removePlayer($player);
        return $this;
    }

    public function getColor(): int
    {
        return $this->color;
    }

    public function setColor(int $color = self::COLOR_PURPLE): self
    {
        $this->color = $color;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title = ""): self
    {
        $this->title = $title;
        return $this;
    }

    public function getSubTitle(): string
    {
        return $this->subTitle;
    }

    public function setSubTitle(string $subTitle = "") : self
    {
        $this->subTitle = $subTitle;
        return $this;
    }

    public function getFullTitle() : string
    {
        $text = $this->title;
        if (!empty($this->subTitle)) {
            $text .= "\n\n" . $this->subTitle;
        }
        return mb_convert_encoding($text, 'UTF-8');
    }

    public function setPercentage(float $percentage) : self
    {
        $this->percentage = min(1, $percentage);
        return $this;
    }

    public function getPercentage(): float
    {
        return $this->percentage;
    }

    public function hideFrom(array $players): void
    {
        $this->addPlayersPacket($players,BossEventPacket::hide($this->entityId));
    }

    public function hideFromAll(): void
    {
        $this->hideFrom($this->getPlayers());
    }

    public function showTo(array $players): void
    {
        $pk = BossEventPacket::show($this->entityId,$this->getFullTitle(),$this->getPercentage());
        $pk->color = $this->getColor();
        $this->addPlayersPacket($players, $pk);
    }

    public function showToAll(): void
    {
        $this->showTo($this->getPlayers());
    }

    public function sendFullTitle(array $players) : void {
        $this->addPlayersPacket($players,BossEventPacket::title($this->entityId,$this->getFullTitle()));
    }

    public function sendFullTitleToAll() : void {
        $this->sendFullTitle($this->getPlayers());
    }

    public function sendHealth(array $players): void
    {
        $this->addPlayersPacket($players, BossEventPacket::healthPercent($this->entityId, $this->getPercentage()));
    }

    public function sendHealthToAll(): void
    {
        $this->sendHealth($this->getPlayers());
    }

    public function getEntity(): ?Entity
    {
        return Server::getInstance()->getWorldManager()->findEntity($this->entityId);
    }

    public function sendToPlayers(array $players) : void {
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
    }

    public function sendToAll() : void {
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
            new Vector3(0,0,0),
            null,
            0.0,
            0.0,
            0.0,
            [new ProtocolAttribute(Attribute::HEALTH, 0.0, 100.0, 100.0, 100.0)],
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
     * @param array $players
     * @param DataPacket $pk
     */
    private function addPlayersPacket(array $players, DataPacket $pk) {
        foreach ($players as $player) {
            if (isset($this->packets[$player->getId()])) {
                $this->packets[$player->getId()][] = $pk;
            } else {
                $this->packets[$player->getId()] = [$pk];
            }
        }
    }

    public function getMetadata() : EntityMetadataCollection
    {
        return $this->metadata;
    }

    public function __toString(): string
    {
        return
            __CLASS__ .
            " ID: $this->entityId, " .
            "Players(" . count($this->players) . "): " . implode(", ",array_keys($this->players)) . ", " .
            "Title: '" . $this->title . "', " .
            "Subtitle: '" . $this->subTitle . "', " .
            "Percentage: '" . $this->getPercentage() . "'";
    }

}