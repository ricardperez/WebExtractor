<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'Driver.php';
require_once 'AttributeFormatParser.php';
require_once 'Logger/Logger.php';

/**
 * Description of DOMDriver
 *
 * @author ricardperez
 */
class DOMDriver extends Driver
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

    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    
    $elementsList = $this->extractAllDOMElementsMatching($dom, $descriptionFileJSON["elementsList"]);
    if (empty($elementsList))
    {
      Logger::debug("Elements list is empty");
    }

    foreach ($elementsList as $listElement)
    {
      $element = $this->createProductFromDOMElement($listElement, $descriptionFileJSON["element"]);
      if ($element != null)
      {
        array_push($elements, $element);
      }
    }
    
    if (empty($elements))
    {
      Logger::debug("No elements found");
    }

    return $elements;
  }

  /**
   * 
   * @param DOMNode $element
   * @param type $filtersJson - a json that has an array of filters ('attribute' and 'value' keys)
   * @return boolean
   */
  private function domElementPassesFilter(DOMNode $element, $filtersJson)
  {
    if ($filtersJson == null || empty($filtersJson))
    {
      return true;
    }

    foreach ($filtersJson as $filter)
    {
      $attribute = $element->getAttribute($filter["attribute"]);
      if ($attribute != $filter["value"])
      {
        return false;
      }
    }

    return true;
  }

  /**
   * 
   * @param DOMNode $element
   * @param type $json - a json that has an array of filters ('attribute' and 'value' keys)
   * @return DomNode - the first element of $element children (recursively) that passes the filter.
   */
  private function findFirstDOMElementMatching(DOMNode $element, $json)
  {
    $allElements = $element->getElementsByTagName($json["type"]);
    foreach ($allElements as $nextElement)
    {
      if ($this->domElementPassesFilter($nextElement, $json["filters"]))
      {
        return $nextElement;
      }
    }
    return null;
  }

  /**
   * 
   * @param DOMNode $element
   * @param type $json - a json that has an array of filters ('attribute' and 'value' keys)
   * @return array of DomNode - all elements of $element children (recursively) that pass the filter.
   */
  private function findAllDOMElementsMathching(DOMNode $element, $json)
  {
    $result = array();

    $allElements = $element->getElementsByTagName($json["type"]);
    foreach ($allElements as $nextElement)
    {
      if ($this->domElementPassesFilter($nextElement, $json["filters"]))
      {
        array_push($result, $nextElement);
      }
    }

    return $result;
  }

  private function extractAllDOMElementsMatching(DOMNode $element, $json)
  {
    $nSchemas = count($json);
    $i = 0;
    foreach ($json as $jsonSchema)
    {
      if ($i == $nSchemas - 1)
      {
        break;
      }

      $element = $this->findFirstDOMElementMatching($element, $jsonSchema);
      if ($element == null)
      {
        Logger::debug("Element is null for schema: ", $jsonSchema, " - and element: ", $element);
        return array();
      }

      $i++;
    }


    $elementsList = array();
    if ($nSchemas > 0)
    {
      $lastJson = $json[$nSchemas - 1];
      $elementsList = $this->findAllDOMElementsMathching($element, $lastJson);
      
      if (empty($elementsList))
      {
        Logger::debug("No elements found in last element with last json: ", $lastJson);
      }
    }

    return $elementsList;
  }

  private function getDOMElementAttribute(DOMNode $element, $json)
  {
    $toReturn = null;
    $resultList = $this->extractAllDOMElementsMatching($element, $json['schema']);
    if (!empty($resultList))
    {
      $result = $resultList[0];

      $valueType = $json['value']['type'];
      if ($valueType == 'value')
      {
        $toReturn = $result->nodeValue;
      } else if ($valueType == 'attribute')
      {
        $attribute = $json['value']['attribute'];
        $toReturn = $result->getAttribute($attribute);
      }
    }

    if ($toReturn != null)
    {
      if ($json['value']['format'])
      {
        $format = $json['value']['format'];
        $attributeParser = new AttributeFormatParser($toReturn, null);
        $toReturn = $attributeParser->parse($format);
      }
    }

    return $toReturn;
  }

  private function createProductFromDOMElement(DOMNode $element, $json)
  {
    $result = array();

    foreach ($json as $attributeJson)
    {
      $attributeName = $attributeJson['attribute'];
      $attributeValue = $this->getDOMElementAttribute($element, $attributeJson);

      $result[$attributeName] = $attributeValue;
    }

    return $result;
  }

}
