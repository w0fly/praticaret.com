<?php
#region Fonksiyon Başlangıcı

//OpenCart - Panel arası SHA ve MD5 Trafik Şifrelemesi

$os = PHP_OS;

if (strpos($os, 'WIN') !== false) 
{

    echo "  WINDOWS - TEST";

} 

elseif (strpos($os, 'Linux') !== false) 

{

    echo "LINUX - TEST";

} 

else 
{

    echo "Desteklenmeyen bir işletim sistemi.\n";

}

#endregion
?>