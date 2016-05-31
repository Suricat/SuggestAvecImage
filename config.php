<?php if(!defined('PLX_ROOT')) exit; ?>
<?php

# Control du token du formulaire
plxToken::validateFormToken($_POST);

if(!empty($_POST)) {
	$plxPlugin->setParam('title', $_POST['title'], 'string');
	$plxPlugin->setParam('imgWidth', $_POST['imgWidth'], 'numeric');
	$plxPlugin->setParam('imgHeight', $_POST['imgHeight'], 'numeric');
	$plxPlugin->setParam('isCImageInTheme', $_POST['isCImageInTheme'], 'numeric');
	$plxPlugin->saveParams();
	header('Location: parametres_plugin.php?p=SuggestAvecImage');
	exit;
}

$var = array();
# initialisation des variables
$var['title']     =  $plxPlugin->getParam('title')==''     ? $plxPlugin->getLang('L_TITLE') : $plxPlugin->getParam('title');
$var['imgWidth']  =  $plxPlugin->getParam('imgWidth')==''  ? 138 : $plxPlugin->getParam('imgWidth');
$var['imgHeight'] =  $plxPlugin->getParam('imgHeight')=='' ? 95 : $plxPlugin->getParam('imgHeight');
$var['isCImageInTheme'] =  $plxPlugin->getParam('isCImageInTheme')=='' ? 0 : $plxPlugin->getParam('isCImageInTheme');
?>

<form id="form_SuggestAvecImage" action="parametres_plugin.php?p=SuggestAvecImage" method="post">

	<fieldset>
	    <p class="field"><label for="id_title"><?php $plxPlugin->lang('L_LIB_TITLE') ?>&nbsp;:</label>
		<?php plxUtils::printInput('title',$var['title'],'text','20-20') ?></p>
		<p class="field"><label for="id_imgWidth"><?php $plxPlugin->lang('L_IMG_WIDTH') ?>&nbsp;:</label>
		<?php plxUtils::printInput('imgWidth',$var['imgWidth'],'text','2-5') ?> px</p>
		<p class="field"><label for="id_imgHeight"><?php $plxPlugin->lang('L_IMG_HEIGHT') ?>&nbsp;:</label>
		<?php plxUtils::printInput('imgHeight',$var['imgHeight'],'text','2-5') ?> px</p>
		<p><label for="isCImageInTheme">
		<input type="checkbox" id="isCImageInTheme" name="isCImageInTheme" value="1"<?php echo ($var['isCImageInTheme']) ? ' checked' : '' ?> />
		<?php $plxPlugin->lang('L_ISCIMAGEINTHEME') ?></label></p>
	</fieldset>
		
	<fieldset>
		<p class="in-action-bar">
			<?php echo plxToken::getTokenPostMethod() ?>
			<input type="submit" name="submit" value="<?php $plxPlugin->lang('L_SAVE') ?>" />
		</p>
	</fieldset>
</form>