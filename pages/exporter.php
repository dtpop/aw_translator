<?php

// Sprachwahl (Quelle)
// später: Datum letzte Änderung
// später: Seiten auswählen

// run ...

// Alle Artikel einlesen
// 


if (rex_post('config-submit', 'boolean')) {
    $this->setConfig(rex_post('settings', [
       ['language','int']
    ]));
    $this->setConfig(rex_post('settings', [
       ['version','int']
    ]));
    $translator = new translator();
    $translator->export();
//    echo rex_view::success($this->i18n('saved'));
}

$sel_language = new rex_select();
$sel_language->setId('aw-select-language');
$sel_language->setName('settings[language]');
$sel_language->setSize(1);
$sel_language->setAttribute('class', 'form-control');
$sel_language->setSelected($this->getConfig('language'));


foreach (rex_clang::getAll() as $clang) {
    $sel_language->addOption($clang->getName(), $clang->getId());
}

$sel_version = new rex_select();
$sel_version->setId('aw-select-version');
$sel_version->setName('settings[version]');
$sel_version->setSize(1);
$sel_version->setAttribute('class', 'form-control');
$sel_version->setSelected($this->getConfig('version'));

$sel_version->addOption('Liveversion', 0);
$sel_version->addOption('Arbeitsversion', 1);


$content = '<p>Wähle die Sprache aus, die zur Übersetzung geschickt werden soll. Die Seiten müssen bereits die zu übersetzenden Elemente in der Quellsprache enthalten.</p>';

$content .= '<fieldset>';


$formElements = [];
$n = [];
$n['label'] = '<label for="aw-select-language">Sprache</label>';
$n['field'] = $sel_language->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');


if (rex_plugin::get('structure', 'version')->isAvailable()) {
    $formElements = [];
    $n = [];
    $n['label'] = '<label for="aw-select-version">Version</label>';
    $n['field'] = $sel_version->get();
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $content .= $fragment->parse('core/form/form.php');
}



$formElements = [];


$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="config-submit" value="1" ' . rex::getAccesskey($this->i18n('save'), 'save') . '>Export starten</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('flush', true);
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$content .= '</fieldset>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit');
$fragment->setVar('title', $this->i18n('export'));
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

echo '
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        ' . $content . '
    </form>';
