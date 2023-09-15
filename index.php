<?php 
/*
@Author Cristiano MCon - cristiano.mcon@gmail.com		
Datagrid - Exemplos de utilizacao
*/
ini_set('display_errors',1);
require "Database.php";
require "Datagrid.php";
?>
<html>
<head>
    <title> Datagrid  </title>
</head>
<body>
<div class="container">   
<?php 
$cabecalho = ['Modelo','Ano','Marca','Potencia'];
$valores = [
    ['Palio','1997','Fiat','1.0'],
    ['Astra','1999','Chevrolet','2.0'],
    ['Vectra','1999','Chevrolet','4.5'],
];

$dg = new Datagrid();
$dg->setCDNJQuery();
$dg->setOrdenacao();
$dg->setArrayDados($cabecalho, $valores);
echo '<h2 class="page-header"> Datagrid usando array  </h2>';
echo $dg->getDatagrid();      

$dg = new Datagrid();
$dg->setOrdenacao();
//Individualizacao do datagrid
//Neutraliza bug que faz com que dataTable seja ativado em outros datagrids montados na mesma pagina
$dg->setTableID('demo2');
$dg->setCDNJQuery();
$dg->setDataTables();
$dg->setArrayDados($cabecalho, $valores);
echo '<h2 class="page-header"> Datagrid usando array ( com dataTable ativado)  </h2>';
echo  $dg->getDatagrid();     


$db = new Database();
$conecta = $db->conecta();
if(!$conecta){
    echo $db->erro;
}else{
    $db->setSQL('select Host,Db,User from db');
    $exe=$db->executa();
    $dg = new Datagrid();
    $dg->setCDNJQuery();
    $dg->setOrdenacao();
    $dg->setResourceId($exe);
    echo '<h2 class="page-header"> Datagrid usando resource ID 
    <br><small>O nome das colunas eh gerado usando o alias dos campos da query </small>  
    </h2>';
    echo $dg->getDatagrid();      
}


?>

</div>

<!-- CDN - JQUERY - BOOTSTRAP -->
<link href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

</body>
</html>


