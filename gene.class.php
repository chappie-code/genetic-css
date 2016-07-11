<?php

class Gene
{
  var $property_list;
  var $available_properties;


  function __construct()
  {
    $this->available_properties = ["font-size", "color"];
  }

  function set_properties($gene_properties)
  {
    foreach($gene_properties as $property)
    {
      $this->property_list[] = $property;
    }

  }


}
