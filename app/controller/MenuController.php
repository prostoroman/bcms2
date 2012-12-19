<?php

class MenuController extends Controller
{
   public $mTree = array();
   
   public function init(Pimple $di) {
      
   }

   public function menu1() {

      $homepage = ORM::for_table('b_pages')->select('id')->select('name_url')->select('name_menu')->where('parent', 0)->where('name_url', '')->find_one();
      $menu1 = ORM::for_table('b_pages')->select('id')->select('name_url')->select('name_menu')->where('parent', $homepage->id)->where('is_visible', 1)->order_by_asc('order')->find_many();

      //$menu1 = ORM::for_table('b_pages')->raw_query("SELECT `id`, `name_url`, `name_menu` FROM `b_pages` WHERE `parent` = (SELECT `id` FROM `b_pages` WHERE `parent` = '0' AND `name_url` = '' LIMIT 1) AND `is_visible` = '1' ORDER BY `order` ASC")->find_many();
      
      return $menu1;
   }

   public function submenu($pageId) {
      
      $pages = ORM::for_table('b_pages')->select('name_url')->select('name_menu')->select('parent')->select('url')->where('parent', $pageId)->where('is_visible', 1)->order_by_asc('order')->find_many();

      foreach($pages as $page)
      {
         $page->name_url = $page->url;
      }
      
      return $pages;
   }

   public function menuTree($out = '', $parent = 0, $level = 0)
   {
      $level++;

      $pages = ORM::for_table('b_pages')
               ->select('id')
               ->select('name_menu')
               ->select('url')
               ->where('parent', $parent)
               ->where('is_visible', 1)
               ->order_by_asc('order')
               ->find_many();
      
      if(!$out)
      {
         $out = '<ul class="menu">'.PHP_EOL;
      }
      else if(!empty($pages))
      {
         $out .= '<ul class="'. $level . '">'.PHP_EOL;
      }
      
      foreach ($pages as $page)  
      {  
         $out .= '<li class="level'.$level.'"><a href="' . $page->url . '">' . $page->name_menu . '</a>'.PHP_EOL;
         $out = $page->id > 0 ? $this->menuTree($out, $page->id, $level) : $out;
         $out .= '</li>'.PHP_EOL;
      }
      
      $out .= !empty($pages) ? '</ul>'.PHP_EOL : '';

      return $out;
   }
      
   public function generateUrls() {

      $pages = ORM::for_table('b_pages')->select('id')->select('name_url')->select('parent')->find_many();
      
      foreach($pages as $page) {
         
         if($page->id > 0) {
            $page->url = '/'.$this->build_url($page->name_url, $page->parent);
            $page->save();
            echo $page->name_url . ' &rarr; ' . $page->url . '<br />';
         }
      }

   }   
   
   // Build URL path for a page
   private function build_url($page_url, $page_parent){

      $pages_url[0] = $page_url;
      $n=1;
      $cur_parent = $page_parent;
      
      while($page = ORM::for_table('b_pages')
                  ->select('id')
                  ->select('name_url')
                  ->select('name_menu')
                  ->select('parent')
                  ->where('id', $cur_parent)
                  ->find_one()) {

         if ($page->id == 0) break;
         
         $pages_url[$n] = $page->name_url;
         $n++;
         $cur_parent = $page->parent;
         
      }

      $url = "";
      for ($n=$n-1; $n>=0; $n--){
         $url .= $pages_url[$n]."/";
      }
      
      $url = preg_replace("/\/$/","",$url);

      return $url;
   }
}