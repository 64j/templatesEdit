templatesEdit
=============
<h3>UPD: 13.07.2014<h3>
<p>- исправлена ошибка при копировании шаблона</p>
<h3>UPD: 10.07.2014<h3>
<p>- исправлены мелкие ошибки</p>
<h3>UPD: 10.07.2014</h3>
<p>- добавленна возможность разделять поля на группы</p>
<p>- на странице модуля теперь можно увидеть как выглядят данные шаблона, так же скопировать настройку шаблона "blank" и перенести на другой сайт, либо импортировать с другого сайта.</p>

<h2>[EVO] templatesEdit — модуль и плагин для изменения вида документов в админ панели MODX</h2>


<p>Описание работы модуля и плагина</p>
<hr>
<p>Модуль предназначен для изменения полей и вкладок на странице редактирования документа, а так же их порядок.<br>
	Можно изменить/удалить:</p>
<ul>
	<li>Названия всех полей и добавить к ним описание либо всплывающую подсказку, видимость полей</li>
	<li>Названия вкладок и их видимость</li>
</ul>
<p>Так же можно менять вкладки местами, создавать новые или удалять, поля удалять нельзя но так же можно менять местами и переносить на другие вкладки, для скрытия поля достаточно изменить его видимость.</p>
<p>Вкладка &quot;<strong>Общие</strong>&quot; не удаляется и не изменяется её видимость, вкладки &quot;<strong>Права доступа</strong>&quot; и &quot;<strong>Мета теги</strong>&quot; - <strong style="color: #F00">игнорируются !</strong></p>
<p>При создании нового ТВ параметра он появляется на владке &quot;Общие&quot;, после создания его можно перенести на нужную вкладку в <strong>режиме редактирования шаблонов</strong> (читай ниже).</p>
<p>При удалении ТВ параметра он так же удаляется из вывода шаблона документа.</p>
<p>При создании нового шаблона, расположение его вкладок и полей берётся из шаблона <strong>blank</strong>, по этому желательно сразу после установки и подключения плагина, нужно зайти в любой документ с шаблоном blank и выставить желательное расположение полей которое будет использоваться в других шаблонах по умолчанию.<br>
Если в документе с шаблоном blank нажать &quot;Сбросить шаблон по умолчанию&quot;, то расположение вернётся в первозданному виду.</p>
<p>Если настроили вид шаблона <strong>blank</strong>, и лень во всех шаблонах делать одно и тоже, можно на странице модуля нажать <strong>Установить шаблоны по шаблону blank</strong> и тогда все шаблоны <span style="color: #F00">перезапишутся</span> по маске шаблона <strong>blank</strong>.</p>
<br>
<img src="http://wexar.ru/assets/images/templatesedit.image_4.png" alt="" width="240"><br>
<img src="http://wexar.ru/assets/images/templatesedit_image_docs_1.png" alt="" width="240"><br>
<img src="http://wexar.ru/assets/images/templatesedit_image_docs_2.png" alt="" width="240"><br>
<img src="http://wexar.ru/assets/images/templatesedit_image_docs_3.png" alt="" width="240"><br>
<br>
<h3>Установка модуля и плагина</h3>
<p>Устанавливаете модуль с кодом</p>
<pre style="font: 100 13px/16px monospace; margin: 10px; padding: 10px; background: #fff;color: #000080;word-wrap: break-word;border: 1px solid #C5C5C5;">
//&lt;?php
/*
* Управление шаблонами
*
* templatesEdit
*/<br>
include_once(MODX_BASE_PATH.'assets/modules/templatesEdit/module.templatesedit.php');</pre>
<p>&nbsp;</p>
<p>Перед установкой плагина проверяете наличие события <strong>OnDocFormTemplateRender</strong>, если нет такого то выполняете SQL запрос</p>
<pre style="font: 100 13px/16px monospace; margin: 10px; padding: 10px; background: #fff;color: #000080;word-wrap: break-word;border: 1px solid #C5C5C5;">
INSERT INTO modx_system_eventnames VALUES (NULL, 'OnDocFormTemplateRender', '1', 'Documents');
</pre>
<p>Устанавливаете плагин <strong>templatesEdit </strong><br></p>
<pre style="font: 100 13px/16px monospace; margin: 10px; padding: 10px; background: #fff;color: #000080;word-wrap: break-word;border: 1px solid #C5C5C5;">
//&lt;?php
/* templatesEdit
* plugin
* @events OnDocFormTemplateRender, OnTVFormDelete, OnTVFormSave, OnTempFormDelete, OnTempFormSave
* @settings &amp;editableTemplate=Редактировать шаблоны;list;true,false;true &amp;altRenderTemplate=Альтернативный вывод шаблона (div);list;true,false;false &amp;loadJquery=Load JQuery;list;true,false;true
*/<br>
require_once MODX_BASE_PATH.&quot;assets/modules/templatesEdit/plugin/plugin.templatesedit.php&quot;;</pre>
<p>Для включения <strong>режима редактирования шаблонов</strong>, в настройках плагина &quot;Редактировать шаблоны&quot; - <strong>true</strong>,<br>
для использования альтернативного вывода шаблонов (на div-ах) &quot;Альтернативный вывод шаблона (div)&quot; - <strong>true</strong> </p>
<p>&nbsp;</p>
<p>Далее нужно добавить событие <strong>OnDocFormTemplateRender</strong> в ядро MODX.</p>
<p>В файле <strong>manager/actions/mutate_content.dynamic.php</strong> перед строчкой </p>
<pre style="font: 100 13px/16px monospace; margin: 10px; padding: 10px; background: #fff;color: #000080;word-wrap: break-word;border: 1px solid #C5C5C5;">
&lt;!-- General --&gt;</pre>
<p>ставите</p>
<pre style="font: 100 13px/16px monospace; margin: 10px; padding: 10px; background: #fff;color: #000080;word-wrap: break-word;border: 1px solid #C5C5C5;">&lt;?php<br>$evtOut = $modx-&gt;invokeEvent('OnDocFormTemplateRender', array(<br>    'id' =&gt; $id<br>));<br>if (is_array($evtOut)) {<br>    echo implode('', $evtOut);<br>} else {<br>?&gt;</pre>
<p>и после строчки</p>
<pre style="font: 100 13px/16px monospace; margin: 10px; padding: 10px; background: #fff;color: #000080;word-wrap: break-word;border: 1px solid #C5C5C5;">
&lt;/div&gt;&lt;!-- end #tabSettings --&gt;</pre>
<p>ставите </p>
<pre style="font: 100 13px/16px monospace; margin: 10px; padding: 10px; background: #fff;color: #000080;word-wrap: break-word;border: 1px solid #C5C5C5;">
&lt;?php } ?&gt;</pre>
<p>После изменений плагин начнёт работать.</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>При выборе Алтернативного шаблона возможны проблемы с виджетами плагина ManagerManager, к примеру виджет <strong>mm_widget_showimagetvs</strong> не показывает картинку, для исправления достаточно изменить немного код самого виджета. </p>
<p>В файле <strong>assets/plugins/managermanager/widgets/showimagetvs/showimagetvs.php</strong> изменить строчку</p>
<pre style="font: 100 13px/16px monospace; margin: 10px; padding: 10px; background: #fff;color: #000080;word-wrap: break-word;border: 1px solid #C5C5C5;">
$j(&quot;#tv'.$tv['id'].'&quot;).parents(&quot;td&quot;).append(&quot;&lt;div class=\&quot;tvimage\&quot; id=\&quot;tv'.$tv['id'].'PreviewContainer\&quot;&gt;&lt;img src=\&quot;&quot;+url+&quot;\&quot; style=\&quot;&quot;+'.$style.'+&quot;\&quot; id=\&quot;tv'.$tv['id'].'Preview\&quot;/&gt;&lt;/div&gt;&quot;);</pre>
<p>заменить на ( <small>замена parents(&quot;td&quot;) на parent()</small> )</p>
<pre style="font: 100 13px/16px monospace; margin: 10px; padding: 10px; background: #fff;color: #000080;word-wrap: break-word;border: 1px solid #C5C5C5;">
$j(&quot;#tv'.$tv['id'].'&quot;).parent().append(&quot;&lt;div class=\&quot;tvimage\&quot; id=\&quot;tv'.$tv['id'].'PreviewContainer\&quot;&gt;&lt;img src=\&quot;&quot;+url+&quot;\&quot; style=\&quot;&quot;+'.$style.'+&quot;\&quot; id=\&quot;tv'.$tv['id'].'Preview\&quot;/&gt;&lt;/div&gt;&quot;);</pre>
