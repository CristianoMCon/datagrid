<?php 
/*
@Author Cristiano MCon - cristiano.mcon@gmail.com		
@Create 13/04/2017 09h42 
@version 1.0
*/

class Datagrid {
	
	protected $caption_descricao='';		
	private $colunas=array(),$link_com_parametro=array(),$totalizadores=array(),$array_colunas_valores=array(),$array_colunas=array(),$array_metodos_utilizados=array();
	private $contador=0,$ordenar=true,$datatables=false,$table_id='dg_table_id',$toggle_hide_collumns,$datatableStyle;
	private $checkbox=false,$checkboxColuna=false,$checkboxCondicao=[],$checkboxMarca,$checkboxEstado,$checkboxMarcado,$checkboxBotao;
	private $checkbox_bto_descricao='Confirmar';
	private $checkbox_bto_css='btn btn-primary',$checkbox_condicao_yes_match,$checkbox_condicao_no_match;
	//private $checkboxSubmitChecados=false;
	private $css_class=array('table'=>'table table-striped table-condensed','tr'=>'');
	public $cdn_jquery=false,$debug=false;
	public $table_resultados,$csv_resultados,$total_registros;
	public $link_com_parametro_coluna_extrai = [];
	public $link_com_parametro_coluna_recebe = [];
	//Data 05/12/2017 por Cristiano MCon
	//Novo, para agregar trocentos parametros ao link
	public $parametros_link = [];	
    public $debugg=false,$cssClass;
	public $cssTituloPersonalisado=false,$arrayColunasTotalizantes,$mostraOrdenacao=true,$filtro_jquery=false,$filtro_jquery_add_lib=true;	
        
/*
* Armazena metodos utilizados
*/	
        public function setMetodosUtilizados($metodo){$this->array_metodos_utilizados[$metodo] = 0;}
/*
* Retorna array dos metodos utilizados
*/	
	public function getMetodosUtilizados(){return $this->array_metodos_utilizados;}	

/*
* Define classes css que serao usadas na formatacao da tabulacao
*/
	public function setClassCss($tag='table',$nome)	{
            $this->setMetodosUtilizados(__FUNCTION__);
            $this->css_class[$tag] = $nome;
	}			
	
/** 
* Função que define colunas usadas como parametros para os links montados dinamicamente
* Funcionalidades: title, url (link), javascript (funcao js)
* NOVAS funcionalidades: alias, COLUNA EXTRAI, COLUNA RECEBE
* @param Array ( Coluna => array( TITLE,URL,JAVASCRIPT )
* @return void 
*/ 
  public function setLinks(array $arr){
        $this->setMetodosUtilizados(__FUNCTION__);
	if($this->debugg){		
		var_dump(__FUNCTION__,$arr);   
	}
        if(count($arr)){
            foreach($arr as $coluna=>$config){
                //var_dump(__FUNCTION__,$coluna,$config);
                $this->link_com_parametro[$coluna]['title']		=$config[0];
                $this->link_com_parametro[$coluna]['url']		=$config[1];                
                //Se injecao de chamada javascript, inclui em array
                if(isset($config[2])){
                    //echo $coluna.' '.$config[2].'<br>';
                   $this->link_com_parametro[$coluna]['javascript']=$config[2];			
                }
                //Data 27/09/2017 18h01 por Cristiano MCon
                //Alias do link - descricao q sera mostrada
                //Criado para melhorar o visual, evitando mostrar o id que sera usado na consulta
                //Imagina linhas com 10 colunas e 5 usando links com ids, vai ficar ruim o visual             
                if(isset($config[3])){
                    $this->link_com_parametro[$coluna]['alias']=$config[3];
                }
                //Data 28/09/2017 17h58 por Cristiano MCon - Dificil implementar isso mas deu certo por enquanto
                //UPDATE NOVA FUNCIONALIDADE - Acrescenta mais um parametro ao link, ficando dois parametros no link
				//Estou criando nova funcao para substituir essa parte, essa funcao nao possibilita multiplos parametros somente um
                if(isset($config[4])){                    
					//Verifica se palavra reservada esta presente na url
					//Depois dessa palavra deve ter a coluna que sera puxada o valor
					//Ex: _CMCONCPF - Coluna CPF precisa existir na query
					$pattern = '/_CMON/';
					$url=$this->link_com_parametro[$coluna]['url'];
					if(!preg_match($pattern,$url)){
						//echo 'Existe para parametro '.$coluna.'-'.$url.'<br>';
					//}else{
						echo 'ERRO! Palavra chave nao encontrada!<br>Coluna:'.$coluna.'<br>Link:'.$url.'<br>';
						echo 'Atencao! Para usar mais um parametro deve ser adicionado ao link a palavra chave _CMON + coluna alvo';						
						echo 'Ex: lancamentos/negociacao/f/somente-produto/cpf/_CMONCPF_CMON/p/';
						exit;
					}
				if($this->debugg){
					var_dump($config[4]);
				}
                    //Array formato exemplo: ARRAY CPF COLUNA_RECEBE = REFF
                    $this->link_com_parametro_coluna_extrai[ $config[4] ]['coluna_recebe']  = $coluna ;
                    $this->link_com_parametro_coluna_extrai[ $config[4] ]['valor']          = 0 ;
                    //ARRAY REFF VALOR = 0 (valor ser atualizado dinamicamente)
                    $this->link_com_parametro_coluna_recebe[ $coluna ]['valor']          = 0 ;
					
                }
                //Data 25/10/2017 por Cristiano MCon
                //TARGET
                if(isset($config[5])){
                    $this->link_com_parametro[$coluna]['target']		=" target='".$config[5]."'";
                }else{
                    //padrao _blank
                    $this->link_com_parametro[$coluna]['target']        ='';
                }
            }
        }
    }
	
