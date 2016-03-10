<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'Driver.php';
require_once 'AttributeFormatParser.php';

/**
 * Description of JSONDriver
 *
 * @author ricardperez
 */
class JSONDriver extends Driver
{

  public function extractProducts($url, $parameters, $descriptionFileJSON)
  {
    $urlAttributeParser = new AttributeFormatParser("", $parameters);
    $formattedURL= $urlAttributeParser->parse($url);
    $html = $this->getHTMLContentsFromURL($formattedURL);
    
    return $this->extractProductsFromHTML($html, $descriptionFileJSON);
  }
  
  protected function extractProductsFromHTML($html, $descriptionFileJSON)
  {
    $elements = array();

    $json = json_decode($html, true);

    $jsonItems = $this->getJSON($json, $descriptionFileJSON['elementsList']);
    if (is_array($jsonItems))
    {
      foreach ($jsonItems as $jsonItem)
      {
        $element = $this->createProductFromJSON($jsonItem, $descriptionFileJSON["element"]);
        if ($element != null)
        {
          array_push($elements, $element);
        }
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
        $attributeParser = new AttributeFormatParser($attributeValue, null);
        $attributeValue = $attributeParser->parse($format);
      }

      $result[$attributeName] = $attributeValue;
    }

    return $result;
  }

}
