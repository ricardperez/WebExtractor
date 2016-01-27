<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'AttributeFormatParser.php';

/**
 * Description of JSONExtractor
 *
 * @author ricardperez
 */
class JSONExtractor
{
  public function extractProductsFromJSON($html, $descriptionFileJSON, $site)
  {
    $elements = array();

    $json = json_decode($html, true);

    $jsonItems = $this->getJSON($json, $descriptionFileJSON['elementsList']);
    foreach ($jsonItems as $jsonItem)
    {
      $element = $this->createProductFromJSON($jsonItem, $descriptionFileJSON["element"]);
      if ($element != null)
      {
        $element['site'] = $site;
        array_push($elements, $element);
      }
    }

    return $elements;
  }
  
  private function getJSON($rootJSON, $pathJSON)
  {
    $result = $rootJSON;
    foreach ($pathJSON as $nextPath)
    {
      $result = $result[$nextPath];
      if ($result == null)
      {
        break;
      }
    }
    
    return $result;
  }
  
  private function createProductFromJSON($jsonItem, $descriptionJSON)
  {
    $result = array();
    
    foreach ($descriptionJSON as $attributeJson)
    {
      $attributeName = $attributeJson['attribute'];
      $attributeValue = $this->getJSON($jsonItem, $attributeJson['schema']);
      
      if ($attributeJson['format'])
      {
        $format = $attributeJson['format'];
        $attributeValue = AttributeFormatParser::getInstance()->parse($format, $toReturn);
      }

      $result[$attributeName] = $attributeValue;
    }

    return $result;
  }

}
