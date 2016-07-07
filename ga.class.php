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
    $this->db = new Medoo();


  //  $this->dump();
  }

  function strain_population()
  {

  }



  function step()
  {
      //Clear weakest genes if there's been enough generations
      $generation = $this->get_generation();
      $max = $this->get_max_generations()
      if($generation >= $max)
      {
        $this->strain_population();
      }


      // repopulate the pool
      if(count($this->gene_pool) < $this->gene_pool_size)
      {
        $this->populate_gene_pool();
      }


  }

  function update_generation()
  {
    $this->db->query("update dna_strands set current_generation = current_generation+1 where id=1");
  }

  function get_generation()
  {
    $data = $this->db->get('dna_strands',['current_generation'],['id' => 1]);
    return $data['current_generation'];

  }

  function get_max_generations()
  {
    $data = $this->db->get('dna_strands',['max_generations'],['id' => 1]);
    return $data['max_generations'];

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
    //$count = count($this->gene_pool) - 1;
    $count = $this->count_genes() - 1;

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

    $this->store_gene($baby);
    return $baby;
  }

  function store_gene($gene)
  {
    $gene = ["data"=>json_encode($gene), "parent_id"=>1];
    $this->db->insert('genes',$gene);

  }

  function count_genes()
  {
    $count = $this->db->query("select count(*) as count from genes where parent_id=1")->fetchAll(PDO::FETCH_ASSOC);
    $count = $count[0]["count"];
    return $count;
  }

  function kill_all()
  {
    $this->db->delete('genes',["AND" =>['parent_id' => 1]]);
  }

  function open_generation()
  {

  }

  function fitness()
  {

  }


}


?>
