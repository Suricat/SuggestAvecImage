<?php
$plxPlugin = $plxAdmin->plxPlugins->getInstance('SuggestAvecImage');
?>
<br />
<?php $plxPlugin->lang('L_HELP') ?> :
<pre style="color:#000;font-size:12px; background:#fff; padding: 10px 20px 20px 20px; border:1px solid #efefef">
<?php
echo (htmlspecialchars("
<?php eval(\$plxShow->callHook('showSuggestAvecImage')) ?>
"));
?>
</pre>
<br />
<br />
<?php $plxPlugin->lang('L_HELP_CIMAGE') ?> : http://dbwebb.se/opensource/cimage
<br />
<br />
<pre style="color:#000;font-size:12px; background:#fff; padding: 10px 20px 20px 20px; border:1px solid #efefef">
/* ---------------------------------------------------------------*/
/* --- ADD THIS CSS IF YOU DON'T USE PLUCSS ----------------------*/
/* ---------------------------------------------------------------*/
.col {float: left;position: relative;min-height: 1px;padding-left: 0.5rem;padding-right: 0.5rem;width: 100%; box-sizing: border-box;}
.col.sml-0,.gallery.sml-0 li {display: none;}
.col.sml-1,.gallery.sml-1 li {width: 8.3333%;}
.col.sml-2,.gallery.sml-2 li {width: 16.6666%;}
.col.sml-3,.gallery.sml-3 li {width: 25%;}
.col.sml-4,.gallery.sml-4 li {width: 33.3333%;}
.col.sml-5,.gallery.sml-5 li {width: 41.6666%;}
.col.sml-6,.gallery.sml-6 li {width: 50%;}
.col.sml-7,.gallery.sml-7 li {width: 58.3333%;}
.col.sml-8,.gallery.sml-8 li {width: 66.6666%;}
.col.sml-9,.gallery.sml-9 li {width: 75%;}
.col.sml-10,.gallery.sml-10 li {width: 83.3333%;}
.col.sml-11,.gallery.sml-11 li {width: 91.6666%;}
.col.sml-12,.gallery.sml-12 li {width: 100%;}
/* ---------------------------------------------------------------*/
/* --- END CSS TO ADD---------------------------------------------*/
/* ---------------------------------------------------------------*/
</pre>


