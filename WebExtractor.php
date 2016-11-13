<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'DOMDriver.php';
require_once 'JSONDriver.php';
require_once 'AttributeFormatParser.php';
require_once 'Logger/Logger.php';

class WebExtractor
{

  private $drivers = null;

  /**
   * Must call this for every site
   * @param type $key
   * @param type $driver
   */
  public function addDriver($key, $driver)
  {
    assert($driver instanceof Driver);
    $this->drivers[$key] = $driver;
  }

  /**
   * 
   * @return array
   */
  public function extractAll($descriptionsFilePath, $parameters)
  {
    $descriptionJson = json_decode(file_get_contents($descriptionsFilePath), true);
    $configurations = $descriptionJson['files'];

    $allItems = array();

    foreach ($configurations as $configuration)
    {
      if ($this->areRequiredParametersFulfilled($configuration['requiredParameters'], $parameters))
      {
        $site = $configuration['name'];
        
        Logger::debug("Going to get products from ", $site);

        $items = $this->extractProductsFromSite($site, $configuration['url'], $configuration['file'], $parameters);

        $allItems = array_merge($allItems, $items);
      }
      else
      {
        Logger::debug("Don't have the required parameters for ", $configuration);
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

  private function extractProductsFromSite($site, $url, $descriptionFile, $parameters)
  {
    $descriptionFileContents = file_get_contents($descriptionFile);
    $descriptionFileJSON = json_decode($descriptionFileContents, true);

    $driver = $this->drivers[$site];
    assert($driver != null); //must call addDriver for this site

    $elements = $driver->extractProducts($url, $parameters, $descriptionFileJSON);
    foreach ($elements as &$element)
    {
      $element['site'] = $site;
    }

    return $elements;
  }

}

?>