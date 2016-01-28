<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'DOMExtractor.php';
require_once 'JSONExtractor.php';
require_once 'AttributeFormatParser.php';

class WebExtractor
{

  /**
   * 
   * @return array
   */
  public function extractAll($descriptionsFilePath, $parameters)
  {
    $descriptionJson = json_decode(file_get_contents($descriptionsFilePath), true);
    $configurations = $descriptionJson['files'];
    
    $urlAttributeParser = new AttributeFormatParser("", $parameters);
    
    $allItems = array();

    foreach ($configurations as $configuration)
    {
      if ($this->areRequiredParametersFulfilled($configuration['requiredParameters'], $parameters))
      {
        $site = $configuration['name'];
        $url = $urlAttributeParser->parse($configuration['url']);
        
        $items = $this->extractProductsFromSite($site, $url, $configuration['file'], $configuration['type']);

        $allItems = array_merge($allItems, $items);
      }
    }

    return $allItems;
  }

  private function areRequiredParametersFulfilled($requiredParameters, $parameters)
  {
    for ($i = 0; $i < count($requiredParameters); $i++)
    {
      $requiredParameter = $requiredParameters[$i];
      if ($parameters[$requiredParameter] === null)
      {
        return false;
      }
    }

    return true;
  }

  private function extractProductsFromSite($site, $url, $descriptionFile, $type)
  {
    $elements = array();

    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');

    $html = curl_exec($ch);
    curl_close($ch);

    $descriptionFileContents = file_get_contents($descriptionFile);
    $descriptionFileJSON = json_decode($descriptionFileContents, true);

    if ($type == 'dom')
    {
      $domExtractor = new DOMExtractor();
      $elements = $domExtractor->extractProductsFromDOM($html, $descriptionFileJSON, $site);
    } else if ($type == 'json')
    {
      $jsonExtractor = new JSONExtractor();
      $elements = $jsonExtractor->extractProductsFromJSON($html, $descriptionFileJSON, $site);
    }

    return $elements;
  }

}

?>