<?php

require_once("medoo.php");
require_once("gene.class.php");

class GeneticCSS{

  var $max_pool_size;
  var $gene_pool;
  var $gene_pool_age;
  var $css_id;
  var $gene_id;

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

  function get_css_id()
  {
    return $this->css_id;
  }

  function strain_population()
  {

    // get a list of id's for the lowest scoring genes
    // delete those id's
    // allow the generation to repopilate


    $this->kill_weakest_population();

  }

  function kill_weakest_population()
  {

    $pool_stats = $this->load_genepool_stats();
    $purge_list = array();
    $average = 0;
    $views = 0.0;
    $conversions = 0.0;

    //get the average
    foreach($pool_stats as $gene)
    {
      //$average += ($gene['conversions']/$gene['views']);
      $views += $gene['views'];
      $conversions += $gene['conversions'];

    }

    if($views > 0)
      $average = $conversions / $views;
    else {
      $average = 0;
    }

    //collect below average

    foreach($pool_stats as $gene)
    {
      if($gene['views'] > 0)
      {

        if(($gene['conversions']/$gene['views']) <= $average)
        {
            $purge_list[] = $gene['id'];
        }

      }
    }

    //purge list
    $this->kill_list($purge_list);



  }

  function load_genepool()
  {
    $data = $this->db->select('genes','data',['parent_id' => 1]);
    $pool = array();
    foreach($data as $raw_gene)
    {
      $pool[] = json_decode($raw_gene);
    }

    //var_dump($pool);

  }

  function load_genepool_stats()
  {
    $data = $this->db->query('select id,conversions,views from genes where parent_id = 1')->fetchAll(PDO::FETCH_ASSOC);
    //$data = $data[0];
    //var_dump($data);
    //die();

    $pool_stats = array();
    foreach($data as $raw_gene)
    {
      $pool_stats[] = $raw_gene;
    }

    return $pool_stats;

  }

  function step()
  {

      //Clear weakest genes if there's been enough generations
      $generation = $this->get_generation();
      $max = $this->get_max_generations();

      if($generation >= $max)
      {
        $this->strain_population();

      }


      // repopulate the pool
      if($this->count_genes() < $this->gene_pool_size)
      {
        $this->populate_gene_pool();
      }

      $this->update_generation();


  }

  function stats()
  {
    echo "<pre>";
    echo "current_generation = ". $this->get_generation();
    echo "<br>";
    echo "max_generation = ". $this->get_max_generations();
    echo "</pre>";

  }

  function load_random_css($css_id)
  {
    // get the least viewed
    //$val = $this->db->get('genes','data',['parent_id'=>1]);
    $this->css_id = $css_id;
    $gene = $this->db->query("select id,data from genes where parent_id = 1 order by rand() limit 1")->fetchAll(PDO::FETCH_ASSOC);



    $gene = $gene[0];


    $this->increment_gene_view($gene['id']);
    $this->gene_id = $gene['id'];

    $css = $this->get_css_from_gene(json_decode($gene['data']));

    return $css;
  }

  function get_gene_id()
  {
    return $this->gene_id;
  }

  function register_click($gene_id)
  {
    $this->increment_gene_conversion($gene_id);
  }

  function get_css_from_gene($gene)
  {


    $string = "#" . $this->css_id . "{";
    foreach($this->property_list as $property)
    {
      if($property == 'font-size')
        $string .= $property . ":" . $gene->$property . ";";

      if($property == 'color')
      {
        $string .= $property . ":";
        $string .= $this->get_rgb_string_from_array($gene->$property);
      }
    }
    $string .= "}";

    return $string;

  }

  function get_css()
  {
    $count = 0;


    foreach($this->gene_pool as $gene)
    {
      $string = "#genetic_".$count."{";
      foreach($this->property_list as $property)
      {
        if($property == 'font-size')
          $string .= $property . ":" . $gene[$property] . ";";

        if($property == 'color')
        {
          $string .= $property . ":";
          $string .= $this->get_rgb_string_from_array($gene[$property]);
        }
      }
      $string .= "}";
      $count++;

    }


    return $string;

  }

  function get_random_gene()
  {
    $gene = $this->db->query("select data from genes where parent_id = 1 order by rand() limit 1")->fetchAll(PDO::FETCH_ASSOC);
    $gene = $gene[0];
    $gene = json_decode($gene['data']);

    return (array)$gene;
  }

  function get_rgb_string_from_array($data)
  {

    $string = "rgb(" . $data[0] . "," . $data[1] . "," . $data[2] . ")";

    return $string;
  }


  function update_generation()
  {
    if($this->get_generation() < $this->get_max_generations())
      $this->db->query("update dna_strands set current_generation = current_generation+1 where id=1");
    else {
      $this->db->query("update dna_strands set current_generation = 0 where id=1");
    }
  }

  function increment_gene_view($gene_id)
  {
    $this->db->query("update genes set views = views+1 where id={$gene_id}");
  }

  function increment_gene_conversion($gene_id)
  {
    $this->db->query("update genes set conversions = conversions+1 where id={$gene_id}");
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

      //$random_gene_key = array_rand($this->gene_pool);
      $gene1 = $this->get_random_gene(); //$this->gene_pool[$random_gene_key];
      $gene2 = $this->get_random_gene();


      $gene3 = $this->mate($gene1,$gene2);

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

  /*

  Returns database gene count

  */
  function count_genes()
  {
    $count = $this->db->query("select count(*) as count from genes where parent_id=1")->fetchAll(PDO::FETCH_ASSOC);
    $count = $count[0]["count"];
    return $count;
  }

  function kill_list($list)
  {
    foreach($list as $gene_id)
    {
      $this->db->delete('genes',["AND" =>['parent_id' => 1, 'id' =>$gene_id]]);
    }
  }



  function kill_all()
  {
    $this->db->delete('genes',["AND" =>['parent_id' => 1, 'id[>]' =>392]]);
    $this->db->update('dna_strands',['current_generation'=>'0'],['id' => 1]);
  }

  function open_generation()
  {

  }

  function fitness()
  {

  }


}


?>
