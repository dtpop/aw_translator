<?php

// Module einlesen

$sql = rex_sql::factory();
$sql->setQuery('SELECT id, name FROM '.rex::getTable('module').' ORDER BY name');

$Modules = $sql->getArray();

// print_r($Modules);

$valArray = [];

foreach ($Modules as $Module) {
   $valArray[] = array('module'.$Module['id'],'string');
}



if (rex_post('config-submit', 'boolean')) {
    $this->setConfig(rex_post('settings', $valArray));
    echo rex_view::success($this->i18n('saved'));
}

$helptext = '<p>In den Feldern die Slice values eintragen, die für die Übersetzungstexte relevant sind. Beispiel: 1,2,3 - Es werden die Slice Values value1, value2 und value3 exportiert. Die Reihenfolge der Slice Values beeinflusst auch die Ausgabe. Wenn also in einem Modul zunächst value2 ausgegeben wird und danach value1, so ist sinnvollerweise 2,1 einzutragen, um im Übersetzungsfile die gleiche Reihenfolge zu haben wir auf der Website.</p>'
        . '<p>Beispiel für mform und mblock Elemente: <pre>{"1":["ueberschrift","text"]}</pre></p>'
        . '<p>Beispiel für normale Elemente Elemente: <pre>{"1":[],"2":[]}</pre></p>'
        ;

$fragment = new rex_fragment();
$fragment->setVar('title', 'Info');
$fragment->setVar('body', $helptext, false);
echo $fragment->parse('core/page/section.php');



$form = rex_config_form::factory('awtranslator');


$form->addFieldset('Allgemeine Einstellungen');

$field = $form->addCheckboxField('xmlmode');
$field->setLabel('XML Modus');
$field->addOption('XML Modus aktivieren', "1");
$field->setNotice('Im XML Modus (ab Version 1.1) wird der Export im XML Format erstellt.');

$form->addFieldset('Moduleinstellungen');

foreach ($Modules as $Modul) {
   $field = $form->addTextField('module'.$Modul['id']);
   $field->setLabel($Modul['name'].' ('.$Modul['id'].')');
//   $field->setNotice('<code>rex_config::get("warehouse","currency")</code>');   
}

$field = $form->addLinklistField('articles_to_translate');
$field->setNotice('Artikel, die übersetzt werden sollen auswählen. Kein Auswahl für alle Artikel.');
$field->setLabel('Zu übersetzende Artikel');

$field = $form->addTextAreaField('additional_meta_fields');
$field->setLabel('Zusätzliche Meta Felder');
$field->setNotice('Meta Infos eines Artikels z.B. yrewrite_description. Durch Zeilenschaltung getrennt. Wenn der Artikelname in die Übersetzung mit aufgenommen werden soll, so kann dieser mit der Angabe <pre>name</pre> mit aufgenommen werden. Wenn der Kategoriename mit übersetzt werden soll, so kann dieser mit der Angabe <pre>catname</pre> aufgenommen werden.');

$content = $form->get();

$fragment = new rex_fragment();
$fragment->setVar('title', 'Moduleinstellungen');
$fragment->setVar('body', $content, false);
$content = $fragment->parse('core/page/section.php');

echo $content;