 //Data 05/12/2017 15h17 por Cristiano MCon
 //Configura coluna para gerar link e incluir parametros - habilitado para multiplos parametros dinamicos
 //Esta funcao substitui a setLinks(), essa outra nao deixa configurar multiplos parametros dinamicos
 //!IMPORTANTE! Nao pode utilizar 2x a mesma coluna, se ela foi usada em setLinks() nao pode ser usada em setLinksToMultiParametros() e vice versa
 //Aqui sao setados um a um usando a coluna principal como array
 //O valor do primeiro parametro que é o nome da coluna é add ao final do link, os outros sao add apos esse
 //Mais abaixo essas informacoes sao validadas e resgatadas
 //Necessario pelo menos uma configuracao que é a url
 //TA COISA LINDA!! Bem facil de entender e escalavel
  public function setLinksToMultiParametros(array $arr){
	$this->setMetodosUtilizados(__FUNCTION__);
	//var_dump(__FUNCTION__,$arr);   
	if(count($arr)){
		foreach($arr as $coluna=>$arrs){			
			//var_dump($coluna,$arrs['config'],$arrs['col_adds']);			
			//Config basica
			if(count($arrs['config'])){
				//Link
                $this->parametros_link[$coluna]['link']		=$arrs['config'][0];
				//Title do link
				if(isset($arrs['config'][1])){
                	$this->parametros_link[$coluna]['title']=$arrs['config'][1];
				}
				//Target do link
				if(isset($arrs['config'][2])){
                	$this->parametros_link[$coluna]['alias']=$arrs['config'][2];
				}				
				//Alias do link
				if(isset($arrs['config'][3])){
                	$this->parametros_link[$coluna]['target']=$arrs['config'][3];
				}					
				//Param get usado no endereco do link, senao usa nome da coluna
				if(isset($arrs['config'][4])){
                	$col_principal_alias = $arrs['config'][4];
				}else{
					$col_principal_alias = $coluna;
				}
			}
			//Add coluna principal ao arrays dos parametros do link
			$this->parametros_link[ $coluna ]['col_adds'][$coluna] 	= $col_principal_alias;	
			$this->parametros_link['col_adds_all'][$coluna] 		= $coluna;		
			//Parametros - Colunas adicionais
			//Atualiza o link acima com os valores dessas colunas e vai incrementando colocando a primeira letra como parametro
			//Ex: link/p/
			if(isset($arrs['col_adds']) and count($arrs['col_adds'])){
				foreach($arrs['col_adds'] as $col_add){					
					$exp = explode('_',$col_add);
					//Se param get definido sintaxe formato: CPF_ncpf ( COLUNA ALVO + NOME DO PARAMETRO)
					if(count($exp) == 2){
						$col_add 	= $exp[0];
						$param_get	= $exp[1];
					}else{
						//senao usa nome da coluna
						$param_get	= $col_add;
					}
					$this->parametros_link[ $coluna ]['col_adds'][$col_add] = $param_get;
					//Add em array para ser possivel gerenciar, guarda coluna adicional e a coluna principal
					$this->parametros_link['col_adds_all'][$col_add] = $coluna;
				}
			}                        
                        // Se javascript setado
                        if(isset($arrs['javascript']) and count($arrs['javascript'])){  
                        $this->parametros_link[ $coluna ]['javascript']       = $arrs['javascript'][0];
                        }			
		}
	}	
	if($this->debug){
            var_dump($this->parametros_link);			
	}
  }
  //Verifica se esta ativada nova funcao dos links
  public function isHabilitadoLinksToMultiParametros(){
	  if(count($this->parametros_link)){
	  	return true;
	  }else{
		return false;
	  }
  }  
  //Data 05/12/2017 15h17 por Cristiano MCon  
  //Retorna nome da coluna principal se existir
  public function isParametroAdicional($col_add){
      if(isset($this->parametros_link['col_adds_all'][$col_add])){
          return $this->parametros_link['col_adds_all'][$col_add];
      }else{
          return false;
      }
  }
  //Retorna str param get para usar no link como parametro se existir
  public function getParamGet($coluna,$col_add){
      if(isset($this->parametros_link[ $coluna ]['col_adds'][$col_add])){
          return $this->parametros_link[ $coluna ]['col_adds'][$col_add];
      }else{
          return false;
      }
  }
  //Data 06/12/2017 por Cristiano MCon    
  //Seta conteudo da coluna
  public function setConteudoParamGet($coluna,$conteudo){	  
  	  $this->parametros_link['col_conteudo'][$coluna] = $conteudo;
  }
  //Data 06/12/2017 por Cristiano MCon    
  //Retona valor armazenado para a coluna
  public function getConteudoParamGet($coluna){
	  if(isset($this->parametros_link['col_conteudo'][$coluna])){
	  	  return $this->parametros_link['col_conteudo'][$coluna];
	  }else{
		  return false;
	  }
  }
  //Data 06/12/2017 por Cristiano MCon  
  //Monta link completo, considerando config do link e parametros adicionais
  public function setLinkCompleto($coluna){
    //Se existir link setado para coluna
    if(isset($this->parametros_link[ $coluna ]['link'])){
        //Extrai esse link
        $link = $this->parametros_link[ $coluna ]['link'];
        if(count($this->parametros_link[ $coluna ]['col_adds'])){
            //Varre array, extrai nome da coluna que tem o conteudo e o alias q sera usado como parametro no link
            foreach($this->parametros_link[ $coluna ]['col_adds'] as $col_name=>$param_get){
                //Atencao! Se possuir conteudo entao add como parametro do link, senao prossegue
                if($this->getConteudoParamGet($col_name)){
                      //echo __FUNCTION__.': Col name: '.$col_name.' Conteudo: '.$this->getConteudoParamGet($col_name).' Param get:'.$param_get.'<br>';
                      $link .= '/'.$param_get.'/'.$this->getConteudoParamGet($col_name);
                }
            }		  
        }                    
        return $link;
    }else{
            return false;
    }
  }
  //Data 06/12/2017 por Cristiano MCon  
  //Verifica se coluna esta configurada para gerar link 
  public function isColunaConfiguradaParaLink($coluna){
	  //Se nao for numero zero
	  if($coluna){
		  if(in_array($coluna,array_keys($this->parametros_link))){
			  return true;
		  }else{
			  return false;
		  }
	  }
  }
  //Retorna title
  public function getTitleColunaConfiguradaParaLink($coluna){	  
	  if(isset($this->parametros_link[ $coluna ]['title'])){
		  return $this->parametros_link[ $coluna ]['title'];
	  }else{
		  return false;
	  }
	}
  //Retorna target
  public function getTargetColunaConfiguradaParaLink($coluna){	  
	  if(isset($this->parametros_link[ $coluna ]['target'])){
		  return $this->parametros_link[ $coluna ]['target'];
	  }else{
		  return false;
	  }
	}
  //Retorna alias
  public function getAliasColunaConfiguradaParaLink($coluna,$dados){	  
	  if(isset($this->parametros_link[ $coluna ]['alias'])){
		  return $this->parametros_link[ $coluna ]['alias'];
	  }else{
		  return $dados;
	  }
	}
  
/* Retorna funcao javascript
 * 1 - nome da funcao javascript
 * 2 - opcional - nome da coluna usada como parametro  
 */
  public function getJavascriptFunctionColunaConfiguradaParaLink($coluna){	
    if(isset($this->parametros_link[ $coluna ]['javascript'])){        
            //Ja retorna pronto para incluir na tag a href
            $valor = $this->getConteudoParamGet($coluna);   
            //substitui espacos por underline            
            $valor = str_replace(' ','_',$valor);
            return "javascript:".$this->parametros_link[ $coluna ]['javascript']."('$valor')";
    }else{
            return false;
    }
}
/** 
* Função que extrai apenas as colunas da consulta sql
* @param Resource Id
* @return Array 
*/ 	
	public function getColunasByResourceId($ri)
	{
		$this->setMetodosUtilizados(__FUNCTION__);		
		for($i=0;$i < $ri->columnCount();++$i)
		{
			$metadata = $ri->getColumnMeta($i);
			if($metadata['name'] != 'hidden')
			{
				$this->colunas[] = $metadata['name'];
			}
		}
		return $this->colunas;
	}
/*
*	Extrai colunas e valores via resource id
*/
	public function setResourceId($ri)
	{		
		$this->setMetodosUtilizados(__FUNCTION__);
        $arr_bi = [];
		//Extrai valores
		$valores = array();
		while($dados=$ri->fetch(PDO::FETCH_NUM))
		{			
			$valores[] = $dados;
                        //Create 21/04/2018 19h14 por Cristiano MCon
                        //Solucao para grafico para evitar de puxar 2x a mesma query consumindo memoria
                        //Ideia replicada de HString::getArrayByResourceId
                        //Originalmente esta funcao nao retornava nada mas agora vai retornar esse array bidimensional
                        $status = ucfirst( $dados[0] );
                        $arr_bi[ $status ] = $dados[1];
		}
		//Extrai colunas
		$colunas=$this->getColunasByResourceId($ri);                
		//Joga no fluxo
		$this->setArrayDados($colunas,$valores);			
                return $arr_bi;
	}	
	
/*
* Metodo que recebe os dados em arrays para montar tabulacao
 * Formato dos arrays
 * Array colunas - extrai somente o cabecalho que fica na key 1 do array
 * $arr['colunas']   = $dados_csv[0]; 
 * Arrays valores - fazer loop dos dados e incrementar no array gerando nova keys 
 * $arr['valores'][] = $dados;          
*/	
	public function setArrayDados(array $colunas,array $valores){
		
		$this->setMetodosUtilizados(__FUNCTION__);		
		
		if(!is_array($colunas) or !is_array($valores))
		{
			die(__FUNCTION__.":ERRO:TODOS OS PARAMETROS DEVEM SER ARRAY!");
		}
		//Guarda array com as colunas da query
		$this->array_colunas=$colunas;	
		
		//Combina arrays - Casa arrays - Array campos x Array valores 
		//Para ser possivel usar na montagem dos links
		foreach($valores as $arr)
		{			
			$this->array_colunas_valores[] = array_combine(array_values($colunas),array_values($arr));			
		}
                //chama function para gerar toggle, html com colunas para ocultar ja fica pronto para uso
                $this->setHideCollumns();                
	}        

/** 
* Campos que serao usados para retornar total
* @param Resource Id
* @return Array 
*/ 	
	public function setTotalizadores($campo)
	{
            //echo $campo;
		$this->setMetodosUtilizados(__FUNCTION__);		
		$this->totalizadores[$campo] = 0;
	}
	
