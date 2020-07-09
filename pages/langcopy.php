<?php

if (rex_post('config-submit', 'boolean')) {
    $this->setConfig(rex_post('settings', [
       ['sourcelang','int'],
       ['targetlang','int'],
       ['version_source','int'],
       ['version_target','int'],
       ['clearall','int'],
    ]));
    $langcopy = new langcopy();
    $langcopy->run();
    echo rex_view::success($this->i18n('lang_copied'));
}


$sel_source = new rex_select();
$sel_source->setId('aw-select-sourcelang');
$sel_source->setName('settings[sourcelang]');
$sel_source->setSize(1);
$sel_source->setAttribute('class', 'form-control');
$sel_source->setSelected($this->getConfig('sourcelang'));

foreach (rex_clang::getAll() as $clang) {
    $sel_source->addOption($clang->getName(), $clang->getId());
}

$sel_target = new rex_select();
$sel_target->setId('aw-select-targetlang');
$sel_target->setName('settings[targetlang]');
$sel_target->setSize(1);
$sel_target->setAttribute('class', 'form-control');
$sel_target->setSelected($this->getConfig('targetlang'));

foreach (rex_clang::getAll() as $clang) {
    $sel_target->addOption($clang->getName(), $clang->getId());
}

$sel_version_source = new rex_select();
$sel_version_source->setId('aw-select-version-source');
$sel_version_source->setName('settings[version_source]');
$sel_version_source->setSize(1);
$sel_version_source->setAttribute('class', 'form-control');
$sel_version_source->setSelected($this->getConfig('version_source'));

$sel_version_source->addOption('Liveversion', 0);
$sel_version_source->addOption('Arbeitsversion', 1);


$sel_version_target = new rex_select();
$sel_version_target->setId('aw-select-version-target');
$sel_version_target->setName('settings[version_target]');
$sel_version_target->setSize(1);
$sel_version_target->setAttribute('class', 'form-control');
$sel_version_target->setSelected($this->getConfig('version_target'));

$sel_version_target->addOption('Liveversion', 0);
$sel_version_target->addOption('Arbeitsversion', 1);



$content = '<p>Wählen Sie Quell- und Zielsprache und kopieren Sie alle Inhalte der Quellsprache in die Zielsprache.</p>'
      . '<p><strong>Wichtig! </strong> Erstellen Sie vor der Kopie ein Backup der Datenbank über die Backup-Funktion von Redaxo oder erstellen Sie einen Datenbank Dump!</p>';

$content .= '<fieldset>';

$formElements = [];
$n = [];
$n['label'] = '<label for="aw-select-sourcelang">Quellsprache</label>';
$n['field'] = $sel_source->get();
$formElements[] = $n;

if (rex_plugin::get('structure', 'version')->isAvailable()) {
    $n = [];
    $n['label'] = '<label for="aw-select-version-source">Version der Quelle</label>';
    $n['field'] = $sel_version_source->get();
    $formElements[] = $n;
}

$n = [];
$n['label'] = '<label for="aw-select-targetlang">Zielsprache</label>';
$n['field'] = $sel_target->get();
$formElements[] = $n;


if (rex_plugin::get('structure', 'version')->isAvailable()) {
    $n = [];
    $n['label'] = '<label for="aw-select-version-target">Version des Ziels</label>';
    $n['field'] = $sel_version_target->get();
    $formElements[] = $n;
}

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$formElements = [];

$n = [];
$n['label'] = '<label for="awtranslator-clearall">' . $this->i18n('clearall') . '</label>';
$n['field'] = '<input type="checkbox" id="awtranslator-clearall" name="settings[clearall]" value="1" ' . ($this->getConfig('clearall') ? ' checked="checked"' : '') . ' />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/checkbox.php');

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="config-submit" value="1" ' . rex::getAccesskey($this->i18n('save'), 'save') . '>Kopieren starten</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('flush', true);
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');


$content .= '</fieldset>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit');
$fragment->setVar('title', $this->i18n('langcopy'));
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');



echo '
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        ' . $content . '
    </form>';
