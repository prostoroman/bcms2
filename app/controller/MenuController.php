<?php

class MenuController extends Controller
{
   
   public function init(Pimple $di) {
      
   }

   function menu($level = 1) {
   
      if($level == 1) return $this->menu1();
      
      $request = $this->app->request();
      $url = $request->getResourceUri();
      
      if($url === '/' and $level > 1) return;
      
      $urlParts = explode('/', $url);
      $currentLevel = count($urlParts) - 1;
      
      if($level - $currentLevel > 1 && $url !== '/') return;
      
      $sliced = array_slice($urlParts, 0, $level);
      $baseUrl = implode('/',$sliced);
            
      $basePage = ORM::for_table('b_pages')->select('id')->where('url', $baseUrl)->find_one();

      if(!$basePage) return;
      
      $pages = ORM::for_table('b_pages')
                  ->select('id')
                  ->select('name_url')
                  ->select('name_menu')
                  ->select('url')
                  ->select('has_childs')
                  ->where('parent', $basePage->id)
                  ->where('is_visible', 1)
                  ->order_by_asc('order')
                  ->find_many();

      foreach($pages as $page)
      {
         if(strpos($url, $page->url) !== false) $page->is_active = 1; //$page->url == $url
      }
      
      return $pages;
   }

   public function menu1() {

      //$pages = ORM::for_table('b_pages')->raw_query("SELECT `id`, `name_url`, `name_menu` FROM `b_pages` WHERE `parent` = (SELECT `id` FROM `b_pages` WHERE `parent` = '0' AND `name_url` = '' LIMIT 1) AND `is_visible` = '1' ORDER BY `order` ASC")->find_many();

      $homepage = ORM::for_table('b_pages')->select('id')->where('parent', 0)->where('name_url', '')->find_one();
      
      $pages = ORM::for_table('b_pages')
                  ->select('id')
                  ->select('url')
                  ->select('name_menu')
                  ->select('has_childs')
                  ->where('parent', $homepage->id)
                  ->where('is_visible', 1)
                  ->order_by_asc('order')
                  ->find_many();

      $request = $this->app->request();
      $url = $request->getResourceUri();

      foreach($pages as $page)
      {
         if($page->url == '/' && $url == '/')
         {
            $page->is_active = 1;
         }
         else if($page->url !== '/' && strpos($url, $page->url) !== false)
         {
            $page->is_active = 1;
         }
      }
      
      return $pages;
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

      $request = $this->app->request();
      $url = $request->getResourceUri();
      
      $pages = ORM::for_table('b_pages')
               ->select('id')
               ->select('name_menu')
               ->select('url')
               ->select('has_childs')
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
         $isActive = '';
         
         if($page->url == '/' && $url == '/') $isActive = ' current';
         if($page->url !== '/' && strpos($url, $page->url) !== false) $isActive = ' current';
         
         $out .= '<li class="level'.$level.$isActive.'"><a href="' . $page->url . '">' . $page->name_menu . '</a>'.PHP_EOL;

         if($page->id > 0 && $page->has_childs)
         {
            $out = $this->menuTree($out, $page->id, $level);
         }
         
         $out .= '</li>'.PHP_EOL;
      }
      
      $out .= !empty($pages) ? '</ul>'.PHP_EOL : '';

      return $out;
   }
      
   public function generateUrls() {

      $pages = ORM::for_table('b_pages')->select('id')->select('name_url')->select('parent')->find_many();
      
      foreach($pages as $page) {
         
         if($page->id > 0)
         {
            $page->url = '/'.$this->build_url($page->name_url, $page->parent);
            $page->save();
            echo $page->name_url . ' &rarr; ' . $page->url . '<br />';
         }
      }

   }   

   public function hasChilds() {

      $pages = ORM::for_table('b_pages')->select('id')->select('name_url')->select('parent')->find_many();
      
      foreach($pages as $page)
      {

      $childs = ORM::for_table('b_pages')->select('id')->where('parent', $page->id)->find_many();
         
         if(!$childs)
         {
            $page->has_childs = false;
            $page->save();
         }
         else
         {
            $page->has_childs = true;
            $page->save();
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