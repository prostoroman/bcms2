<?php

class MenuController extends Controller
{
  
   public function init(Pimple $di) {
      
   }

   public function menu1() {

      $homepage = ORM::for_table('b_pages')->select('id')->select('name_url')->select('name_menu')->where('parent', 0)->where('name_url', '')->find_one();
      $menu1 = ORM::for_table('b_pages')->select('id')->select('name_url')->select('name_menu')->where('parent', $homepage->id)->where('is_visible', 1)->order_by_asc('number')->find_many();

      //$menu1 = ORM::for_table('b_pages')->raw_query("SELECT `id`, `name_url`, `name_menu` FROM `b_pages` WHERE `parent` = (SELECT `id` FROM `b_pages` WHERE `parent` = '0' AND `name_url` = '' LIMIT 1) AND `is_visible` = '1' ORDER BY `number` ASC")->find_many();
      
      return $menu1;
   }

   public function submenu($pageId) {

      $page = ORM::for_table('b_pages')->select('parent')->select('name_url')->where('id', $pageId)->find_one();
      
      $url = $this->build_url($page->name_url, $page->parent);
   
      $pages = ORM::for_table('b_pages')->select('name_url')->select('name_menu')->select('parent')->where('parent', $pageId)->where('is_visible', 1)->order_by_asc('number')->find_many();

      foreach($pages as $page) {
         $page->name_url = $url.'/'.$page->name_url;
      }
      
      return $pages;
   }

   // Build URL path for a page
   function build_url($page_url, $page_parent){

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