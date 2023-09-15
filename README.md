<h2>Datagrid</h2>
<p>Monta automagicamente tabulação para listagem de dados usando fonte de dados array ou resource id de query mysql</p>
<ul>
    <li>Ponto forte: Agiliza implementação de listagens pois classe cria table, colunas e as linhas automaticamente.</li>
    <li>Ponto fraco: Criacao dinamica usando query esta restrita somente ao banco mysql.</li>
</ul>

<pre>
require 'Datagrid.php';
    
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

<p>Novas atualizações em breve</p>
