<p align="center">
  <img src="http://image.noelshack.com/fichiers/2021/39/5/1633118741-logo-no-background.png" alt="Voltage logo" height="180" />
</p>

<h1 align="center">Voltage-Groups</h1>
<a href="https://discord.gg/ntF6gH6NNm"><img src="https://img.shields.io/discord/814507789656784898?label=discord&color=7289DA&logo=discord" alt="Discord" /></a>
<br/>
----------------------
<br/>

## Info
> Note: The `main` branch may be in an unstable or even broken state during development.
<br/>

Versions they are available [here](https://github.com/Voltagegroups/BossBarApi/releases)
<br/>
Branch stable for [PM4](https://github.com/Voltagegroups/BossBarApi/tree/pm4)


## Api
A very basic example can be seen here: [BossBarHud](https://github.com/Voltagegroups/BossBarHud)

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
//The entire color palette
BossBar::COLOR_PINK
BossBar::COLOR_BLUE
BossBar::COLOR_RED
BossBar::COLOR_GREEN
BossBar::COLOR_YELLOW
BossBar::COLOR_PURPLE
BossBar::COLOR_WHITE

/** @var BossBar */
$bossbar->setColorToAll(BossBar::COLOR_BLUE);
$bossbar->setColorToPlayers(array $players, BossBar::COLOR_BLUE);
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

## Contents

- [Features](./FEATURES.md)
- [License](./LICENSE)

## Usages

* [PocketMine-MP](https://github.com/pmmp/PocketMine-MP)

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
