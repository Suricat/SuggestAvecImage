<?php
/**
 * Plugin SuggestAvecImage
 * @author	Suricat
 **/
class SuggestAvecImage extends plxPlugin {

  private $_pluginName = 'SuggestAvecImage';

	/**
	 * Constructeur de la classe
	 *
	 * @param	default_lang	langue par défaut
	 * @return	stdio
	 * @author	Suricat
	 **/
	public function __construct($default_lang) {

    // appel du constructeur de la classe plxPlugin (obligatoire)
		parent::__construct($default_lang);

		// droits pour accèder à la page config.php du plugin
		$this->setConfigProfil(PROFIL_ADMIN);

    // déclaration des hooks
		$this->addHook('showSuggestAvecImage', 'showSuggestAvecImage');
    $this->addHook('plxAdminEditArticle' , 'plxAdminEditArticle' );
    $this->addHook('plxAdminDelArticle'  , 'plxAdminDelArticle'  );
	}



  //---------------------------------------------------------------------------------------
  //--- Affichage des suggestions d'articles-----------------------------------------------
  //---------------------------------------------------------------------------------------
  public function showSuggestAvecImage() {
    $plxMotor = plxMotor::getInstance();

    $imgWidth  = $this->getParam('imgWidth');
    $imgHeight = $this->getParam('imgHeight');
    $title     = $this->getParam('title');
    $isCatOnly = $this->getParam('isCatOnly');
    $CImageURL = ($this->getParam('isCImageInTheme')) ? $plxMotor->urlRewrite($plxMotor->aConf['racine_themes'].$plxMotor->style) : PLX_PLUGINS.'SuggestAvecImage/cImage';

    if($imgWidth  =='') $imgWidth  = 138;
    if($imgHeight =='') $imgHeight = 95;
    if($title     =='') $title     = 'Vous aimerez aussi :';
    if($isCatOnly =='') $isCatOnly = 0;

    $nbArts = 4;
    $format='<div class="col sm4-6 sm5-4 sml-3 suggestImgArt">'
           .'<a href="#art_url" title="#art_title">'
           .'<img src="'.$CImageURL.'/img.php?src=#img_url'
           .'&w='.$imgWidth.'&h='.$imgHeight.'&crop-to-fit" width="'.$imgWidth.'px" height="'.$imgHeight.'px" alt="#art_title" />'
           .'#art_title'
           .'</a>'
           .'</div>';

    $random = array();

    if($isCatOnly!=1)
        {
          $OPlugin = $this->FCJSON_read();
          ///$this->setParam('TestVal', json_encode($OPlugin), 'cdata');   
          ///$this->saveParams();
          if($OPlugin!=null && isset($OPlugin['OIdArt']))
              {
                $OIdArt = $OPlugin['OIdArt'];
                $plxGlob_arts = clone $plxMotor->plxGlob_arts;
                $T4Suggest = $OIdArt[$plxMotor->plxRecord_arts->f('numero')]['T4Suggest'];
                $aFiles = array();
                
                for($i=0; $i<count($T4Suggest); $i++)
                    {
                      array_push($aFiles, $plxGlob_arts->query('/^'.$T4Suggest[$i].'.(.+).xml$/','','sort',0,1)[0]);
                      array_push($random, $i);
                    }
              }  
        }

    if($isCatOnly==1 || count($random)==0)
        {   
          // recherche des catégories actives de l'article en cours de lecture
          $nbArtsPlus1 = $nbArts+1; // +1 pour avoir 4 articles si on doit élinminer celui en cours de lecture
          $artCatIds = explode(',', $plxMotor->plxRecord_arts->f('categorie'));
          $activeCats = explode('|',$plxMotor->activeCats);
          $cat_ids = array_intersect($artCatIds,$activeCats);
          $cat_ids = ($cat_ids ? implode('|', $cat_ids) : '');
          // recherche de tous les articles publiés dans les catégories actives de l'article en cours de lecture
          $plxGlob_arts = clone $plxMotor->plxGlob_arts;
          $motif = '/^[0-9]{4}.((?:[0-9]|home|,)*(?:'.str_pad($cat_ids,3,'0',STR_PAD_LEFT).')(?:[0-9]|home|,)*).[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
          
          $aFiles = $plxGlob_arts->query($motif,'art','rsort',0,9999,'before');
          $nbFiles = sizeof($aFiles);
          if($aFiles and $nbFiles>1) 
              {
                $arts = array();
                // recherche aléatoire des articles à recommander
                $random = array_rand($aFiles, ($nbArtsPlus1 > $nbFiles ? $nbFiles : $nbArtsPlus1) ); 
                ///echo(json_encode($aFiles));
              }
        }

    if(count($random)!=0)
        {
          $nbArtsAdded=0;
          foreach($random as $numart) 
              {
                if($nbArtsAdded==$nbArts) break;
                // on ne liste pas l'article en cours de lecture 
                if($aFiles[$numart] <> basename($plxMotor->plxRecord_arts->f('filename'))) 
                    {
                      $art = $plxMotor->parseArticle(PLX_ROOT.$plxMotor->aConf['racine_articles'].$aFiles[$numart]);
                      $thumbnail = $art['thumbnail'];
                      if(!$thumbnail)
                          {
                            if(preg_match('/<img\s+.*?src=[\"\']?([^\"\' >]*)[\"\']?[^>]*>/i', $art['chapo'], $thumbnail)==1)
                                {
                                  $thumbnail = $thumbnail[1];
                                }
                            else
                            if(preg_match('/<img\s+.*?src=[\"\']?([^\"\' >]*)[\"\']?[^>]*>/i', $art['content'], $thumbnail)==1)
                                {
                                  $thumbnail = $thumbnail[1];
                                }
                            else{ continue;
                                }
                          }
                      $row = str_replace('#art_url',$plxMotor->urlRewrite('?article'.intval($art['numero']).'/'.$art['url']),$format);    
                      $row = str_replace('#art_title',plxUtils::strCheck($art['title']),$row);
                      $row = str_replace('#img_url',$plxMotor->urlRewrite($thumbnail),$row);
                      $arts[] = $row;
                      $nbArtsAdded++;
                    }  
              }

          // affichage des résultats
          if($arts) 
              {
                  $suggest = '<div class="suggest">'
                            .(($title && $title!='') ? '<h3>'.$title.'</h3>' : '')
                            .implode('', $arts)
                            .'<div class="clearer"></div>'
                            .'</div>';
                  echo $suggest;
              }
        } 
  }



