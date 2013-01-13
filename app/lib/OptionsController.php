<?php

/**
* OptionsController
* 
* Class for working with options
* 
* @author Roman Lokhov <roman@bs1.ru>
* @version 1.0
*/

class OptionsController extends Controller
{
   protected $options = array();
   
   public function init(Pimple $di)
   {
      $options = ORM::for_table('b_options')->find_array();
      foreach($options as $option)
      {
         $this->options[$option['name']] = array('value' => $option['value'], 'description' => $option['description']);
      }
      //print_r($this->options);
   }

   public function __get($name)
   {
      $option = $this->options[$name];

      return $option['value'];
   }

   public function __isset($name)
   {   
      if(array_key_exists($name, $this->options))
      {
          return true;
      }
      
      return false;
   }   
   
   private function add($name, $value = '')
   {
      $option = ORM::for_table('b_options')->create();
      $option->name = $name;
      $option->value = $value;
      $option->save();
   }
 
   public function edit($name, $value)
   {
      if(!$name) return;
      $option = ORM::for_table('b_options')->where('name', $name)->find_one();
      $option->value = $value;
      $option->save();
   }

   public function all()
   {
      return $this->options;
   }

   public function delete($name)
   {
      $option = ORM::for_table('b_options')->where('name', $name)->find_one();

      return $option->delete();
   }
   
   public function install()
   {
      $db = ORM::get_db();
      
      //$db->exec('DROP TABLE IF EXISTS "b_options";');
      $db->exec('CREATE TABLE IF NOT EXISTS "b_options" ("id" INTEGER PRIMARY, "name" TEXT, "value" TEXT,"description" TEXT)');
   }
   
   public function uninstall()
   {
      $db = ORM::get_db();
      $db->exec('DROP TABLE IF EXISTS "b_options";');
   }
}