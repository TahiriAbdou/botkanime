<?php

interface iBot{
    
    public function url($current,$gateway=false);
    
    public function setup($current,$gateway=false);
    
    public function pages();
    
}