  //---------------------------------------------------------------------------------------
  //--- Activation du plugin---------------------------------------------------------------
  //---------------------------------------------------------------------------------------
  public function OnActivate() {
    $this->init();
  }



  //---------------------------------------------------------------------------------------
  //--- Initialisation des éléments de fonctionnement du plugin----------------------------
  //--- Création et enregistrement de l'objet OIdArt si isCatOnly est à 0------------------
  //--- OIdArt contient IdArticle, TCat, TTag et T4Suggest---------------------------------
  //---------------------------------------------------------------------------------------
  public function init(){
    $isCatOnly = plxUtils::cdataCheck(trim($this->getParam('isCatOnly')));
    if($isCatOnly!=1)
        {
          // construction de OIdArt
          $OIdArt = $this->createOIdArt();   
          if(count($OIdArt)>0)
              {
                $this->addT4Suggest($OIdArt); 
                
                $OPlugin = array();

                $OPlugin['OIdArt'] = $OIdArt;

                // enregistrement de OIdArt au format JSON dans la configuration du plugin
                $this->FCJSON_write($OPlugin);
              }
        }
  }



  //---------------------------------------------------------------------------------------
  //--- Création ex nihilo de l'objet OIdArt avec IdArticle, TCat, TTag-------------------
  //--- à partir de tous les articles publiés---------------------------------------------
  //---------------------------------------------------------------------------------------
  // OIdArt = { IdArt1 : { TCat : [ IdCat1,IdCat2 ], TTag : [ tag1, tag2,..., tagN ] }
  //          , IdArt2 : { TCat : [ IdCat1        ], TTag : [ tag1, tag2,..., tagN ] }
  //          ...
  //          , IdArtN : { TCat : [ IdCat1        ], TTag : [ tag1, tag2,..., tagN ] }
  //          }
  protected function createOIdArt(){
    $OIdArt = array();

    $plxMotor = plxMotor::getInstance();

    // Récupération des fichiers
    $plxGlob_arts = clone $plxMotor->plxGlob_arts;
    $motif = '/^[0-9]{4}.(?:[0-9]|home|,)*(?:'.$plxMotor->activeCats.'|home)(?:[0-9]|home|,)*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
    if($aFiles = $plxGlob_arts->query($motif,'art',$sort,0,9999,'before')) 
        {
          foreach($aFiles as $v) 
              { // On parcourt tous les fichiers
                $art = $plxMotor->parseArticle(PLX_ROOT.$plxMotor->aConf['racine_articles'].$v);
                $TCat = explode(',', plxUtils::strCheck($art['categorie']));
                $TTag = explode(',', plxUtils::strCheck($art['tags']));

                $this->NettoyageTab($TCat, 0x03);
                $this->NettoyageTab($TTag, 0x06);
                
                if(in_array('draft', $TCat)) continue;
                
                // recupere les données de l'article
                $OIdArt[''.plxUtils::strCheck($art['numero'])] = array('TCat' => $TCat,'TTag' => $TTag);
              }
        }

    return($OIdArt);
  }


