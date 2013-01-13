<?php

/**
* OptionsController
* 
* Class for working with options
* 
* @author Roman Lokhov <roman@bs1.ru>
* @version 1.0
*/

class UsersController extends Controller
{
   
   public function init(Pimple $di)
   {
   }
   
   public function add($data)
   {
      $user = ORM::for_table('b_users')->create();
      $user->username = $data['username'];
      $user->password = $data['password'];
      $user->email = $data['email'];
      $user->group = $data['group'];
      $user->status = $data['status'];
      //$user->activation = $data['activation'];
      return $user->save();
   }
 
   public function edit($id, $data)
   {
      if(!$id) return false;
      $user = ORM::for_table('b_users')->find_one($id);
      $user->set($data);
      
      return $user->save();
   }

   public function all()
   {
      return ORM::for_table('b_users')->find_array();
   }

   public function delete($id)
   {
      $user = ORM::for_table('b_options')->find_one($id);

      return $user->delete();
   }
   
   public function install()
   {
      $db = ORM::get_db();
      
      $db->exec('CREATE TABLE IF NOT EXISTS "b_users" ("id" INTEGER PRIMARY KEY  NOT NULL, "username" TEXT NOT NULL  UNIQUE , "password" TEXT NOT NULL , "email" TEXT NOT NULL , "group" INTEGER NOT NULL , "status" TEXT, "activation" TEXT');
   }
   
   public function uninstall()
   {
      $db = ORM::get_db();
      $db->exec('DROP TABLE IF EXISTS "b_users";');
   }
}