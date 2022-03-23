<?php

namespace zPearlss;

use pocketmine\plugin\PluginBase;
use zPearlss\task\ReviveCheckTask;
use zPearlss\utils\ConfigUtils;

class RevivePlus extends PluginBase
{

    private static ?self $instance = null;

    protected function onEnable(): void
    {
        self::$instance = $this;

        $this->saveDefaultConfig();

        if(!ConfigUtils::get("PLUGIN_ENABLED")){
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }

        $this->getScheduler()->scheduleRepeatingTask(new ReviveCheckTask(), 20);
        $this->getServer()->getPluginManager()->registerEvents(new ReviveListener(), $this);
    }

    protected function onDisable(): void
    {
        $this->getConfig()->save();
    }

    public static function getInstance(): ?self
    {
        return self::$instance;
    }
}