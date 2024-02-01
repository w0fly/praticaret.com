<?php
include "../config.php";
/**
 *
 * @description Webhook istek hızı
 * @param string 
 * 	  'slow'   => 300 saniye,
 *	  'medium' => 180 saniye (default/taviye edilen),
 * 	  'fast'   => 60 saniye
 * 	  'vfast'  => 30 saniye
 * 	   
 */
$trendyol->webhook->setRequestMode('medium');

/**
 *
 * @description Trendyol sonuçlarında kaç siparişin getirileceği
 * @param string 
 * 	  'vmax'     => 200 adet,
 *	  'max'      => 150 adet,
 * 	  'medium'   => 100 adet (default/taviye edilen),
 * 	  'min'      => 50 adet
 * 	   
 */
$trendyol->webhook->setResultMode('medium');

/* Anonymous function ile siparişleri almak */
$trendyol->webhook->orderConsume(function($order){
	
	echo "Sipariş Bilgileri";
	echo "<pre>";
	print_r($order);
	echo "</pre>";
	
});

/* Class ile siparişleri almak */

Class TrendyolOrders
{
	
	public function consume($order)
	{

		echo "Sipariş Bilgileri";
		echo "<pre>";
		print_r($order);
		echo "</pre>";	

	}

}

$trendyol->webhook->orderConsume(array(new TrendyolOrders(), 'consume'));