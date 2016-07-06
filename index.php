<?php
// Same as error_reporting(E_ALL);
require_once("ga.class.php");

$css = <<<CSS
#selector { display:block; width:100px; }
#selector a { float:left; text-decoration:none }
CSS;

$ga = new GeneticCSS(1);

$ga->step();
//$ga->dump();

?>
<html>
<head>

</head>
<body>
  <div class="selector">Click Me!</div>
</body>
</html>
