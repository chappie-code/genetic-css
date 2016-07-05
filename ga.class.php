<?php

require_once("medoo.php");

class GeneticCSS{

  var $max_pool_size;
  var $gene_pool;
  var $gene_pool_age;

  var $property_list;

  var $adam= ["font-size" => "14", "color"=>[0,0,0]];
  var $eve = ["font-size" => "20", "color"=>[255,0,0]];

  function __construct($id)
  {
    //load generation data from id
    $this->gene_pool_age = 0;
    $this->gene_pool_size = 9;
    $this->gene_pool[] = $this->adam;
    $this->gene_pool[] = $this->eve;

    $this->property_list = ["font-size", "color"];


    $this->dump();
  }




  function step()
  {

      if(count($this->gene_pool) < $this->gene_pool_size)
      {
        $this->populate_gene_pool();
      }

      if()


  }

  function dump($gene)
  {
    if(!empty($gene))
    {
      echo "<pre>";
      var_dump($this->gene_pool);
      echo "</pre>";
    }

    echo "<pre>";
    var_dump($this->gene_pool);
    echo "</pre>";
  }

  function populate_gene_pool()
  {
    $count = count($this->gene_pool) - 1;

    for($i = $count; $i < $this->gene_pool_size; $i++)
    {

      $random_gene_key = array_rand($this->gene_pool);
      $gene1 = $this->gene_pool[$random_gene_key];
      $gene2 = $this->gene_pool[$i];


      $gene3 = $this->mate($gene1,$gene2);
      $this->gene_pool[] = $gene3;
    }
  }

  function mate($gene1,$gene2)
  {
    $baby = array();
    foreach($this->property_list as $property)
    {
      $baby[$property] = rand(0,1)? $gene1[$property] : $gene2[$property];
    }

    return $baby;
  }

  function open_generation()
  {

  }

  function fitness()
  {

  }


}


?>
