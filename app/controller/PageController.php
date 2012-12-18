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
   
   public function find($parts) {

      $parent = 0;
      $maxLevel = count($parts);
      
      $url = implode('/', $parts);
      
      $itemPage = Model::factory('Page')->where('url', $url)->find_one();
      
      /*
      for($i = 0; $i < $maxLevel; $i++) {
         
         if($i == 0) {
            $itemPage = ORM::for_table('b_pages')->select('id')->select('name_url')->select('name_menu')->where('parent', 0)->where('name_url', '')->find_one();
         }
         else {
            $itemPage = Model::factory('Page')->where('parent', $parent)->where('name_url', $parts[$i])->find_one();
         }
         
         $parent = $itemPage ? $itemPage->id : $parent;
         
         if(!is_object($itemPage)) break;
         
      }
      */
      
      return $itemPage;
   
   }
}