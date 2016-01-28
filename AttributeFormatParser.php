<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AttributeFormatParser
 *
 * @author ricardperez
 */
class AttributeFormatParser
{

  private $value;
  private $parameters;
  public function AttributeFormatParser($value, $parameters)
  {
    $this->value = $value;
    $this->parameters = $parameters;
  }

  public function parse($string)
  {
    $result = $string;

    $openStr = "{{";
    $closeStr = "}}";

    $offset = 0;

    $startIndex = strpos($result, $openStr, $offset);
    while ($startIndex !== false)
    {
      $endIndex = strpos($result, $closeStr, $offset);

      $substringStart = $startIndex + strlen($openStr);
      $substring = substr($result, $substringStart, ($endIndex - $substringStart));
      $replacement = $this->parseExpression($substring);

      $result = substr_replace($result, $replacement, $startIndex, ($endIndex - $startIndex + strlen($closeStr)));

      $offset += strlen($replacement);
      $startIndex = strpos($result, $openStr, $offset);
    }

    return $result;
  }

  private function parseExpression($expression)
  {
    $filters = explode("|", $expression);
    $result = "";
    
    foreach ($filters as $filter)
    {
      $result = $this->applyFilter($filter, $result);
    }

    return $result;
  }

  private function applyFilter($filterString, $input)
  {
    $parameters = [$input];

    $functionName = $filterString;
    $openParenthesisPos = strpos($filterString, "(");
    if ($openParenthesisPos !== false)
    {
      $functionName = substr($filterString, 0, $openParenthesisPos);

      $closeParenthesisPos = strpos($filterString, ")");
      $parametersStr = substr($filterString, $openParenthesisPos + 1, ($closeParenthesisPos - $openParenthesisPos - 1));
      $moreParameters = explode(",", $parametersStr);
      $parameters = array_merge($parameters, $moreParameters);
    }


    $parseFilterMethods = array(
        "%" => array($this, "filterValue"),
        "lastCharacters" => array($this, "filterLastCharacters"),
        "range" => array($this, "filterRangeCharacters"),
        "split" => array($this, "filterSplit"),
        "at" => array($this, "filterAt"),
        "replace" => array($this, "filterReplace"),
        "urlEncode" => array($this, "filterURLEncode"),
        "number" => array($this, "filterNumber"),
        "lowercase" => array($this, "filterLowercase"),
    );

    $methodToCall = $parseFilterMethods[$functionName];
    if ($methodToCall !== null)
    {
      return $methodToCall($parameters);
    } else
    {
      return $this->filterAttribute($functionName);
    }
  }

  private function filterValue($parameters)
  {
    return $this->value;
  }
  
  private function filterAttribute($attributeName)
  {
    return $this->parameters[$attributeName];
  }

  private function filterLastCharacters($parameters)
  {
    $string = $parameters[0];
    $count = intval($parameters[1]);
    $start = (strlen($string) - $count);

    return $this->filterRangeCharacters([$string, $start, $count]);
  }

  private function filterRangeCharacters($parameters)
  {
    $string = $parameters[0];
    $start = intval($parameters[1]);
    $count = intval($parameters[2]);
    return substr($string, $start, $count);
  }

  private function filterSplit($parameters)
  {
    $string = $parameters[0];
    $separator = $parameters[1];

    return explode($separator, $string);
  }

  private function filterAt($parameters)
  {
    $strings = $parameters[0];
    $index = intval($parameters[1]);

    return $strings[$index];
  }
  
  private function filterReplace($parameters)
  {
    $string = $parameters[0];
    $search = $parameters[1];
    $replace = $parameters[2];
    return str_replace($search, $replace, $string);
  }
  
  private function filterURLEncode($parameters)
  {
    $string = $parameters[0];
    return urlencode($string);
  }
  
  private function filterNumber($parameters)
  {
    $string = $parameters[0];
    return (float)$string;
  }
  
  private function filterLowercase($parameters)
  {
    $string = $parameters[0];
    return strtolower($string);
  }

}
