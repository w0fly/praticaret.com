<?php
#region Fonksiyon Başlangıcı

//OpenCart - Panel arası SHA ve MD5 Trafik Şifrelemesi

$os = PHP_OS;

if (strpos($os, 'WIN') !== false) 
{
    //Eğer Windows ise, Ethernet kartı dinlenerek domain mix vasıtasıyla SHA MD5 şifrelemesi yaptıralım

    echo "  WINDOWS - TEST";

} 

elseif (strpos($os, 'Linux') !== false) 

{
    //Eğer Linux ise, JSON veyahut Trafik dinlenerek SHA MD5 şifrelemesi yaptıralım

    echo "LINUX - TEST";

} 

else 
{

    echo "Desteklenmeyen bir işletim sistemi.\n";

}

#endregion
?>