	public function setTotalizador($campo,$dados)
	{
		$this->setMetodosUtilizados(__FUNCTION__);				
		$this->totalizadores[$campo] += $dados;
	}

	public function getTotalizador($campo=null)
	{
		$this->setMetodosUtilizados(__FUNCTION__);
                if($campo){
                    return $this->totalizadores[$campo];
                }
                return $this->totalizadores;
	}
	public function getResultCheckboxCondicao(){
            $this->setMetodosUtilizados(__FUNCTION__);		
            return [ $this->checkbox_condicao_yes_match , $this->checkbox_condicao_no_match ];
	}
	
/*
* Padrao true, liga/desliga ordenacao nas linhas	
*/
	public function setOrdenacao($v=true){
		$this->setMetodosUtilizados(__FUNCTION__);
		$this->ordenar = $v;
	}	

	public function setTableID($novoId){
		$this->table_id= $novoId;
	}

/*
* Metodo final que retorna os resultados da consulta listados em table e ja com as config aplicadas
*/	
	public function getDatagrid($table_id=NULL){            
                //armazena funcao usada para rastreabilidade de erros 
		$this->setMetodosUtilizados(__FUNCTION__);		
		//Se id table definido, entao atualiza prop, senao usa a padrao=dg_table_id
		if($table_id){ 
			$this->table_id= $table_id; 
		}		
		//Define css da tag table
		if(isset($this->css_class['table'])){
			$tag_table="class='".$this->css_class['table']."'";
		}		
		//Define css da tag tr
		if(isset($this->css_class['tr'])){					
			$tag_tr="class='".$this->css_class['tr']."'";
		}                

		$tab ='';

		if($this->datatableStyle){
			$tab .= $this->datatableStyle;
		}	

		//TABLE THEAD
		$tab .= "
		<table id='".$this->table_id."' $tag_table>
		".$this->caption_descricao."
		<thead>
		<tr $tag_tr>";	                                
		//Table sem formatacao para exportar para excel
		$this->table_resultados = "<table><thead><tr>";                                
		//Se ordenacao ativada, inclui cerquilha 
		if($this->ordenar){ 
			array_unshift($this->array_colunas,'#'); 
		}
		//CHECKBOX - inclui input checkbox dinamicamente e id para ser usado em funcao js (opcional)
		if($this->checkbox){ 
			//valida marcacao dos checkboxes
			$marca = '';
			if($this->checkboxMarcado){
                            $marca = 'checked=true';
			}                                       
			$input_chekbox       = "<input type='checkbox' id='".$this->checkboxMarca."' $marca > ";
			$input_guarda_estado = '<input type="hidden" id="'.$this->checkboxEstado.'">';                      
			array_unshift($this->array_colunas,$input_chekbox.$input_guarda_estado);                     
		}                
		//TABLE THEAD TR
		foreach($this->array_colunas as $campo){
			$tab .='<th>'.$campo.'</th>';
			//Table sem formatacao
			$this->table_resultados .='<th>'.$campo.'</th>';   
                        //CSV deixa string livres, sem formatacao
                        $this->csv_resultados['colunas'][] = $campo;
		}//loop			
		$tab .='</tr></thead>';	
		//Table sem formatacao
		$this->table_resultados .='</tr></thead>';                
		//TABLE TBODY
		$ctd						=1;
		$tab 						.='<tbody>';
		//Table sem formatacao
		$this->table_resultados .='<tbody>';              
		//LOOP NOS REGISTROS
		foreach($this->array_colunas_valores as $arr){                    
			//Se ordenacao ativada, add valor do contador
			if($this->ordenar){ 
				array_unshift($arr,$ctd); 
			}                        
 			//inclui checkbox dinamicamente
                        if($this->checkbox){ 
                            $checkValue = '?';
                            $disabled='';
                            //valida marcacao dos checkboxes
                            $marca = '';
                            //html que armazena input checkbox
                            $html_checkbox='';
                            if($this->checkboxMarcado){
                                $marca = 'checked=true';
                            }                                                                            
                            //Coluna que sera utilizado como parametro
                            //Se existir, entao pega valor
                            if( isset($arr[ $this->checkboxColuna ])){
                                $checkValue = $arr[ $this->checkboxColuna ];                                
                            }
                            //Analisa condicao se estiver definida
                            if( isset($this->checkboxCondicao)){
                                //var_dump( $this->checkboxCondicao );
                               
                                $valor_linha_do_loop  = '';
								$valor_da_condicao = '';

                                if(isset($this->checkboxCondicao[0]) and isset($arr[$this->checkboxCondicao[0]])){
									//Remove tag html, deixando a string pura
                                	$valor_linha_do_loop  = strip_tags( $arr[ $this->checkboxCondicao[0] ] );
                                }

                                if(isset($this->checkboxCondicao[1])){
                                	$valor_da_condicao    = $this->checkboxCondicao[1] ;
								}
								
                                //Se NAO DEU MATCH!
                                if( $valor_linha_do_loop != $valor_da_condicao ){
                                    $disabled = 'disabled=disabled';
                                    //echo "NO MATCH!: $valor_linha_do_loop e $valor_da_condicao <br>";  
                                    $this->checkbox_condicao_no_match ++;
                                }else{
                                    $this->checkbox_condicao_yes_match ++;
                                    $html_checkbox = '<input type="checkbox" value="'.$checkValue.'" '.$disabled.' '.$marca.'>';
                                }                               
                            }
                            array_unshift($arr,$html_checkbox);
                        }
                        //Para CSV
                        unset($dados_csv);
	 		//TABLE TBODY TR        
			$tab .='<tr>';	
			//Table sem formatacao
			$this->table_resultados .='<tr>';					
			//LOOP NOS REGISTROS
			foreach($arr as $ind=>$dados){	
				//Status 
				$links_com_parametro		=false;
				$neo_links_com_parametro	=false;		
								                                                        
				//CONFIG:TOTALIZADORES 
				if(count($this->totalizadores)){
					if(!is_numeric($ind)){
						//Se coluna existir no array de campos totalizadores
						if(array_key_exists($ind,$this->totalizadores)){	
							//Envia valor para ser incrementado						
							$this->setTotalizador($ind,$dados);							
						}
					}
				}	
				//CONFIG:LINK COM MULTI PARAMETROS - NOVO
				//Data 05/12/2017 17h03 por Cristiano MCon				
				//O que esta funcionando nesta funcao:
				//-Montagem do link com todos os parametros definidos para a coluna
				//-Title do link
				//-Alias do link
				//-Target do link					
				//O que falta atualizar nesta funcao que a antiga tem:
				//-Javascript no link ( Importante por exemplo para arrumar a funcao javascript dentro do link senao ficara javascript:alert()/url/p/teste				
				if($this->isHabilitadoLinksToMultiParametros()){
					//Seta para evitar que entre no IF da funcao antiga dos links		
					$neo_links_com_parametro=true;
					//Se coluna configurada para ser parametro de link			
					if($this->isParametroAdicional($ind)){
						//$links_com_parametro	=true;
						$col_add_demo = $ind;	
						$this->setConteudoParamGet($col_add_demo,$dados);										
						$col_principal = $this->isParametroAdicional($col_add_demo);	
						/*if($this->debug){	
							echo '<hr>LOOP NOS REGISTROS<br>';						
							echo '-Ind <b>'.$col_add_demo.'</b><br>';	
							echo "-Seu conteudo: <i>".$this->getConteudoParamGet($col_add_demo).'</i><br>'; 		
							echo "-Pertence ao link da coluna: <i>".$this->isParametroAdicional($col_add_demo).'</i><br>'; 
							echo "-Link da coluna <b>$col_principal</b> atualizado: <i>". $this->setLinkCompleto( $col_principal ) . '</i><br><br>'; 					
						}*/						
					}

					//Se coluna da iteracao for a setada para ter o link - Monta link
					if($this->isColunaConfiguradaParaLink($ind)){                                            
						//Seta para evitar que entre no IF LINHA SIMPLES
						$links_com_parametro	=true;
						//Provisorio - puxar info abaixo via funcao 
						$title		=$this->getTitleColunaConfiguradaParaLink($ind);
						$target		=$this->getTargetColunaConfiguradaParaLink($ind);
						//Se alias configurado para o link entao utiliza senao usa conteudo da coluna
						$alias		=$this->getAliasColunaConfiguradaParaLink($ind,$dados);		
                                                //Monta link considerando link normal ou function javascript	        
                                                if( $this->getJavascriptFunctionColunaConfiguradaParaLink($ind)){            
                                                    $mnt_javascript = $this->getJavascriptFunctionColunaConfiguradaParaLink($ind);
                                                    $link = "<a href=".$mnt_javascript." title='$title' target='$target'> $alias </a>";	
                                                }else{
                                                    $link = "<a href='".$this->setLinkCompleto( $ind )."' title='$title' target='$target'> $alias </a>";	
                                                }
                                                if($this->debug){																																		
                                                        echo '<hr>Montado link normal<br>';												
                                                        echo "Coluna: <b>$ind</b> Link: <b>$link</b><br>";
                                                }                                                    
						$tab   .= '<td>'.$link.'</td>';						
						$this->table_resultados .='<td>'.$link.'</td>';						
                                                //CSV deixa string livres, sem formatacao                                                
                                                $dados_csv[] = $link;

					}									

				}
				
				//CONFIG:LINK COM PARAMETROS - DEPRECADA
				//-Verifica se coluna consta para montar link			
				//-Esta forma nao possibilita multiplos parametros dinamicos no link
				//Entra nesse IF somente se nova funcao nao estiver sendo utilizada
				if(!$neo_links_com_parametro){					
				if(count($this->link_com_parametro) and array_key_exists($ind,$this->link_com_parametro)){ 
					//Seta para evitar que entre no IF LINHA SIMPLES  
					$links_com_parametro		=true;                                     									
					//Varre array para casar campo alvo 
					foreach($this->link_com_parametro as $coluna=>$links){	                                
					 	//Data 28/09/2017 17h58 por Cristiano MCon - Dificil implementar isso mas deu certo por enquanto
						//COLUNA EXTRAI - Se INDEX existir no array, entao armazena valor para ser usado em coluna recebe
						//var_dump($this->link_com_parametro_coluna_extrai);							
						if(in_array($ind, array_keys($this->link_com_parametro_coluna_extrai))){                                    
							$col_recebe = $this->link_com_parametro_coluna_extrai[ $ind  ]['coluna_recebe'];                                    	                               		//echo 'YES! Index extrai de '.$ind.' Valor '.$dados.', sera descarregado em '. $col_recebe .'<br>';
							$this->link_com_parametro_coluna_recebe[ $col_recebe  ]['valor']    = $dados;
							$this->link_com_parametro_coluna_extrai[ $ind  ]['valor']           = $dados;
							//var_dump($this->link_com_parametro_coluna_extrai,$this->link_com_parametro_coluna_recebe);
						}                                            
						//Se coluna da iteracao for a setada para ter o link - Monta link
						if( $ind === $coluna ){   						                                                                                                        
							$url	= $this->link_com_parametro[$coluna]['url'];
							//Data 05/12/2017 17h03 por Cristiano MCon
	if($this->debug){
		echo '<hr>DENTRO DO IF link_com_parametro<br>';																		
		echo 'Match ind x coluna<br>';
		echo 'Ind( Coluna ) <b>'.$coluna.'</b> Conteudo <b>'.$dados.'</b> Link <b>'.$url.'</b><br>';							
	}
						    //Data 28/09/2017 17h58 por Cristiano MCon - Dificil implementar isso mas deu certo por enquanto
							//COLUNA RECEBE - Se INDEX existir no array - Trata url, replace na palavra chave pelo valor extraido do array COLUNA EXTRAI
							if(in_array($ind, array_keys($this->link_com_parametro_coluna_recebe))){
								//TRATA URL COM NOVA FUNCIONALIDADE
								$turl = explode('_CMON',$url);                                                        
								//Se tamanho valido para esquema 
								if(sizeof($turl) > 1){                                                            
									//var_dump($coluna,$turl);
									$parametro_especial = $turl[1];
									$valor_recebe = $this->link_com_parametro_coluna_extrai[$parametro_especial]['valor'];                     
								   // echo 'YES! Index '.$ind.' recebe valor '.$valor_recebe.'<br>';
									$palavra_chave = '_CMON'.$parametro_especial.'_CMON';
									//PARTE FINAL - REPLACE PALAVRA CHAVE POR VALOR DO PARAMETRO RECEBIDO
									$url = str_replace($palavra_chave, $valor_recebe, $url);
								}                                                        
							}                                                        
							
							//Se na url definida possuir parametros
							$explode = explode('?',$url);
							//Se maior que um entao possui parametros
							//Add parametros
							//if(count($explode) > 1){
                            $url .= $dados;
							//}							
							$title	= $this->link_com_parametro[$coluna]['title'];
							$js		= '';							
							//Se chamada javascript
							if(isset($this->link_com_parametro[$coluna]['javascript'])){
	                            $js	= $this->link_com_parametro[$coluna]['javascript'];		
							}	
							//Data 27/09/2017 18h01 por Cristiano MCon
							//ALIAS - Se definido entao assume descricao do link ao inves do valor original
							if(isset($links['alias'])){ 
								 $dados = $links['alias'];                                                            
							}
							//Javascript - Se true, inclui funcao javascript definida
							if($js){
								$furl = "<a href=\"javascript:".$js."('$url')\" title=\"$title\"> ".$dados." </a>";							
							}else{	
								//echo 'coluna: '.$coluna.' target: '.$links['target'].'<br>';         
								$furl = "<a href=\"$url\" title=\"$title\" ".$links['target']."> ".$dados." </a>";																						
							}							
							//Adiciona a coluna 
							$tab   .= '<td>'.$furl.'</td>';	
							//Table sem formatacao
							$this->table_resultados .='<td>'.$furl.'</td>';	
                                                //CSV deixa string livres, sem formatacao
                                                $dados_csv[] = $furl;
                                                        
						}
					}
				}}//Fim if count link com parametros	- funcao deprecada

				//LINHA SIMPLES, SEM CONFIG ADICIONADA
				//Se links com parametro NAO estiver ativado				
				if(!$links_com_parametro){							
					//$tab .= '<td>'. HString::getCapitalize( $dados ).'</td>';
					$tab .= '<td>'. $dados .'</td>';
					//Table sem formatacao
					$this->table_resultados .='<td>'.$dados.'</td>';
                                        //CSV deixa string livres, sem formatacao
                                        $dados_csv[] = $dados;
                                        
				}
			}
			$tab .='</tr>';
			//Table sem formatacao
			$this->table_resultados .='</td>';	
                        $this->csv_resultados['valores'][] = $dados_csv;
			//incrementa contador
			$ctd++;
		}//loop
		
		//Total de linhas
		$this->total_registros = $ctd;
                //Data 25/08/2020 por Cristiano Emicon
		//Seta indicador do total de registros
                //Para ser possivel extrair total e usar no html
                $this->setTotalizadores('Registros');
                $this->setTotalizador('Registros', $ctd-1);
                
		$tab .='</tbody></table>';									
                
                //Data 17/10/2017 por Cristiano MCon
                //Se botao submit habilitado
                if($this->checkboxBotao){
$btn_submit_checados = "<button class='".$this->checkbox_bto_css."' id='btn_submit_".$this->checkboxMarca."'>".$this->checkbox_bto_descricao." </button>";
$tab .= $btn_submit_checados;
                }
		//Table sem formatacao
		$this->table_resultados .='</tbody></table>';	

		//Se data tables ativado, inclui script CDNs 		
		if($this->datatables){			
            $tab .= $this->getDataTables();
		}				
		return $tab;
	}
       

/*
* Imprime na tela script javascript para popup
* Nome da funcao do popup gerado : popup_by_datagrid
*/
	public function getPopupDinamico($w=400,$h=780,$l=400,$t=0){
		$this->setMetodosUtilizados(__FUNCTION__);		
		$witdh = $w;$height = $h;$left = $l;$top = $t;					
		echo "
		<script>
		function popup_by_datagrid(e){	
		  url = e;
		  var tools = \"height=$height\";
		  tools += \",width=$witdh\";
		  tools += \",left=\" + ((screen.width / 2) - $left);
		  tools += \",top=$top\";
		  tools += \",status=yes\";
		  tools += \",toolbar=no\";
		  tools += \",menubar=no\";
		  tools += \",location=no\";
		  tools += \",resizable=yes\";
		  tools += \",scrollbars=yes\";
		  window.open(url, \"\", tools);
		}
		</script>";			
	} 	
	public function setCaption($nome){
		$this->setMetodosUtilizados(__FUNCTION__);
		$this->caption_descricao = "<caption> $nome </caption>";
	}	
/*
* Data tables
* Ativa/desativa datatables - padrao ativado
*/	
	public function setDataTables($v=true,$add_borda=false,$destaca_head=false,$destaca_line=false){
		$this->setMetodosUtilizados(__FUNCTION__);
		$this->datatables = $v;	
		if($add_borda){
			$this->datatableStyle .= "<style>
			 .dataTables_wrapper table   {
                                border-radius:5px;
                                border:1px #3c8dbc solid;
			}			
			</style>";
		}
		if($destaca_head){
			$this->datatableStyle .= "<style>
			 .dataTables_wrapper table thead tr {
				background:#3c8dbc; 
                                color:#fff;
			}						
			</style>";
		}
		if($destaca_line){
			$this->datatableStyle .= "<style>
			 .dataTables_wrapper table tbody tr:hover {
				background:#c4ceef; ;
				cursor:hand;cursor:pointer;
			}			
			
			</style>";
		}
		return $this->datatableStyle;
	}
/*
* Imprime na tela script jquery para datatables
* Carrega CDNs, necessario internet ligada 
*/
	public function getDataTables(){		
		$id = $this->table_id;
		$cdn = '';
		if($this->cdn_jquery){	
            $cdn = '<script src="http://code.jquery.com/jquery-1.9.1.js"></script>';
        }                
		//Script e Css fundamentais - Monta CSS baseado na url do sistema
               $dataTable_css = 'datatables/dataTables.bootstrap.css';
               $dataTable_bs = 'datatables/dataTables.bootstrap.min.js';
               //Retorna scripts e css
		return "
		$cdn
<script src=\"https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js\"></script>		
<script src=\"$dataTable_bs\"></script>
<link rel=\"stylesheet\" type=\"text/css\" href=\"$dataTable_css\">                
<script type='text/javascript'>                
jQuery(document).ready(function() {
/*
    jQuery('#".$id."').DataTable({
            'pageLength': 1000,
            'lengthMenu': [25, 50, 100, 500, 1000,2000,5000,99999]                       
        });
*/
/*
Habilitando para DataTables show and hide columns dynamically example
https://datatables.net/examples/api/show_hide.html 
*/
    var table = jQuery('#".$id."').DataTable({
            'pageLength': 25,
            'lengthMenu': [10,25, 50, 100, 500, 1000,5000,99999]                                       
    }); 
    jQuery('a.toggle-vis').on('click', function(e){
        e.preventDefault(); 
        // Get the column API object
        var column = table.column( jQuery(this).attr('data-column') );         
        // Toggle the visibility
        column.visible( ! column.visible() );
    });
});				
</script>
		";
	}	
	public function setCDNJQuery($bool=true){
		$this->cdn_jquery = $bool;
	}
        //Novo metodo - retorna table sem formatacao
        public function getTableResultados(){
            return $this->table_resultados;
        }		
        //Data 27/11/2018 por Cristiano Emicon
        //Novo metodo - retorna csv sem formatacao
        public function getCSVResultados(){
            return $this->csv_resultados;
        }		
        /* 
        * CHECKBOX        
        * Data 17/10/2017 12h00 por Cristiano MCon
        * Funcionalidade criada para ser possivel selecionar varias pendencias a serem pagas - sis cobranca
        * Padrao false, inclui input checkbox
         * Parametros: nome da coluna com o parametro, condicao q deve ser atendida
         * Ex: 'status=1'
        */
        //Define 
        //coluna que sera usada como value do checkbox
        //id do checkbox
        //id do input hidden que vai guardar o estado checkbox marcados ou desmarcados
        public function setCheckbox($coluna,$arr_ids_input,$bool=true){
                $this->setMetodosUtilizados(__FUNCTION__);
                $this->checkbox         = true;              
                $this->checkboxBotao    = $bool;              
                $this->checkboxColuna   = $coluna;
                //$this->checkboxCondicao = $condicao;
                $this->checkboxMarca    = $arr_ids_input[0]; 
                $this->checkboxEstado   = $arr_ids_input[1];   
                //$this->checkboxMarcado  = $checkado;                
        }	        
        //Define condicao para habilitar checkbox na linha
        //Formato: ['NOME DA COLUNA','VALOR'] - Exemplo: ['Exportado','Nao']
        public function setCheckboxCondicao($condicao){
            $this->checkboxCondicao = $condicao;
        }        
        //Define se todos os checkboxes por padrao serao marcados ou nao
        public function setCheckboxChecado($bool=false){
            $this->checkboxMarcado  = $bool; 
        }        
        //Define nova descricao do botao de acao 
        public function setCheckboxDescricaoBto($str){
            $this->checkbox_bto_descricao=$str;
        }
        //Define novo css do botao de acao
        public function setCheckboxCSSBto($str){
            $this->checkbox_bto_css=$str;
        }
        //Data 17/02/2018 por Cristiano MCon
        //monta hide collumns
        public function setHideCollumns(){
            $html = '<div> Ocultar coluna:';      
            foreach($this->array_colunas as $k=>$coluna){
                if($this->ordenar){
                    //avanca um numero devido ordenacao
                    $k = $k + 1;
                }
                $html .= '<a class="toggle-vis btn btn-link" data-column="'.$k.'">'.$coluna.'</a>';
            }
            $html .= '</div>';
            $this->toggle_hide_collumns = $html;
        }
        //print html
        public function getHideCollumns(){
            return $this->toggle_hide_collumns;
        }
        //Data 11/04/2018 11h por Cristiano MCon
        //Datagrid configurado para copiar conteudo de celula e colar em elemento indicado
        public function getDatagridCopiaCola($pelemento_cola_conteudo,$ptitulo=NULL,$psmall=NULL){
            $retorno = $this->getDatagrid();
            $id_table = $this->table_id;
            $titulo = $ptitulo ? "<h2 class='page-header'>$ptitulo <small>$psmall</small></h2>" : '';
            
echo "
<style>
table { width:90%; }
table td { text-align: left; border:1px #ccc solid; padding:2px 2px; }
table tr:hover { background:#ccc; cursor:hand; cursor:pointer; }
table tr:nth-child(odd) {
            background: #ccc;            
        }
.red{ background:#f00;}
</style>

<script>
$(function(){
$('#$id_table tr td').click(function(){
    var td_conteudo = $(this).text();
    console.log( td_conteudo );
    $('$pelemento_cola_conteudo').val( td_conteudo );    
});
});            
</script>
";                          
       echo $titulo.$retorno;              
            
        }
        //Create 04/05/2018 10h42 por Cristiano MCon
        //Monta datagrid via array 
        //Criei para gerar table para erros da carga, mostrar para usuarios com permissao sobre erro
        public function getDatagridByArray($arr,$class=null){            
            //Extrai primeira linha do array, no caso o cabecalho
            $cabecalho = array_shift($arr);
            //var_dump($cabecalho,$arr);                        
            echo $this->getTable($this->getTableHead($cabecalho), $this->getTableBody($arr),$class );
        }
        public function getTableHead($arr){
            $tab='<thead><tr>';
            foreach($arr as $campo){   
                //if(!$campo){ $campo='BLANK'; }
                $tab .='<th>'.str_replace(' ','#',$campo).'</th>';
            }
            $tab .='</tr></thead>';	            
            return $tab;
        }
        public function getTableBody($arr){
            $tab='<tbody>';
            foreach($arr as $k=>$dados){
                //var_dump($dados);
                $cols_numero = count($dados);
                $col_atual=1;
                $tab .= '<tr>';
                foreach($dados as $campo){
                    //var_dump($campo);
                    //if(!$campo){ $campo='BLANK'; }
                     $tab .='<td>'. $campo.'</td>';
                     //echo $cols_numero.' '.$col_atual;
                     $col_atual++;
                     if($col_atual == $cols_numero){
                         $tab .= '</tr>';
                     }
                }
            }
            $tab .='</tbody>';	            
            return $tab;
        }
        public function getTable($thead,$tbody,$class=NULL){
$css_padrao = "<style>
table { width:96%; }
table td { text-align: right; border:1px #ccc solid; padding:2px 2px; }
table tr:hover { background:#ccc; }
</style>";                
            if($class){ $class="class='$class'"; echo $css_padrao;}
            return "<table $class > $thead $tbody </table>";
        }

/*
Data 12/12/2018 por Cristiano Emicon
EXEMPLO DE USO COM FILTRO DINAMICO                  
$tab = new Tabulacao;
$tab->filtro_jquery=true;
$tab->filtro_jquery_add_lib=false; //opcional para nao carregar lib jquery em caso de conflito entre libs
$colunas = ['Banda','Estilo'];
$valores = [
    0=> ['Megadeth','Metal'],
    1=> ['Judas Priest','Metal'],
    2=> ['Faith No More','Grunge'],
];
echo $tab->tabulacaoByArray($colunas,$valores);		         
IMPORTEI este metodo da class tabulacao.class que crie em 12/04/2017 12h12 por Cristiano MCon	
Muito util junto com metodo filtro dinamico jquery
 */        
	//public function tabulacaoByArray($campos=array(),$valores=array())
	public function getTabulacaoByArray($campos=array(),$valores=array()){
		if(!is_array($campos) and !is_array($valores)){
			die(__FUNCTION__.":ERRO:TODOS OS PARAMETROS DEVEM SER ARRAY!");
		}		
		//Combina array - Array campos como indices de Array valores		
		//Para ser possivel usar na montagem dos links
		foreach($valores as $arr){			
			$arr_campos_valores[] = array_combine(array_values($campos),array_values($arr));			
		}		
		//CSS padrao bootstrap
		$css='class=table table-hover table-condensed';
		//Se redefinado css
		if($this->cssClass){ $css="class='$this->cssClass'";}						
		/*
		* INICIO
		*/
		$tab = "
		<table $css>
		<thead>
		<tr>";			
		/*
		* COLUNAS 
		*/
		//Ordenacao das linhas - padrao true
		if($this->mostraOrdenacao){ array_unshift($campos,'#'); }		
		foreach($campos as $campo){
			$tab .='<th>'.$campo.'</th>';
		}//loop			
		$tab .='</tr></thead>';			
		
		/*
		* CAMPOS -> VALORES
		*/
		$ctd=1;
		$tab .='<tbody id="fbody">';
		//varre array campos=>valores
		foreach($arr_campos_valores as $arr){
			//CONFIG:ORDENACAO (padrao true)				
			if($this->mostraOrdenacao){ array_unshift($arr,$ctd); }
			//Abre tag linha da table						
			$tab .='<tr>';	
			//varre valores considerando campo=>valor
			/*
			* VALORES - MONTA TABULACAO
			*/						
			foreach($arr as $ind=>$dados)
			{						
				//CONFIG:TOTALIZADORES 
				if(count($this->arrayColunasTotalizantes))
				{
					if(!is_numeric($ind))
					{
						if(in_array($ind,$this->arrayColunasTotalizantes))
						{
							//Atribui totalizador para propriedade com
							$var = 'totalizador_'.$ind;
							$this->$var += $dados;
							
						}
					}
				}
				//CONFIG:LINK COM PARAMETROS
				//OBS: colocando logica neste ponto, o link vai ocupar o lugar do valor do resultado								
				if(count($this->link_com_parametro))
				{
					//Varre link definidos
					foreach($this->link_com_parametro as $alvo=>$links)
					{							
						//echo 'Indx: '.$ind.'<br>Alvo: '.$alvo.'<br>Link descr: '.$links['descricao'].'<br>Link url: '.$links['url'].'<hr>';
						
						//Se campo da listagem bate com campo do link
						//Monta link
						if(  $ind === $alvo )
						{
							//echo 'Match!Indx: '.$ind.'->Alvo: '.$alvo.'->Dados: '.$dados.'<hr>';
							
							//Defini URl + Valor do campo
							$url	= $this->link_com_parametro[$alvo]['url'] . $dados;
							//Monta no link
							$furl	= "<a href=\"$url\"> ".$dados." </a>";															
							//Adiciona a coluna 
							$tab   .= '<td>'.$furl.'</td>';	
																					
						}else{
							
							//Se campo da listagem NAO bater com campo do link							
							//Add Coluna basica										
							$tab .= '<td>'.$dados.'</td>';
						}
					}
					
				}else{				
				
					//Coluna basica										
					$tab .= '<td>'.$dados.'</td>';
				}
			}
			$tab .='</tr>';
			$ctd++;
		}//loop
		
		//Total de linhas
		$this->total_registros = $ctd;
		
		$tab .='</tbody>';									
		$tab .='</table>';	
                //Se filtro dinamico ativado
                if($this->filtro_jquery){
                    $tab = $this->setFiltroDinamicoJquery() . $tab ;
                }                
		return $tab;
	}
	//@author Cristiano Miranda da Conceicao
	//@criacao 22/04/2016 14h27
	//habilita filtro nas linhas do table, retornando somente a palavra consultada	
	//@author Cristiano Miranda da Conceicao
	//@criacao 17/02/2017 11h02
	//nova propriedade para evitar conflito de biblis jquery	
        //Renomei de getFiltroJquery() para setFiltroDinamicoJquery()
	public function setFiltroDinamicoJquery(){            
            $jo = '';
            //Se incluir script da bibli jquery TRUE
            $jo = $this->filtro_jquery_add_lib ? "<script src=\"http://code.jquery.com/jquery-1.8.2.js\"></script>" : '';							
            //Monta script js		
            $jo .= "		
<script>
$(function(){
$(\"#searchInput\").on('keyup',function(){
    //split the current value of searchInput
    var data = this.value.toUpperCase().split(\" \");
    //create a jquery object of the rows
    var jo = $(\"#fbody\").find(\"tr\");
    if (this.value == \"\") {
        jo.show();
        return;
    }
    //hide all the rows
    jo.hide();

    //Recusively filter the jquery object to get results.
    jo.filter(function (i, v) {
        var t = $(this);
        for (var d = 0; d < data.length; ++d) {
            //if (t.is(\":contains('\" + data[d] + \"')\")) {
			 if (t.text().toUpperCase().indexOf(data[d]) > -1) {
                return true;
            }
        }
        return false;
    })
    //show the rows that match.
    .show();
}).focus(function () {
    this.value = \"\";
    $(this).css({
        \"color\": \"black\"
    });
    $(this).unbind('focus');
}).css({
    \"color\": \"#C0C0C0\"
});
});
</script>";

            $jo .= '
            <div> <span> Filtrar resultados </span> : <input id="searchInput" placeholder="Digite algo para filtrar" autofocus> </div>';
            return $jo;
	}	 	
}