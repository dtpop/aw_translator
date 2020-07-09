<?php

/*
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
 */


if (rex_post('import-submit', 'boolean')) {


    
    $addon = rex_addon::get('awtranslator');

    if (isset($_FILES) && $_FILES['importfile']['size'] > 1) {
        $file_temp = awtranslator::getDir() . '/' . $_FILES['importfile']['name'];

        @move_uploaded_file($_FILES['importfile']['tmp_name'], $file_temp);
        if (trim($addon->getConfig('xmlmode'),'|') == 1) {
            xmlimport::import($file_temp);
        } else {
//            importer::run($file_temp);
        }
    }
}


$content = '<p>' . rex_i18n::msg('import_info_text') . '</p>';

$content .= '
    <fieldset>
        <input type="hidden" name="function" value="import" />';

$formElements = [];
$n = [];
$n['label'] = '<label for="aw-file-import">' . rex_i18n::msg('import_file') . '</label>';
$n['field'] = '<input type="file" id="aw-import-file" name="importfile" size="18" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');


$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-send rex-form-aligned" type="submit" name="import-submit" value="1"><i class="rex-icon rex-icon-import"></i>importieren</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$content .= '</fieldset>';


$fragment = new rex_fragment();
$fragment->setVar('class', 'edit');
$fragment->setVar('title', $this->i18n('import'));
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

echo '
   <form action="' . rex_url::currentBackendPage() . '" enctype="multipart/form-data" method="post" data-confirm="' . rex_i18n::msg('confirm_proceed') . '">
        ' . $content . '
    </form>';
