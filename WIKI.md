Create a new boss bar
```PHP
/** @var int */
$id = BossBarApi::getManager()->createBossBar(?int $id = null, ?string $title = null, ?string $subtitle = null, ?float $percentage = null, ?int $color= null, ?array $players = null, bool $send = false); //you can define an id if you want
/** @var BossBar */
$bossbar = BossBarApi::getManager()->getBossBar($id);
```

Send a boss bar
```PHP
$bossbar->sendToPlayers(array $players);
$bossbar->sendToAll();
```

Set the title and/or subtitle and/or percentage
```PHP
/** @var BossBar */
$bossbar->setTitleToAll(string $title = "");
$bossbar->setTitleToPlayers(array $players, string $title = "");
/** @var BossBar */
$bossbar->setSubTitleToAll(string $subTitle = "");
$bossbar->setSubTitleToPlayers(array $players, string $subTitle = "");
/** @var BossBar */
$bossbar->setPercentageToAll(float $percentage = 0); //value between 0.00 and 1.00
$bossbar->setPercentageToPlayers(array $players, float $percentage = 0); //value between 0.00 and 1.00
```

Set the Color
```PHP
//The entire color palette in https://github.com/pmmp/BedrockProtocol/blob/master/src/types/BossBarColor.php
use pocketmine\network\mcpe\protocol\types\BossBarColor;

BossBarColor::PINK
BossBarColor::BLUE
BossBarColor::RED
BossBarColor::GREEN
BossBarColor::YELLOW
BossBarColor::PURPLE
BossBarColor::WHITE

/** @var BossBar */
$bossbar->setColorToAll(BossBarColor::BLUE);
$bossbar->setColorToPlayers(array $players, BossBarColor::BLUE);
```

Add and remove players

```PHP
$bossbar->addPlayer(Player $player);
$bossbar->removePlayer(Player $player);

/** @var Player[] $players */
$bossbar->addPlayers(array $players);
$bossbar->removePlayers(array $players);
$bossbar->removeAllPlayers();
```

Get the entity the boss bar is assigned to

```PHP
/** @var Entity|Player $entity */
$bar->getEntity();
```

allows to reload the boss bar

```PHP
/** @var Player[] $players */
$bar->reloadPlayers(array $players);
```

Examples

```PHP
$bossBar = BossBarApi::getManager()->getBossBar(BossBarApi::getManager()->createBossBar(null,"Welcome","to BossBar API",0.5,BossBar::COLOR_GREEN,Server::getInstance()->getOnlinePlayers(),false));
$player = Server::getInstance()->getPlayerExact("voltage");
$bossBar
    ->setColorToPlayers([$player], BossBar::COLOR_PINK)
    ->sendToAll();
```
=
```PHP
$id = BossBarApi::getManager()->createBossBar();
$bossBar = BossBarApi::getManager()->getBossBar($id);
$player = Server::getInstance()->getPlayerExact("voltage");
$bossBar
    ->addPlayers(Server::getInstance()->getOnlinePlayers())
    ->setTitleToAll("Welcome")
    ->setSubTitleToAll("to BossBar API")
    ->setPercentageToAll(0.5)
    ->setColorToAll(BossBar::COLOR_GREEN)
    ->setColorToPlayers([$player], BossBar::COLOR_PINK)
    ->sendToAll();
```

## Community

Active channels:

- Twitter: [@voltagegroups](https://twitter.com/VoltageGroups?t=wSiFVaX5GiHx8Z-LmSC7iQ&s=09)
- Discord: [ntF6gH6NNm](https://discord.gg/ntF6gH6NNm)
- © Voltage-Groups
<div align="center">
  <img src="http://image.noelshack.com/fichiers/2021/39/5/1633118741-logo-no-background.png" height="50" width="50" align="left"></img>
</div>
<br/><br/>

## © Voltage-Groups

Voltage-Groups are not affiliated with Mojang. All brands and trademarks belong to their respective owners. Voltage-Groups is not a Mojang-approved software, nor is it associated with Mojang.