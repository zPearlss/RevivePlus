<?php

namespace zPearlss\task;

use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use zPearlss\ReviveListener;
use zPearlss\utils\ConfigUtils;

class ReviveCheckTask extends Task
{
    private array $timers = [];

    public function onRun(): void
    {
        $maxDistance = ConfigUtils::get("REVIVE_RADIUS") ?? 5;
        $revTime = ConfigUtils::get("REVIVE_TIME") ?? 5;
        if(!empty(($revList = ReviveListener::getReviveablePlayers()))){
            foreach ($revList as $user){
                $lowerName = strtolower($user->getName());
                if(($closestEntity = $user->getWorld()->getNearestEntity($user->getPosition(), $maxDistance)) !== null){
                    if(ConfigUtils::get("BEING_REVIVED_SCORETAG_ENABLED")) {
                        $tag = ConfigUtils::get("BEING_REVIVED_SCORETAG");
                        $user->setScoreTag($tag);
                    }

                    if(!isset($this->timers[$lowerName])){
                        $this->timers[$lowerName] = $revTime;
                    }

                    if($this->timers[$lowerName] > 0){
                        $this->timers[$lowerName]--;
                    }else{
                        $entPosition = $user->getPosition();

                        $user->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SLEEPING, false);
                        $user->setImmobile(false);

                        if(ConfigUtils::get("RESTORE_HEALTH_AFTER_REVIVE")){
                            $user->setHealth($user->getMaxHealth());
                        }

                        if (ConfigUtils::get("RESTORE_FOOD_AFTER_REVIVE")){
                            $user->getHungerManager()->setFood(20);
                        }

                        ReviveListener::removeReviveablePlayer($user);
                        unset($this->timers[$lowerName]);
                    }
                }else{
                    if(isset($this->timers[$lowerName])){
                        unset($this->timers[$lowerName]);
                    }

                    if(ConfigUtils::get("NEEDS_REVIVE_SCORETAG_ENABLED")) {
                        $tag = ConfigUtils::get("NEEDS_REVIVE_SCORETAG");
                        $user->setScoreTag($tag);
                    }
                }
            }
        }
    }
}