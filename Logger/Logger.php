<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Logger
 *
 * @author ricardperez
 */
class Logger
{
  public static function debug()
  {
    $args = func_get_args(); 
    $str_message = "";
    foreach ($args as $attribute)
    {
      if (is_array($attribute))
      {
        $str_message .= print_r($attribute, true);
      }
      else
      {
        $str_message .= $attribute;
      }
      
      $str_message .= " ";
    }
    
    file_put_contents("log.txt", $str_message . "\n", FILE_APPEND);
  }
}
