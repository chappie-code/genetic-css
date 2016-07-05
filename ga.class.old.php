<?php

Class css_ga
{
	var $adam = array(
	'background-color' => [240, 173, 78],
	'color' => [0,0,0],
	'font-size' => '14',

	'padding' => [6,12,6,12],
	'margin' => [0,0,0,0],
	'height' => '50',
	'width' => '100',
	'border-radius' => [0,0,0,0,0,0,0,0]
	);

	var $eve = array(
	'background-color' => [240, 173, 178],
	'color' => [255,255,255],
	'font-size' => '15',

	'padding' => [12,6,12,6],
	'margin' => [0,0,0,0],
	'height' => '50',
	'width' => '100',
	'border-radius' => [25,25,25,25,25,25,25,25]
	);

	var $steve = array(
	'background-color' => [140, 73, 178],
	'color' => [0,0,0],
	'font-size' => '15',

	'padding' => [12,6,12,6],
	'margin' => [0,0,0,0],
	'height' => '50',
	'width' => '100',
	'border-radius' => [25,25,25,25,25,25,25,25]
	);

	//properties
	var $properties = ['background-color','color','font-size','padding','margin','height','width','border-radius'];
	var $properties_swapped = 3; // The number of css properties to swap when mating
	var $pool_size = 7;
	var $kill_rate = 4; // kill the lowest 4 genes
	var $number_of_passes_between_generation = 800;
	var $mutation_rate = 0.02;
	var $selected_gene;
	//arrays that hold the genes, and the results
	var $gene_pool = array();
	var $gene_results = array();
	var $time = array('generation'=>0,'passes'=>0);

	var $parents = array();

	function __construct()
	{
	  $this->init();
	}

	function init()
	{
		if(!file_exists('/home/astrologyanswers/public_html/ed/ga/aa_gene_pool.json'))
		{
			$this->gene_pool[] = $this->adam;
			$this->gene_pool[] = $this->eve;
			$this->gene_pool[] = $this->steve;
			$this->gene_results = [['clicks' => 0, 'views' => 0],['clicks' => 0, 'views' => 0],['clicks' => 0, 'views' => 0]];

			//$this->save_genepool();
		}
		else {
			$this->read_genepool();
		}
	}

	function read_genepool()
	{
		$this->gene_results = json_decode(file_get_contents('/home/astrologyanswers/public_html/ed/ga/aa_results.json'),TRUE);
		$this->gene_pool = json_decode(file_get_contents('/home/astrologyanswers/public_html/ed/ga/aa_gene_pool.json'),TRUE);
		$this->time = json_decode(file_get_contents('/home/astrologyanswers/public_html/ed/ga/aa_generation.json'),TRUE);

		if($this->gene_results === FALSE)
		{
			json_decode(file_get_contents('/home/astrologyanswers/public_html/ed/ga/aa_results.json'),TRUE);
		}
		if($this->gene_pool === FALSE)
		{
			json_decode(file_get_contents('/home/astrologyanswers/public_html/ed/ga/aa_gene_pool.json'),TRUE);
		}
		if($this->time === FALSE)
		{
			json_decode(file_get_contents('/home/astrologyanswers/public_html/ed/ga/aa_generation.json'),TRUE);
		}
	}

	function save_genepool($step = '')
	{
		if($step == 'kill')
		{
			$gen = $this->time['generation'];
			file_put_contents('/home/astrologyanswers/public_html/ed/ga/gens/gen_'.$gen.'aa_results.json',json_encode($this->gene_results));
			file_put_contents('/home/astrologyanswers/public_html/ed/ga/gens/gen_'.$gen.'aa_gene_pool.json',json_encode($this->gene_pool));
			file_put_contents('/home/astrologyanswers/public_html/ed/ga/gens/gen_'.$gen.'aa_generation.json',json_encode($this->time));
		}
		file_put_contents('/home/astrologyanswers/public_html/ed/ga/aa_results.json',json_encode($this->gene_results));
		file_put_contents('/home/astrologyanswers/public_html/ed/ga/aa_gene_pool.json',json_encode($this->gene_pool));
		file_put_contents('/home/astrologyanswers/public_html/ed/ga/aa_generation.json',json_encode($this->time));


	}

	function crossover_function ($gene1,$gene2)
	{
		for($i = 0; $i<= $properties_swapped; $i++)
		{
			$swap_property = $rand(0,2);
			$temp = $gene1[$swap_index];
			$gene1[$swap_index] = $gene2[$swap_index];
			$gene2[$swap_index] = $temp;
		}
	}

	function get_stats()
	{
		$data['generation'] = $this->time['generation'];
		$data['passes'] = $this->time['passes'];
		$data['current_genes'] = $this->gene_pool;

		return $data;
	}

	function mate()
	{


		$gene1 = $this->parents[array_rand($this->parents)];
		$gene2 = $this->parents[array_rand($this->parents)];
		$baby = $gene1;


		for($i = 0; $i< $properties_swapped; $i++)
		{
			$swap_property = $this->properties[array_rand($this->properties)];
			$baby[$swap_property] = $gene2[$swap_property];
		}


		$baby = $this->mutate($baby);


		$baby_results = ['clicks' => 0, 'views' => 0];
		array_push($this->gene_pool,$baby);
		array_push($this->gene_results,$baby_results);


	}

	function mutate($baby)
	{
		$new_baby = array();
		foreach($baby as $key => $value)
		{
			if(rand(1,100*$this->mutation_rate) == 1)
			{
				$new_baby[$key] = $this->do_mutation($key,$value);
			}
			else
			{
				$new_baby[$key] = $value;
			}
		}
		return $new_baby;
	}

	function do_mutation($property,$value)
	{
		$new_val= array();


		switch($property)
		{
			case 'background-color':
				return $this->mutate_background_color($value);

			case 'color':
				return $this->mutate_color($value);

			case 'font-size':
				$rand_sign = rand(0,1);
				$new_val = $rand_sign? $value + $this->mutation_rate : $value - $this->mutation_rate ;
				return $new_val;

			case 'border-radius':
				return $this->mutate_border_radius($value);

			case 'padding':

				return $this->mutate_padding($value);

			case 'margin':

				return $this->mutate_margin($value);

			case 'height':
				return $this->mutate_height($value);
			case 'width':
				return $this->mutate_width($value);
		}
		return $value;
	}

	function clicked($id)
	{
		if(isset($this->gene_results[$id]))
		{
			$this->gene_results[$id]['clicks'] += 1;
			$this->save_genepool();
		}


	}


	function step()
	{

		$kill = '';

		//if it's past the time to die kill, and mate
		if($this->time['passes'] >= $this->number_of_passes_between_generation)
		{
			if($this->kill())
			{
				$this->time['generation'] += 1;
				$kill = 'kill';
			}

		}

		$this->parents = $this->gene_pool;
		//make sure that 10 genes exists, if not, evolve more
		while(count($this->gene_pool) <= $this->pool_size)
		{
			$this->mate();
		}

		$this->time['passes'] = $this->time['passes'] +1;
		$this->save_genepool($kill);


	}

	function kill()
	{
		$no_clicks = TRUE;
		$score_array = array();
		foreach($this->gene_results as $key => $value)
		{
			if($value['clicks'] >0)
				$no_clicks = FALSE;

			if($value['views'] > 0)
				$score_array[$key] = $value['clicks'] / $value['views'];
			else {
				$score_array[$key] = 0;
			}
		}

		//don't kill if no clicks
		if($no_clicks)
			return FALSE;

		//sort valyes
		arsort($score_array);
		//pop killed values
		for($i = 0; $i < $this->kill_rate; $i++)
		{
			array_pop($score_array);
		}

		//sort keys

		$new_pool;
		$new_results;
		foreach($score_array as $key => $value)
		{
			$new_pool[$key] = $this->gene_pool[$key];
			$new_results[$key] = $this->gene_results[$key];
			//if score doesn't exist, remove index from other arrays


		}
		$this->gene_pool = $new_pool;
		$this->gene_results = $new_results;

		$new_result = array();
		foreach($this->gene_results as $key => $value)
		{
			$new_result[$key]['clicks'] = 0;
			$new_result[$key]['views'] = 0;
		}

		$this->gene_results = $new_result;


		$this->time['passes'] = 0;

		return TRUE;

	}


	function return_css($element_id)
	{

		$lowest_key = array_rand($this->gene_results);
		foreach($this->gene_results as $k => $v)
		{
			if($this->gene_results[$k]['views'] <= $this->gene_results[$lowest_key]['views'])
				$lowest_key = $k;
		}
		$this->selected_gene = $lowest_key; //lowest index from the gene pool
		$gene = $this->gene_pool[$this->selected_gene];
		$this->gene_results[$this->selected_gene]['views'] += 1;
		$this->save_genepool();

		$css_string = '';
		$css_string = "$element_id {";

		//Constants for this GA
		$css_string .= "cursor:pointer;";
		$css_string .= "display:inline-block;";
		foreach($gene as $key => $value)
		{
			if($key == 'background-color')
				$css_string .= $key . ":" . "rgb($value[0] , $value[1] , $value[2]) ;";
			if($key == 'color')
				$css_string .= $key . ":" . "rgb($value[0] , $value[1] , $value[2]) ;";
			if($key == 'font-size')
				$css_string .= $key . ":" . $value."px; ";
			if($key == 'padding')
				$css_string .= $key . ":" . "$value[0]px $value[1]px $value[2]px $value[3]px; ";
			if($key == 'margin')
				$css_string .= $key . ":" . "$value[0]px $value[1]px $value[2]px $value[3]px; ";
			if($key == 'width')
				$css_string .= $key . ":" . $value."px; ";
			if($key == 'heigth')
				$css_string .= $key . ":" . $value."px; ";
			if($key == 'border-radius')
				$css_string .= $key . ":" . "$value[0]% $value[1]% $value[2]% $value[3]% / $value[4]% $value[5]% $value[6]% $value[7]% ;";
		}


		$css_string .= "}";


		return $css_string;

	}

	function get_current_genepool_css($element_id_seed = 'my_button')
	{
		$return = '';


		$count = 0;
		foreach($this->gene_pool as $key => $value)
		{
			$return .= '<style type="text/css">';
			//css property => value
			if(is_array($value))
			{
				$return .= "#".$element_id_seed."$count {";

				foreach($value as $k => $v)
				{
					$return .= $this->get_css_for_property($k,$v);
				}

				$return .= '}</style>';
				$return .= '<div id="'.$element_id_seed.$count.'">Clicks: '.$this->gene_results[$key]['clicks'].' |  Views: '.$this->gene_results[$key]['views'].'</div>';
				$count++;

			}



		}

		return $return;
	}

	function get_all_generation_css()
	{

	}

	function return_click_script()
	{


		return '<script type="text/javascript">
					$("#my_button").click(function(){
						$.ajax({
							url: "http://astrologyanswers.com/cssClick.php",

							type: "POST",
							data: {gene_id : $(this).data("button-id") },
							}).done(function() {

						});
					});


				</script>';
	}

	function get_css_for_property($key,$value)
	{
		$generated_css = '';
		if($key == 'background-color')
			$generated_css = $key . ":" . "rgb($value[0] , $value[1] , $value[2]) ;";
		if($key == 'color')
			$generated_css = $key . ":" . "rgb($value[0] , $value[1] , $value[2]) ;";
		if($key == 'font-size')
			$generated_css = $key . ":" . $value."px; ";
		if($key == 'padding')
			$generated_css = $key . ":" . "$value[0]px $value[1]px $value[2]px $value[3]px; ";
		if($key == 'margin')
			$generated_css = $key . ":" . "$value[0]px $value[1]px $value[2]px $value[3]px; ";
		if($key == 'width')
			$generated_css = $key . ":" . $value."px; ";
		if($key == 'heigth')
			$generated_css = $key . ":" . $value."px; ";
		if($key == 'border-radius')
			$generated_css = $key . ":" . "$value[0]% $value[1]% $value[2]% $value[3]% / $value[4]% $value[5]% $value[6]% $value[7]% ;";

		return $generated_css;
	}

	function mutate_border_radius($properties)
	{
		$max_change_rate = 10;
		$property_max = 100; // never have more then 100%
		$property_min = 0;
		$new_value;
		foreach($properties as $k => $v)
		{
			$rand_sign = rand(0,1);

				$v = $v + ($rand_sign? rand(0,$max_change_rate) : (rand(0,$max_change_rate) * -1));
				if($v < $property_min) $v = $property_min;
				if($v > $property_max) $v = $property_max;

			$new_value[$k] = $v;
		}

		return $new_value;
	}

	function mutate_height($properties)
	{
		$max_change_rate = 10;
		$property_max = 60; // never have more then 255 color
		$property_min = 20;

		$rand_sign = rand(0,1);

			$properties = $properties + ($rand_sign? rand(0,$max_change_rate) : (rand(0,$max_change_rate) * -1));
			if($properties < $property_min) $properties = $property_min;
			if($properties > $property_max) $properties = $property_max;



		return $properties;
	}

	function mutate_width($properties)
	{
		$max_change_rate = 10;
		$property_max = 200; // never have more then 255 color
		$property_min = 100;

		$rand_sign = rand(0,1);

			$properties = $properties + ($rand_sign? rand(0,$max_change_rate) : (rand(0,$max_change_rate) * -1));
			if($properties < $property_min) $properties = $property_min;
			if($properties > $property_max) $properties = $property_max;



		return $properties;
	}

	function mutate_background_color($properties)
	{
		$max_change_rate = 20;
		$property_max = 255; // never have more then 255 color
		$property_min = 0;

		$new_value;
		foreach($properties as $k => $v)
		{
			$rand_sign = rand(0,1);

				$v = $v + ($rand_sign? rand(0,$max_change_rate) : (rand(0,$max_change_rate) * -1));
				if($v < $property_min) $v = $property_min;
				if($v > $property_max) $v = $property_max;

			$new_value[$k] = $v;
		}

		return $new_value;


	}

	function mutate_color($properties)
	{
		$max_change_rate = 20;
		$property_max = 255; // never have more then 255 color
		$property_min = 0;

		$new_value;
		foreach($properties as $k => $v)
		{
			$rand_sign = rand(0,1);

				$v = $v + ($rand_sign? rand(0,$max_change_rate) : (rand(0,$max_change_rate) * -1));
				if($v < $property_min) $v = $property_min;
				if($v > $property_max) $v = $property_max;

			$new_value[$k] = $v;
		}

		return $new_value;
	}

	function mutate_padding($properties)
	{

		$max_change_rate = 2;
		$property_max = 10;
		$property_min = 0;


		$new_value;
		foreach($properties as $k => $v)
		{
			$rand_sign = rand(0,1);

				$v = $v + ($rand_sign? rand(0,$max_change_rate) : (rand(0,$max_change_rate) * -1));
				if($v < $property_min) $v = $property_min;
				if($v > $property_max) $v = $property_max;

			$new_value[$k] = $v;
		}

		return $new_value;
	}

	function mutate_margin($properties)
	{

		$max_change_rate = 2;
		$property_max = 5; // never have more then 30px padding
		$property_min = -5;
		$new_value;
		foreach($properties as $k => $v)
		{
			$rand_sign = rand(0,1);

				$v = $v + ($rand_sign? rand(0,$max_change_rate) : (rand(0,$max_change_rate) * -1));
				if($v < $property_min) $v = $property_min;
				if($v > $property_max) $v = $property_max;

			$new_value[$k] = $v;
		}

		return $new_value;
	}


	function array_random_assoc($arr, $num = 1) {
    $keys = array_keys($arr);
    shuffle($keys);

    $r = array();
    for ($i = 0; $i < $num; $i++) {
        $r[$keys[$i]] = $arr[$keys[$i]];
    }
    return $r;
	}


	function generate_all_button_code()
	{
		for( $i =1; $i <= $this->time['generation']; $i++)
		{
			$lowest_key=0;
			foreach($this->gene_results as $k => $v)
			{
				if($this->gene_results[$k]['views'] <= $this->gene_results[$lowest_key]['views'])
					$lowest_key = $k;
			}
			$this->selected_gene = $lowest_key; //lowest index from the gene pool
			$gene = $this->gene_pool[$this->selected_gene];
			$this->gene_results[$this->selected_gene]['views'] += 1;
			$this->save_genepool();

			$css_string = '';
			$css_string = "$element_id {";

			//Constants for this GA
			$css_string .= "cursor:pointer;";
			$css_string .= "display:inline-block;";
			foreach($this->gene_pool as $key => $value)
			{
				if($key == 'background-color')
					$css_string .= $key . ":" . "rgb($value[0] , $value[1] , $value[2]) ;";
				if($key == 'color')
					$css_string .= $key . ":" . "rgb($value[0] , $value[1] , $value[2]) ;";
				if($key == 'font-size')
					$css_string .= $key . ":" . $value."px; ";
				if($key == 'padding')
					$css_string .= $key . ":" . "$value[0]px $value[1]px $value[2]px $value[3]px; ";
				if($key == 'margin')
					$css_string .= $key . ":" . "$value[0]px $value[1]px $value[2]px $value[3]px; ";
				if($key == 'width')
					$css_string .= $key . ":" . $value."px; ";
				if($key == 'heigth')
					$css_string .= $key . ":" . $value."px; ";
				if($key == 'border-radius')
					$css_string .= $key . ":" . "$value[0]% $value[1]% $value[2]% $value[3]% / $value[4]% $value[5]% $value[6]% $value[7]% ;";
			}


			$css_string .= "}";


			return $css_string;
		}
	}
};
?>
