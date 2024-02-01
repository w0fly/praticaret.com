<?php
include "vendor/autoload.php";
include "core/db.php";

use IS\PazarYeri\Trendyol\TrendyolClient;

$trendyol = new TrendyolClient(); 
$trendyol->setSupplierId(786818);
$trendyol->setUsername("Bh9yMxrhPu1NQwSO4Ftk");
$trendyol->setPassword("aBK6g9gfkPSIjuC4JwBW");