  //---------------------------------------------------------------------------------------
  //--- Lecture du fichier JSON contenant les données de travail du plugin dans le---------
  //--- répertoire de configuration du plugin et conversion du JSON en objet---------------
  //--- return : tableau d'association PHP ou null si vide ou erreur-----------------------
  //---------------------------------------------------------------------------------------
  protected function FCJSON_read(){
    $filename=PLX_ROOT.PLX_CONFIG_PATH.'plugins/'.$this->_pluginName.'_work.json';
    if(file_exists($filename)) 
        {
          $ret = file_get_contents($filename);
          if($ret!=FALSE)
              {
                return(json_decode($ret, true));
              }
          }
      return(null);
    }


  //---------------------------------------------------------------------------------------
  //--- Ecriture en JSON d'un tableau d'association PHP dans le fichier des données de-----
  //--- travail du plugin situé dans le répertoire de configuration du plugin--------------
  //--- return TRUE=OK, FALSE=echec--------------------------------------------------------
  //---------------------------------------------------------------------------------------
  protected function FCJSON_write($obj){
    $IsSucces=FALSE;
    if($obj!=null) 
        {
          $str = json_encode($obj);
          if($str!=FALSE)
              {
                $filename=PLX_ROOT.PLX_CONFIG_PATH.'plugins/'.$this->_pluginName.'_work.json';
                if(file_put_contents($filename, $str)!=FALSE) 
                    {  
                      $IsSucces = TRUE;
                    }
              }
        }
      return($IsSucces);
    }


  //---------------------------------------------------------------------------------------
  //--- Nettoyage de tableau-------- ------------------------------------------------------
  //----FContext : 0x01=get values, 0x02=TVide, 0x04=trim+lowercase------------------------
  //---------------------------------------------------------------------------------------
  protected function NettoyageTab(&$Tab, $FContext){
    // Lors de l'ajout d'un article diretement publié dans une catégorie "001", PluXml retourne
    // un tableau associatif de type ["1", "001"], donc on prend seulement les valeurs pour avoir des tableaux indicés
    if($FContext & 0x01) $Tab = array_values($Tab);
    
    // si un tableau ne contient qu'une chaine vide, on ne conserve qu'un tableau totalement vide
    if($FContext & 0x02) if(count($Tab)==1 && $Tab[0]=="") array_pop($Tab);

    // suppression des espaces et lowercase pour que les tags " MotClef" et "motclef" soient identiques
    if($FContext & 0x04) 
        { $i;
          for($i=0; $i<count($Tab); $i++)
              {
                $Tab[$i] = strtolower(trim($Tab[$i]));
              }
        }
  }



