<?php


class OkanimeController extends BotController{
    
    public function series(){
        $list = [];
        $pages = $this->process('category/anime/')
                ->pages();
        foreach($pages as $page){
            $this->process($page);
            foreach(pq('#content .post') as $post){
                $list[] = [
                    'id' => '',
                    'title' => pq($post)->find('.leftttttttttt .page-title2 a')->text(),
                    'url'    => '',
                    'released_at' => '',
                    'release_url' => '',
                    'categories' => ['cat'=>'url'],
                    'episodes_count' => 0,
                    'status' => 1,
                    'site_score'=>'7.0',
                    'user_score'=>'5.0',
                    'thumbnail' => '',
                    'thumbnail_resized' => '',
                ];
            }
        }
        return $list;
    }
 
}
