<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Product
 *
 * @author ricardperez
 */
class Product
{

  /**
   * 
   * @return string
   */
  public function getLink()
  {
    return $this->link;
  }

  /**
   * 
   * @return string
   */
  public function getImageSrc()
  {
    return $this->imageSrc;
  }

  /**
   * 
   * @return float
   */
  public function getPrice()
  {
    return $this->price;
  }

  /**
   * 
   * @return string
   */
  public function getTitle()
  {
    return $this->title;
  }

  /**
   * 
   * @param string $link
   */
  public function setLink($link)
  {
    assert($link == null || is_string($link));
    $this->link = $link;
  }

  /**
   * 
   * @param srting $imageSrc
   */
  public function setImageSrc($imageSrc)
  {
    assert($imageSrc == null || is_string($imageSrc));
    $this->imageSrc = $imageSrc;
  }

  /**
   * 
   * @param float $price
   */
  public function setPrice($price)
  {
    assert($price == null || is_float($price));
    $this->price = $price;
  }

  /**
   * 
   * @param string $title
   */
  public function setTitle($title)
  {
    assert($title == null || is_string($title));
    $this->title = $title;
  }
  public function getSite()
  {
    return $this->site;
  }

  public function setSite($site)
  {
    $this->site = $site;
  }

    /**
   * 
   * @param string $key
   * @param string $value
   */
  public function setAttribute($key, $value)
  {
    switch ($key)
    {
      case 'name':
        $this->setTitle($value);
        break;
      case 'link':
        $this->setLink($value);
        break;
      case 'image':
        $this->setImageSrc($value);
        break;
      case 'price':
        $this->setPrice((float) $value);
        break;
      default:
        assert(false);
    }
  }

  /**
   * Checks if it has properties $link and $imageSrc
   * @return type
   */
  public function isNull()
  {
//    return (($this->link == null) || ($this->imageSrc == null));
    return false;
  }

  public function toString()
  {
    if ($this->isNull())
    {
      return "NULL";
    } else
    {
      return "Name: " . $this->name . " ; Link: " . $this->link . " ; Price: " . $this->price . " ; - Image: " . $this->imageSrc;
    }
  }

  /**
   *
   * @var string
   */
  private $link;

  /**
   *
   * @var string
   */
  private $imageSrc;

  /**
   *
   * @var float
   */
  private $price;

  /**
   *
   * @var string
   */
  private $title;
  
  /**
   *
   * @var string
   */
  private $site;

}