  //---------------------------------------------------------------------------------------
  //--- Ajout des T4Suggest à OIdArt ------------------------------------------------------
  //----T4Suggest est un tableau contenant jusqu'à 4 id d'articles à suggérer--------------
  //---------------------------------------------------------------------------------------
  protected function addT4Suggest(&$OIdArt){
    // Création dans OIdArt2 de tableauw intermédiaires "TTIdArtCatTagPoint" permettant de générer ensuite les T4Suggest
    // Struture :
    // OIdArt2[IdArt1].TTIdArtCatTagPoint = [ [ IdArt1, NbPoint ], [ IdArt2, NbPoint ], ... , [ IdArtN, NbPoint ] ];
    // OIdArt2[IdArt2].TTIdArtCatTagPoint = [ [ IdArt1, NbPoint ], [ IdArt2, NbPoint ], ... , [ IdArtN, NbPoint ] ];
    // ...
    // OIdArt2[IdArtN].TTIdArtCatTagPoint = [ [ IdArt1, NbPoint ], [ IdArt2, NbPoint ], ... , [ IdArtN, NbPoint ] ];
    
    $OIdArt2 = $OIdArt;
    foreach($OIdArt as $ParentKey => $ParentVal)
        {
          $TCatLen = count($ParentVal['TCat']);
          $TTagLen = count($ParentVal['TTag']);
          $index=0;
          $TTIdArtCatTagPoint = array();
          foreach($OIdArt as $KidKey => $KidVal)
              {
                $i;
                $NbPoint = 0;
                if($KidKey==$ParentKey) continue; // évite de mettre l'article parent dans TTIdArtCatTagPoint 
                
                // pour chaque Catégorie du parent présent dans la liste des catégories de l'enfant, on rajoute 10 points
                for($i=0; $i<$TCatLen; $i++)
                    {
                      if(in_array($ParentVal['TCat'][$i], $KidVal['TCat'])) $NbPoint+=10;
                    }
                    
                // pour chaque Tag du parent présent dans la liste des Tag de l'enfant, on rajoute 1 point
                for($i=0; $i<$TTagLen; $i++)
                    {
                      if(in_array($ParentVal['TTag'][$i], $KidVal['TTag'])) $NbPoint++;
                    }
                    
                $TTIdArtCatTagPoint[$index] = array($KidKey, $NbPoint);
                $index++;
              }
          $OIdArt2[$ParentKey]['TTIdArtCatTagPoint'] = $TTIdArtCatTagPoint;
        }

    
    // on ordonne chaque TTIdArtCatTagPoint par nombre de points décroissant
    $OIdArtTmp = $OIdArt2;
    foreach($OIdArtTmp as $k => $v)
        {
          $TTIdArtCatTagPoint = $v['TTIdArtCatTagPoint'];
          usort( $TTIdArtCatTagPoint, function($Tab1, $Tab2){
                                        $V1 = $Tab1[1];
                                        $V2 = $Tab2[1];
                                        if($V1 == $V2) 
                                            {
                                              return 0;
                                            }
                                        return ($V1 > $V2) ? -1 : 1;} );
          $OIdArt2[$k]['TTIdArtCatTagPoint'] = $TTIdArtCatTagPoint;
        }
    ///Code pour test : 
    ///$this->setParam('OPoint', json_encode($OIdArt2), 'cdata');   
    ///$this->saveParams();
    ///$OIdArt['OPoint'] = $OIdArt2;

    // création des T4Suggest : Tableaux de 4 IdArticle avec le plus grand nombre de points
    // exemple si TTIdArtCatTagPoint = [['2',13],['6',12],['1',11],['3',11],['4',11],['8',11],['5',10],['7',10],['9',10]]
    // alors on aura T4Suggest = ['2','6'] + 2 IdArticle pris au hasard parmi les éléments du tableau où le nombre de points est de 11
    foreach($OIdArt2 as $k => $v)
        {
          $T4Suggest;
          $i;
          $NbPoint1=0;
          $IndexDebNbPointIdentique=0;
          $IndexFinNbPointIdentique=0;
          $TTIdArtCatTagPoint = $v['TTIdArtCatTagPoint'];
          $TIdArt = array();
          for($i=0; $i<count($TTIdArtCatTagPoint); $i++)
              {
                $NbPoint2=$TTIdArtCatTagPoint[$i][1];
                if($NbPoint2==0)
                    {
                      $IndexFinNbPointIdentique=$i;
                      break;
                    }  // n'affiche jamais les articles d'une autre catégorie, même s'il y moins de 5 articles dans la catégorie
                if($NbPoint1!=0 && $NbPoint1!=$NbPoint2)
                    {
                      $IndexFinNbPointIdentique=$i;
                    }
                $NbPoint1=$NbPoint2;
                if($i>3 && $IndexFinNbPointIdentique>3) break;
                $IndexDebNbPointIdentique = $IndexFinNbPointIdentique;
                array_push($TIdArt, $TTIdArtCatTagPoint[$i][0]);
              }
              
          if(count($TIdArt)<=4)
              {
                $T4Suggest = $TIdArt;
              }
          else{ $T4Suggest = array_slice($TIdArt, 0, $IndexDebNbPointIdentique);
                $NbVal = count($T4Suggest);
                if($IndexFinNbPointIdentique==0) $IndexFinNbPointIdentique = count($TTIdArtCatTagPoint);
                $TValToRamdomizeOrder = array_slice($TIdArt, $IndexDebNbPointIdentique, $IndexFinNbPointIdentique);
                shuffle($TValToRamdomizeOrder);
                array_splice($T4Suggest, $NbVal, 0, array_slice($TValToRamdomizeOrder, 0, 4-$NbVal));
              }
          $OIdArt[$k]['T4Suggest'] = $T4Suggest;
        }
  }



