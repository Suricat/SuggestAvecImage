<?php
/**
 * Plugin SuggestAvecImage
 * @author	Suricat
 **/
class SuggestAvecImage extends plxPlugin {

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
	}



	
	/**
	 * Méthode qui affiche les suggestions d'articles
	 *
	 * @return	stdio
	 * @author	Suricat
	 **/
	public function showSuggestAvecImage() {
		$plxMotor = plxMotor::getInstance();

    $imgWidth = $this->getParam('imgWidth');
    $imgHeight = $this->getParam('imgHeight');
    $title = $this->getParam('title');
    $CImageURL = ($this->getParam('isCImageInTheme')) ? $plxMotor->urlRewrite($plxMotor->aConf['racine_themes'].$plxMotor->style) : PLX_PLUGINS.'SuggestAvecImage/cImage';

    if($imgWidth=='') $imgWidth = 138;
    if($imgHeight=='') $imgHeight = 95;
    if($title=='') $title = 'Vous aimerez aussi :';

		$nbArts = 4;
    $format='<div class="col sm4-6 sm5-4 sml-3 suggestImgArt">'
           .'<a href="#art_url" title="#art_title">'
           .'<img src="'.$CImageURL.'/img.php?src=#img_url'
           .'&w='.$imgWidth.'&h='.$imgHeight.'&crop-to-fit" width="'.$imgWidth.'px" height="'.$imgHeight.'px" alt="#art_title" />'
           .'#art_title'
           .'</a>'
           .'</div>';
    // recherche des catégories actives de l'article en cours de lecture
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
          $random = array_rand($aFiles, ($nbArts > $nbFiles ? $nbFiles : $nbArts) );
          foreach($random as $numart) 
              {
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
                    }  
              }

          // affichage des résultats
          if($arts) 
              {
                  $suggest = '<div class="suggest">'
                            .'<h3>'.$title.'</h3>'
                            .implode('', $arts)
                            .'<div class="clearer"></div>'
                            .'</div>';
                  echo $suggest;
              }
        }	
	}
	
	
}
?>