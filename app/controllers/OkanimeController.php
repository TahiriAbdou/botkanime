<?php


class OkanimeController extends BotController{
    
    public function series(){
        $list = [];
        $pages = $this->process('category/anime/')
                ->pages();
        foreach($pages as $page){
            $this->process($page);
            foreach(pq('#content .post') as $post){
                $list[] = ['title'=>pq($post)->find('.leftttttttttt .page-title2 a')->text()];
            }
        }
        return $list;
    }
 
}
