<?php

/**
* PageController
* 
* Class for working with pages
* 
* @author Roman Lokhov <roman@bs1.ru>
* @version 1.0
*/

class PageController extends Controller
{
   
   public function init(Pimple $di) {
      
   }

   /**
   * find pages by url-parts array
   * 
   * @param array $parts parts of url
   * @return object
   */   
   
   public function findByUrl($parts)
   {
      $parent = 0;
      $maxLevel = count($parts);
      $url = implode('/', $parts);
      
      $itemPage = ORM::for_table('b_pages')->where('url', $url)->find_one();
      
      return $itemPage;
   
   }

   public function findById($id)
   {
      $page = ORM::for_table('b_pages')->where('id', $id)->find_one();
            
      return $page;
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
                  ->select_many('id', 'name_url', 'name_menu', 'url', 'has_childs')
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

   public function menu1()
   {
      $homepage = ORM::for_table('b_pages')->select('id')->where('parent', 0)->where('name_url', '')->find_one();
     
      $pages = ORM::for_table('b_pages')
                  ->select_many('id', 'url', 'name_menu', 'has_childs')
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

   public function menuTree($parent = 0, $level = 0, $only_visible = 0)
   {
      $level++;

      $request = $this->app->request();
      $url = $request->getResourceUri();
      
      if($only_visible)
      {
         $pagesORM = ORM::for_table('b_pages')
                  ->select_many('id', 'name_menu', 'url', 'has_childs', 'order', 'date_changed')
                  ->where('parent', $parent)
                  ->where('is_visible', 1)
                  ->order_by_asc('order')
                  ->find_many();
      }
      else
      {
         $pagesORM = ORM::for_table('b_pages')
                  ->select_many('id', 'name_menu', 'url', 'has_childs', 'order', 'date_changed')
                  ->where('parent', $parent)
                  //->where('is_visible', 1)
                  ->order_by_asc('order')
                  ->find_many();
      }
      
      foreach($pagesORM as $page) {
         $pages[] = $page->as_array();
      }
      
      $countPages = count($pages);
      
      for ($i=0; $i < $countPages; $i++)  
      {
         $isActive = '';
         
         if($pages[$i]['url'] == '/' && $url == '/') $pages[$i]['active'] = TRUE;
         if($pages[$i]['url'] !== '/' && strpos($url, $pages[$i]['url']) !== false) $pages[$i]['active'] = TRUE;
         $pages[$i]['level'] = $level;
         
         if($pages[$i]['id'] > 0 && $pages[$i]['has_childs'])
         {
            $pages[$i]['childs'] = $this->menuTree($pages[$i]['id'], $level);
         }
         
      }
      
      return $pages;
   }
      
   public function generateUrls($parent = 0)
   {
      if($parent)
      {
         $pages = ORM::for_table('b_pages')->select('id')->select('name_url')->select('parent')->where('parent', $parent)->find_many();
      }
      else
      {
         $pages = ORM::for_table('b_pages')->select('id')->select('name_url')->select('parent')->find_many();   
      }
      
      foreach($pages as $page) {
         
         if($page->id > 0)
         {
            $page->url = '/'.$this->build_url($page->name_url, $page->parent);
            $page->save();
            //echo $page->name_url . ' &rarr; ' . $page->url . '<br />';
         }
      }
   }   

   public function fixOrder($parent = 0)
   {
      $pages = ORM::for_table('b_pages')
               ->select_many('id', 'name_menu', 'url', 'has_childs', 'order', 'date_changed')
               ->where('parent', $parent)
               ->order_by_asc('order')
               ->find_many();

      $countPages = count($pages);
      
      for ($i=0; $i < $countPages; $i++)  
      {
         
         $pages[$i]->order = $i + 1;
         $pages[$i]->save();
         
         echo $pages[$i]->name_menu.' - '.$pages[$i]->order.'<br>';
         
         if($pages[$i]->id > 0 && $pages[$i]->has_childs)
         {
            $this->fixOrder($pages[$i]->id);
         }
      }
   }

   public function move($id, $where = 'up')
   {
      $page = ORM::for_table('b_pages')
               ->select_many('id', 'parent', 'order')
               ->where('id', $id)
               ->find_one();
      
      if($where == 'up')
      {
         if($page->order < 2) return false;
         $nextOrder = $page->order - 1;
      }
      elseif($where == 'down')
      {
         $maxOrder = ORM::for_table('b_pages')->where('parent', $page->parent)->max('order');
         
         if($page->order == $maxOrder) return false;
         $nextOrder = $page->order + 1;
      }      
      
      $neighbor = ORM::for_table('b_pages')
               ->select_many('id', 'parent', 'order')
               ->where('parent', $page->parent)
               ->where('order', $nextOrder)
               ->find_one();

      if(!$neighbor)
      {
         return false;
      }

      $neighbor->order = $page->order;
      $neighbor->save();      
      
      $page->order = $nextOrder;
      $page->save();

      return true;
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

   public function delete($id)
   {
      $page = $this->findById($id);
      
      if($page)
      {
         $page->delete();
         return true;
      }
      else
      {
         return false;
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