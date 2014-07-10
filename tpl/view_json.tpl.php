<div id="actions">
	<ul class="actionButtons">
		<li id="Button4"><a href="<?php echo $mod_page ?>"><img src="media/style/<?php echo $theme; ?>/images/icons/prev.gif" alt="">&nbsp; Назад</a></li>
		<li id="Button4"><a href="#" onClick="data.submit()"><img src="media/style/<?php echo $theme; ?>/images/icons/save.png" alt="">&nbsp; Сохранить</a></li>
		<li id="Button3"><a href="<?php echo $mod_page . '&action=view_json' ?>"><img src="media/style/<?php echo $theme; ?>/images/icons/refresh.png" alt="">&nbsp; Обновить</a></li>
		<li id="Button2"><a href="<?php echo $mod_page . '&action=set_default_templates' ?>" onClick="if(!confirm('Вы действительно хотите установить все шаблоны по шаблону blank?')){return false;}"><img src="media/style/<?php echo $theme; ?>/images/icons/information.png" alt="">&nbsp; Установить шаблоны по шаблону blank</a></li>
	</ul>
</div>
<hr>
<h3><strong style="color:red">Внимание</strong> — ручное редактирование может разрушить структуру данных.</h3>
<p></p>
<p>
<form action="<?php echo $mod_page . '&action=view_json&save_json=0' ?>" method="post" name="data">
	<input type="hidden" name="templateid" value="0" />
	<textarea id="data_json" name="data_json" style="margin: 0; padding: 0; width: 100%; height: 100px; background: #fff;color: #000080;word-wrap: break-word;border: 1px solid #C5C5C5;"><?php echo $modx->htmlspecialchars($strJSON) ?></textarea>
	<pre style="font: 100 13px/16px monospace; margin: 0; padding: 0; width: 100%; background: #fff;color: #000080;word-wrap: break-word;border: 1px solid #C5C5C5;"><?php echo $viewJSON ?></pre>
</form>
</p>
