<?php
// Same as error_reporting(E_ALL);
require_once("ga.class.php");
$ga = new GeneticCSS(1);
//$ga->kill_all();
$ga->step();
?>
<html>
<head>
<style>
  <?php echo $ga->load_random_css("ga_css"); ?>
</style>
</head>
<body>
  <?php
    $ga->stats();
    
  ?>
  <div id="<?php echo $ga->get_css_id()?>" onclick="httpGetAsync();">Click Me!</div>

<script>
  get_url = './button_click.php?id=<?php echo $ga->get_gene_id() ?>';

  function confirm_click(alert)
  {
    console.log(alert);
  }

  function httpGetAsync()
  {
      var xmlHttp = new XMLHttpRequest();
      xmlHttp.onreadystatechange = function() {
          if (xmlHttp.readyState == 4 && xmlHttp.status == 200)
              confirm_click(xmlHttp.responseText);
      }
      xmlHttp.open("GET", get_url, true); // true for asynchronous
      xmlHttp.send(null);
  }

</script>
</body>
</html>
