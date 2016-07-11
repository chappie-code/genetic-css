<?php

require_once("ga.class.php");
$ga = new GeneticCSS(1);

$ga->register_click($_GET['id']);
echo 1;
