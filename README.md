<h2>Datagrid</h2>
<p>Monta automagicamente tabulação usando dados de array ou resource id</p>

<pre>
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
echo 'Datagrid usando array';
echo $dg->getDatagrid();      
</pre>
