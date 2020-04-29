<?php

namespace SignShop;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as C;
use pocketmine\event\Listener;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\Item;
use onebone\economyapi\EconomyAPI;
use pocketmine\tile\Sign;
use pocketmine\math\Vector3;

class Main extends PluginBase implements Listener{

    public $prefix = C::GRAY."[".C::DARK_GREEN."TabelaMarket".C::GRAY."]";

    public function onEnable(){
        $this->getLogger()->info($this->prefix." ".C::GREEN."aktif edildi!");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function kurma(SignChangeEvent $ev){
        $g = $ev->getPlayer();
        if($ev->getLine(0) == "market"){
            if($g->isOp()){
                if($ev->getLine(1) != null and $ev->getLine(2) != null and $ev->getLine(3) != null){
                $id = $ev->getLine(1);
                $meta = $ev->getLine(2);
                $item = Item::get($id, $meta);
                $name = $item->getName();
                $fiyat = $ev->getLine(3);
                $ev->setLine(0, $this->prefix);
                $ev->setLine(1, C::GREEN.$name);
                $ev->setLine(2, C::GREEN.$fiyat." ".EconomyAPI::getInstance()->getMonetaryUnit());
                $ev->setLine(3, "");
                }
            }else{
                $g->sendMessage($this->prefix." ".C::RED."Market oluşturmak için yetkilendirilmedin");
            }
        }
    }

    public function tiklama(PlayerInteractEvent $ev){
        $g = $ev->getPlayer();
        $tile = $ev->getBlock()->getLevel()->getTile(new Vector3($ev->getBlock()->getFloorX(), $ev->getBlock()->getFloorY(), $ev->getBlock()->getFloorZ()));
		if($tile instanceof Sign){
            if($tile->getText()[0] == $this->prefix){
                $item = Item::fromString(C::clean($tile->getText()[1]));
                $fiyats = C::clean($tile->getText()[2]);
                $fiyat = intval($fiyats);
                if(EconomyAPI::getInstance()->myMoney($g) >= $fiyat){
                    $g->getInventory()->addItem($item);
                    $g->sendMessage($this->prefix." ".C::DARK_GREEN.$item->getName()." ".C::GREEN."adlı eşyayı ".C::DARK_GREEN.$fiyat.EconomyAPI::getInstance()->getMonetaryUnit().C::GREEN." ödeyerek aldın");
                    EconomyAPI::getInstance()->reduceMoney($g, $fiyat);
                }else{
                    $g->sendMessage($this->prefix." ".C::RED."Bu ürünü almak için yeterli miktarda paran yok!");
                }
            }
        }
    }

    public function kirma(BlockBreakEvent $ev){
        $g = $ev->getPlayer();
        $tile = $ev->getBlock()->getLevel()->getTile(new Vector3($ev->getBlock()->getFloorX(), $ev->getBlock()->getFloorY(), $ev->getBlock()->getFloorZ()));
		if($tile instanceof Sign){
            if($tile->getText()[0] == $this->prefix){
                if($g->isOp()){
                    $g->sendMessage($this->prefix." ".C::GREEN."Market başarıyla kaldırıldı!");
                }else{
                    $g->sendMessage($this->prefix." ".C::RED."Market kaldırmak için yetkilendirilmedin!");
                    $ev->setCancelled(true);
                }
            }
        }
    }
}