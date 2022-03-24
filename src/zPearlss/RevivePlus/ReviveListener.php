<?php

namespace zPearlss\RevivePlus;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;
use zPearlss\utils\ConfigUtils;

class ReviveListener implements Listener
{
    private static array $revivablePlayers = [];

    public function entityHit(EntityDamageByEntityEvent $event): void
    {
        $entity = $event->getEntity();
        $damager = $event->getDamager();

        if($entity instanceof Player && $damager instanceof Player){
            if($this->doesPlayerNeedsRevive($entity)){
                $event->cancel();
                return;
            }

            if($event->isCancelled()){
                return;
            }

            if($event->getFinalDamage() >= $entity->getHealth()) {
                $event->cancel();

                if (!ConfigUtils::get("ENTITY_KEEP_INVENTORY")) {
                    $entity->getInventory()->clearAll();
                }

                $entPosition = $entity->getPosition();

                $entity->getNetworkProperties()->setBlockPos(EntityMetadataProperties::PLAYER_BED_POSITION, new BlockPosition($entPosition->getFloorX(), $entPosition->getFloorY(), $entPosition->getFloorZ()));
                $entity->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SLEEPING, true);

                $entity->setImmobile(true);

                self::addReviveablePlayer($entity);
            }
        }
    }

    public static function addReviveablePlayer(Player $player): void
    {
        self::$revivablePlayers[strtolower($player->getName())] = $player;
    }

    public static function removeReviveablePlayer(Player $player): void
    {
        unset(self::$revivablePlayers[strtolower($player->getName())]);
    }

    public static function doesPlayerNeedsRevive(Player $player): bool
    {
        return isset(self::$revivablePlayers[strtolower($player->getName())]);
    }

    public static function getReviveablePlayer(string $player): ?Player
    {
        return self::$revivablePlayers[strtolower($player)] ?? null;
    }

    public static function getReviveablePlayers(): array
    {
        return self::$revivablePlayers;
    }
}