  //---------------------------------------------------------------------------------------
  //--- Injection dans le hook plxAdminEditArticle du code appelant onEditArticle()------
  //--- passage en paramètre des catégories, des tags et état de publication de l'article--
  //---------------------------------------------------------------------------------------
  public function plxAdminEditArticle(){
      echo "<?php
        \$isCatOnly = plxUtils::cdataCheck(trim(\$this->plxPlugins->aPlugins['SuggestAvecImage']->getParam('isCatOnly')));

        if(\$isCatOnly!=1) // Si suggestion d'articles de même catégorie ET avec priorité pour ceux qui ont des Tags en commun
          {       
            // Récupère les caractéristiques actuelles de l'article (au moment de l'enregistrement)
            \$TCatNow = \$content['catId'];
            \$TTagNow = explode(',', plxUtils::cdataCheck(trim(\$content['tags'])));                     
            \$IsPubli = (isset(\$content['draft']) || isset(\$content['moderate'])) ? 0 : 1;
          
            \$this->plxPlugins->aPlugins['SuggestAvecImage']->onEditArticle(\$id, \$TCatNow, \$TTagNow, \$IsPubli);
          }
            ?>";
  }   



  //---------------------------------------------------------------------------------------
  //--- Création et enregistrement de l'objet OIdArt en JSON dans la configuration du----
  //--- plugin lors de l'enregistrement de l'article si changement de catégorie ou de tag
  //--- OIdArt contient IdArticle, TCat, TTag et T4Suggest--------------------------------
  //---------------------------------------------------------------------------------------
  public function onEditArticle($IdArt, $TCatNow, $TTagNow, $IsPubli){
    $IsT4SuggestToUpdate=0;

    $this->NettoyageTab($TCatNow, 0x03); 
    $this->NettoyageTab($TTagNow, 0x06);

    $OPlugin = $this->FCJSON_read();
    
    // Si OIdArt existe, on y cherche l'id de l'article, sinon on reconstruit OIdArt
    if($OPlugin!=null && isset($OPlugin['OIdArt']))
        {
          $OIdArt = $OPlugin['OIdArt'];
          $IdNum  = str_replace('_','',$IdArt);
          
          // si l'article est présent dans OIdArt...
          if(isset($OIdArt[$IdNum]))
              {
                // supprime l'article de OIdArt s'il est passé en brouillon
                if($IsPubli==0)
                    {
                      unset($OIdArt[$IdNum]);
                      $IsT4SuggestToUpdate=1;
                    }
                else{  // on vérifie si les catégories et les tags ont changé
                      $OArt = $OIdArt[$IdNum];
                          
                      // Récupère les caractéristiques enregistrées dans OIdArt pour cet article
                      $TCatOld = $OArt['TCat'];
                      $TTagOld = $OArt['TTag'];
                          
                      // on reconstruit les T4Suggest si les catégories ou les tags ont changé
                      if($TCatNow != $TCatOld ||
                         $TTagNow != $TTagOld)
                            {
                              $OIdArt[$IdNum] = array('TCat' => $TCatNow, 'TTag' => $TTagNow);
                              $IsT4SuggestToUpdate=1;
                            }
                    }
              }
          else // si l'id de l'article n'est pas dans OIdArt et que l'article est un brouillon, il n'y a rien à faire
          if($IsPubli==0)
              {
                $IsT4SuggestToUpdate=0;
              }
          else{ // si l'id de l'article n'est pas dans OIdArt, on l'ajoute avec son TCat et son TTag et on reconstruit les T4Suggest
                $OIdArt[$IdNum] = array('TCat' => $TCatNow, 'TTag' => $TTagNow);
                $IsT4SuggestToUpdate=1;
              }
        }
    else{ // [re]construction de OIdArt en entier
          $OIdArt = $this->createOIdArt();   
          if(count($OIdArt)>0)
              {
                $IsT4SuggestToUpdate=1;
              }
        }
        
    // [re]construire les T4Suggest dans OIdArt
    if($IsT4SuggestToUpdate==1)
        {
          $this->addT4Suggest($OIdArt);     

          if($OPlugin==null) $OPlugin = array();

          $OPlugin['OIdArt'] = $OIdArt;

          // enregistrement de OIdArt au format JSON dans la configuration du plugin
          $this->FCJSON_write($OPlugin);
        }
  }   



