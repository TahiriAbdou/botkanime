<?php

class BotController extends Controller
{
    protected $gateway = 'http://okanime.com/';
    protected $provider = '';

    protected $url;
    protected $doc;
    protected $current;

    public function process($current)
    {
        $this->url($current);
        $i = 'links.'.$this->current;
        if (!Session::has($i)) {
            Session::put($i, phpQuery::newDocumentFileHTML($this->url)->html());
        }
        $this->doc = Session::get($i);
        phpQuery::newDocument($this->doc);

        return $this;
    }

    public function url($current)
    {
        $this->current = $current;
        $this->url = $this->gateway.$current;

        return $this;
    }

    public function pages()
    {
        $last = pq('.wp-pagenavi a:last')->attr('href');
        $last = str_replace([$this->url, 'page', '/'], '', $last);
        $last = (int) $last;
        $pages = [];
        for ($i = 1; $i <= $last; $i++) {
            $pages[] = $this->current.'page/'.$i.'/';
        }

        return $pages;
    }

    public function refresh()
    {
        Session::flush('links');
    }
}
