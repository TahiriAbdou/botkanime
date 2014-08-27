<?php


class OkanimeController extends BotController{
    
    public function series(){
        $this->setup('category/anime/');
        return $this->pages();
    }
    
}