  //---------------------------------------------------------------------------------------
  //--- Injection dans le hook plxAdminDelArticle du code appelant onDelArticle()----------
  //--- passage en paramètre des catégories, des tags et état de publication de l'article--
  //---------------------------------------------------------------------------------------
  public function plxAdminDelArticle(){
      echo "<?php 
        \$isCatOnly = plxUtils::cdataCheck(trim(\$this->plxPlugins->aPlugins['SuggestAvecImage']->getParam('isCatOnly')));
      
        if(\$isCatOnly!=1) // Si suggestion d'articles de même catégorie ET avec priorité pour ceux qui ont des Tags en commun
            {           
              \$this->plxPlugins->aPlugins['SuggestAvecImage']->onDelArticle(\$id);
            }
           ?>";
  }   



  //---------------------------------------------------------------------------------------
  //--- Suppression de l'entrée correspondant à l'article dans l'objet OIdArt-------------
  //--- et reconstruction des T4Suggest----------------------------------------------------
  //---------------------------------------------------------------------------------------
  public function onDelArticle($IdArt){
    $OPlugin = $this->FCJSON_read();
    
    // Si OIdArt existe, on y cherche l'id de l'article, sinon on reconstruit OIdArt
    if($OPlugin!=null && isset($OPlugin['OIdArt']))
        {
          $OIdArt = $OPlugin['OIdArt'];
          $IdNum = str_replace('_','',$IdArt);
            
          // si l'article est présent dans OIdArt...
          if(isset($OIdArt[$IdNum]))
              {
                // supprime l'article de OIdArt et reconstruit les T4Suggest
                unset($OIdArt[$IdNum]);
                $this->addT4Suggest($OIdArt);     

                if($OPlugin==null) $OPlugin = array();

                $OPlugin['OIdArt'] = $OIdArt;

                // enregistrement de OIdArt au format JSON dans la configuration du plugin
                $this->FCJSON_write($OPlugin);
              }
        }
  }  
	
	
}
?>