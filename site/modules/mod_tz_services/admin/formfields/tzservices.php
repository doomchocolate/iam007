<?php
/*------------------------------------------------------------------------

# TZ Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2012 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

// no direct access

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');
jimport('joomla.html.editor');


class JFormFieldTzServices extends JFormField
{
    /**
     * The form field type.
     *
     * @var        string
     * @since   1.6
     */
    protected $type = 'TzServices';

    protected $prefix = 'tzform_';

    protected $tzid = null;

    protected $tzname = null;

    protected $tzvalue = null;

    protected static $initialised = false;

    /**
     * Method to get the field input markup.
     *
     * @return  string    The field input markup.
     * @since   1.6
     */
    protected function getInput()
    {

        $element = $this->element;
        $tzfields = $element->children();

        $elName = $element['name'];
        $this->tzid = $this->id;
        $this->tzname = $this->name;
        $this->tzvalue = $this->value;

        $doc = JFactory::getDocument();
        if (!version_compare(JVERSION,'3.0','ge')) {
            $doc->addScript(JUri::root(true) . '/modules/mod_tz_services/admin/js/jquery-1.9.1.min.js');
            $doc->addScript(JUri::root(true) . '/modules/mod_tz_services/admin/js/jquery-noconflict.js');
        }
        $doc->addScript(JUri::root(true) . '/modules/mod_tz_services/admin/js/base64.js');
        $doc->addScript(JUri::root(true) . '/modules/mod_tz_services/admin/js/jquery-ui-min.js');
        $doc->addScript(JUri::root(true) . '/modules/mod_tz_services/admin/js/tzservices.js');

        $bool   = 0;
        if(version_compare(JVERSION , '3.0', '>=')){
            $bool   = 1;
        }
        $doc -> addScriptDeclaration('
        window.addEvent("domready",function(){
            var languages   = new Object();
            languages.langRemove    = "'.JText::_('JTOOLBAR_REMOVE').'";
            languages.langEdit    = "'.JText::_('JTOOLBAR_EDIT').'";
            languages.langQuestion    = "'.JText::_('MOD_TZ_SERVICES_DELETE_QUESTION').'";
            tzservices("'.$this -> tzid.'","'.$this -> element['name'].'","'.$element['name'].'","'
            .$this -> tzvalue.'","'.$this -> prefix.'",'.$bool.',languages);
        });') ;

        $html[] = ' <div class="control-group">';
        $tblClass = 'table table-condensed';
        if (!version_compare(JVERSION, '3.0', 'ge')) {
            $tblClass = 'adminlist';
            $html[] = '<div class="clr"></div>';
        }
//        $html[] = '     <div id="drag">';
        $html[] = '     <table id="tz-table-' . $element['name'] . '" class="' . $tblClass . '">';
        $html[] = '         <thead>';
        $html[] = '             <tr>';
        $html[] = '             <th class="index" >' . 'No' . '</th>';
        $childCount = 0;
        $childRequired = false;
        if ($tzfields) {
            foreach ($tzfields as $xmlElement) {
                if ($xmlElement['required']) {
                    if ($childCount < 4) {
                        $html[] = '             <th>' . JText::_($xmlElement['label']) . '</th>';
                        $childRequired = true;
                    }
                    $childCount++;
                }
            }
        }
        if (!$childRequired) {
            $html[] = '           <th>' . JText::_('MOD_TZ_SERVICES_INFORMATION') . '</th>';
        }
        $html[] = '               <th width="20%" class="center">' . JText::_('JSTATUS') . '</th>';
        $html[] = '             </tr>';
        $html[] = '         </thead>';
        $html[] = '         <tbody>';
        $html[] = '         </tbody>';
        $html[] = '         <tfoot></tfoot>';
        $html[] = '     </table>';
        $html[] = '     </div>';

        $html[] = ' <div class="control-group">';
        $html[] = '<button class="btn btn-success" type="button" id="' . $this->prefix . 'button_add">' .
            '<i title="' . JText::_('JAPPLY') . '" class="icon-apply icon-white"></i> ' . JText::_('JAPPLY') . '</button>';
        $html[] = '<button class="btn" type="button" id="' . $this->prefix . 'button_cancel">' .
            '<i title="' . JText::_('JCANCEL') . '" class="icon-cancel"></i> ' . JText::_('MOD_TZ_SERVICES_RESET') . '</button>';
        $html[] = ' </div>';

        // Name tags html
        $html[] = '<div id="tz-form-' . $this->element['name'] . '" class="control-group">';

        // Get field with tzfield tags
        if ($tzfields) {
            foreach ($tzfields as $xmlElement) {
                $name = $this->prefix . $xmlElement['name'];
                $id = $name;

                $this->name = $name;
                $this->id = $id;
                $this->element = $xmlElement;
                $this->value = null;
                $this->required = null;
                $this->multiple = null;

                // Set default value
                if ($xmlElement['multiple']) {
                    $this->multiple = $xmlElement['multiple'];
                }
                if ($xmlElement['required']) {
                    $this->required = $xmlElement['required'];
                }
                if ($xmlElement['multiple']) {
                    $this->multiple = $xmlElement['multiple'];
                }

                if ($xmlElement['default']) {
                    $this->value = $xmlElement['default'];
                }

                $type = $xmlElement['type'];
                if (!$type) {
                    $type = 'text';
                }

                $html[] = ' <div class="control-group">';
                $html[] = '     <div class="control-label">';
                $html[] = $this->getLabel();
                $html[] = '     </div>';
                $html[] = '     <div class="controls">';

                // Check type
                switch ($type) {
                    case 'radio':
                        $html[] = $this->getInputRadio();
                        break;
                    case 'textarea':
                        $html[] = $this->getInputTextArea();
                        break;
                    case 'editor':
                        $html[] = $this->getInputEditor();
                        break;
                    case 'media':
                        if (version_compare(JVERSION, '3.0', 'ge')) {
                            $html[] = $this->getInputMedia();
                        } else {
                            $html[] = $this->getInputMedia25();
                        }
                        break;
                    case 'list':
                        if ($xmlElement['multiple']) {
                            $this->name .= '[]';
                        }
                        $html[] = $this->getInputList();
                        break;
                    default:
                        $html[] = $this->getInputText();
                        break;

                }

                $html[] = '     </div>';
                $html[] = ' </div>';
            }
        }
        //----------------------------

        $html[] = '</div>';

        $html[] = '<script>' . '

        var fixHelperModified = function(e, tr) {
                        var $originals = tr.children();
                        var $helper = tr.clone();
                        $helper.children().each(function(index) {
                            jQuery(this).width($originals.eq(index).width())
                         });
                        return $helper;
                    },
                    updateIndex = function(e, ui) {
                    jQuery("td.index", ui.item.parent()).each(function (i) {
                        jQuery(this).html(i + 1);
                        });
                    };
                    jQuery("#tz-table-' . $element['name'] . ' tbody").sortable({
                        helper: fixHelperModified,
                        stop: updateIndex
                        }).disableSelection();
        ' . ' </script>';
        $html[] = '     <input type="hidden" id="' . $this->tzid . '" name="' . $this->tzname . '" value="' . $this->tzvalue . '"/>';
        $html[] = '     <input type="hidden" name="' . $element['name'] . '_task" value="-1"/>';
//        $html[] = ' </div>';


        $html = implode("\n", $html);

        return $html;
    }

    // Get input with type is text
    protected function getInputText()
    {
        // Initialize some field attributes.
        $size = $this->element['size'] ? ' size="' . (int)$this->element['size'] . '"' : '';
        $maxLength = $this->element['maxlength'] ? ' maxlength="' . (int)$this->element['maxlength'] . '"' : '';
        $class = $this->element['class'] ? ' class="' . (string)$this->element['class'] . '"' : '';
        $readonly = ((string)$this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
        $disabled = ((string)$this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
        $required = $this->required ? ' required="required" aria-required="true"' : '';

        // Initialize JavaScript field attributes.
        $onchange = $this->element['onchange'] ? ' onchange="' . (string)$this->element['onchange'] . '"' : '';

        return '<input type="text" name="' . $this->name . '" id="' . $this->id . '" value="'
        . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . $required . '/>';
    }

    //--------------------------------------------------

    // Create media field with type is media for joomla 2.x
    protected function getInputMedia25()
    {
        $assetField = $this->element['asset_field'] ? (string)$this->element['asset_field'] : 'asset_id';
        $authorField = $this->element['created_by_field'] ? (string)$this->element['created_by_field'] : 'created_by';
        $asset = $this->form->getValue($assetField) ? $this->form->getValue($assetField) : (string)$this->element['asset_id'];
        if ($asset == '') {
            $asset = JRequest::getCmd('option');
        }

        $link = (string)$this->element['link'];
        if (!self::$initialised) {

            // Load the modal behavior script.
            JHtml::_('behavior.modal');

            // Build the script.
            $script = array();
            $script[] = '	function jInsertFieldValue(value, id) {';
            $script[] = '		var old_value = document.id(id).value;';
            $script[] = '		if (old_value != value) {';
            $script[] = '			var elem = document.id(id);';
            $script[] = '			elem.value = value;';
            $script[] = '			elem.fireEvent("change");';
            $script[] = '			if (typeof(elem.onchange) === "function") {';
            $script[] = '				elem.onchange();';
            $script[] = '			}';
            $script[] = '			jMediaRefreshPreview(id);';
            $script[] = '		}';
            $script[] = '	}';

            $script[] = '	function jMediaRefreshPreview(id) {';
            $script[] = '		var value = document.id(id).value;';
            $script[] = '		var img = document.id(id + "_preview");';
            $script[] = '		if (img) {';
            $script[] = '			if (value) {';
            $script[] = '				img.src = "' . JURI::root() . '" + value;';
            $script[] = '				document.id(id + "_preview_empty").setStyle("display", "none");';
            $script[] = '				document.id(id + "_preview_img").setStyle("display", "");';
            $script[] = '			} else { ';
            $script[] = '				img.src = ""';
            $script[] = '				document.id(id + "_preview_empty").setStyle("display", "");';
            $script[] = '				document.id(id + "_preview_img").setStyle("display", "none");';
            $script[] = '			} ';
            $script[] = '		} ';
            $script[] = '	}';

            $script[] = '	function jMediaRefreshPreviewTip(tip)';
            $script[] = '	{';
            $script[] = '		tip.setStyle("display", "block");';
            $script[] = '		var img = tip.getElement("img.media-preview");';
            $script[] = '		var id = img.getProperty("id");';
            $script[] = '		id = id.substring(0, id.length - "_preview".length);';
            $script[] = '		jMediaRefreshPreview(id);';
            $script[] = '	}';

            // Add the script to the document head.
            JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

            self::$initialised = true;
        }

        // Initialize variables.
        $html = array();
        $attr = '';

        // Initialize some field attributes.
        $attr .= $this->element['class'] ? ' class="' . (string)$this->element['class'] . '"' : '';
        $attr .= $this->element['size'] ? ' size="' . (int)$this->element['size'] . '"' : '';

        // Initialize JavaScript field attributes.
        $attr .= $this->element['onchange'] ? ' onchange="' . (string)$this->element['onchange'] . '"' : '';

        // The text field.
        $html[] = '<div class="fltlft">';
        $html[] = '	<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
            . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . ' readonly="readonly"' . $attr . ' />';
        $html[] = '</div>';

        $directory = (string)$this->element['directory'];
        if ($this->value && file_exists(JPATH_ROOT . '/' . $this->value)) {
            $folder = explode('/', $this->value);
            array_shift($folder);
            array_pop($folder);
            $folder = implode('/', $folder);
        } elseif (file_exists(JPATH_ROOT . '/' . JComponentHelper::getParams('com_media')->get('image_path', 'images') . '/' . $directory)) {
            $folder = $directory;
        } else {
            $folder = '';
        }
        // The button.
        $html[] = '<div class="button2-left">';
        $html[] = '	<div class="blank">';
        $html[] = '		<a class="modal" title="' . JText::_('JLIB_FORM_BUTTON_SELECT') . '"' . ' href="'
            . ($this->element['readonly'] ? ''
                : ($link ? $link
                    : 'index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset=' . $asset . '&amp;author='
                    . $this->form->getValue($authorField)) . '&amp;fieldid=' . $this->id . '&amp;folder=' . $folder) . '"'
            . ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
        $html[] = JText::_('JLIB_FORM_BUTTON_SELECT') . '</a>';
        $html[] = '	</div>';
        $html[] = '</div>';

        $html[] = '<div class="button2-left">';
        $html[] = '	<div class="blank">';
        $html[] = '		<a title="' . JText::_('JLIB_FORM_BUTTON_CLEAR') . '"' . ' href="#" onclick="';
        $html[] = 'jInsertFieldValue(\'\', \'' . $this->id . '\');';
        $html[] = 'return false;';
        $html[] = '">';
        $html[] = JText::_('JLIB_FORM_BUTTON_CLEAR') . '</a>';
        $html[] = '	</div>';
        $html[] = '</div>';

        // The Preview.
        $preview = (string)$this->element['preview'];
        $showPreview = true;
        $showAsTooltip = false;
        switch ($preview) {
            case 'false':
            case 'none':
                $showPreview = false;
                break;
            case 'true':
            case 'show':
                break;
            case 'tooltip':
            default:
                $showAsTooltip = true;
                $options = array(
                    'onShow' => 'jMediaRefreshPreviewTip',
                );
                JHtml::_('behavior.tooltip', '.hasTipPreview', $options);
                break;
        }

        if ($showPreview) {
            if ($this->value && file_exists(JPATH_ROOT . '/' . $this->value)) {
                $src = JURI::root() . $this->value;
            } else {
                $src = '';
            }

            $attr = array(
                'id' => $this->id . '_preview',
                'class' => 'media-preview',
                'style' => 'max-width:160px; max-height:100px;'
            );
            $img = JHtml::image($src, JText::_('JLIB_FORM_MEDIA_PREVIEW_ALT'), $attr);
            $previewImg = '<div id="' . $this->id . '_preview_img"' . ($src ? '' : ' style="display:none"') . '>' . $img . '</div>';
            $previewImgEmpty = '<div id="' . $this->id . '_preview_empty"' . ($src ? ' style="display:none"' : '') . '>'
                . JText::_('JLIB_FORM_MEDIA_PREVIEW_EMPTY') . '</div>';

            $html[] = '<div class="media-preview fltlft">';
            if ($showAsTooltip) {
                $tooltip = $previewImgEmpty . $previewImg;
                $options = array(
                    'title' => JText::_('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE'),
                    'text' => JText::_('JLIB_FORM_MEDIA_PREVIEW_TIP_TITLE'),
                    'class' => 'hasTipPreview'
                );
                $html[] = JHtml::tooltip($tooltip, $options);
            } else {
                $html[] = ' ' . $previewImgEmpty;
                $html[] = ' ' . $previewImg;
            }
            $html[] = '</div>';
        }

        return implode("\n", $html);
    }

    //---------------------------------------------------------

    // Create media field with type is media for joomla 3.x
    protected function getInputMedia()
    {
        $assetField = $this->element['asset_field'] ? (string)$this->element['asset_field'] : 'asset_id';
        $authorField = $this->element['created_by_field'] ? (string)$this->element['created_by_field'] : 'created_by';
        $asset = $this->form->getValue($assetField) ? $this->form->getValue($assetField) : (string)$this->element['asset_id'];
        if ($asset == '') {
            $asset = JFactory::getApplication()->input->get('option');
        }

        $link = (string)$this->element['link'];
        if (!self::$initialised) {
            // Load the modal behavior script.
            JHtml::_('behavior.modal');

            // Build the script.
            $script = array();
            $script[] = '	function jInsertFieldValue(value, id) {';
            $script[] = '		var old_value = document.id(id).value;';
            $script[] = '		if (old_value != value) {';
            $script[] = '			var elem = document.id(id);';
            $script[] = '			elem.value = value;';
            $script[] = '			elem.fireEvent("change");';
            $script[] = '			if (typeof(elem.onchange) === "function") {';
            $script[] = '				elem.onchange();';
            $script[] = '			}';
            $script[] = '			jMediaRefreshPreview(id);';
            $script[] = '		}';
            $script[] = '	}';

            $script[] = '	function jMediaRefreshPreview(id) {';
            $script[] = '		var value = document.id(id).value;';
            $script[] = '		var img = document.id(id + "_preview");';
            $script[] = '		if (img) {';
            $script[] = '			if (value) {';
            $script[] = '				img.src = "' . JURI::root() . '" + value;';
            $script[] = '				document.id(id + "_preview_empty").setStyle("display", "none");';
            $script[] = '				document.id(id + "_preview_img").setStyle("display", "");';
            $script[] = '			} else { ';
            $script[] = '				img.src = ""';
            $script[] = '				document.id(id + "_preview_empty").setStyle("display", "");';
            $script[] = '				document.id(id + "_preview_img").setStyle("display", "none");';
            $script[] = '			} ';
            $script[] = '		} ';
            $script[] = '	}';

            $script[] = '	function jMediaRefreshPreviewTip(tip)';
            $script[] = '	{';
            $script[] = '		var img = tip.getElement("img.media-preview");';
            $script[] = '		tip.getElement("div.tip").setStyle("max-width", "none");';
            $script[] = '		var id = img.getProperty("id");';
            $script[] = '		id = id.substring(0, id.length - "_preview".length);';
            $script[] = '		jMediaRefreshPreview(id);';
            $script[] = '		tip.setStyle("display", "block");';
            $script[] = '	}';

            // Add the script to the document head.
            JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

            self::$initialised = true;
        }

        $html = array();
        $attr = '';

        // Initialize some field attributes.
        $attr .= $this->element['class'] ? ' class="' . (string)$this->element['class'] . '"' : '';
        $attr .= $this->element['size'] ? ' size="' . (int)$this->element['size'] . '"' : '';

        // Initialize JavaScript field attributes.
        $attr .= $this->element['onchange'] ? ' onchange="' . (string)$this->element['onchange'] . '"' : '';

        // The text field.
        $html[] = '<div class="input-prepend input-append">';

        // The Preview.
        $preview = (string)$this->element['preview'];
        $showPreview = true;
        $showAsTooltip = false;
        switch ($preview) {
            case 'no': // Deprecated parameter value
            case 'false':
            case 'none':
                $showPreview = false;
                break;

            case 'yes': // Deprecated parameter value
            case 'true':
            case 'show':
                break;

            case 'tooltip':
            default:
                $showAsTooltip = true;
                $options = array(
                    'onShow' => 'jMediaRefreshPreviewTip',
                );
                JHtml::_('behavior.tooltip', '.hasTipPreview', $options);
                break;
        }

        if ($showPreview) {
            if ($this->value && file_exists(JPATH_ROOT . '/' . $this->value)) {
                $src = JURI::root() . $this->value;
            } else {
                $src = '';
            }

            $width = isset($this->element['preview_width']) ? (int)$this->element['preview_width'] : 300;
            $height = isset($this->element['preview_height']) ? (int)$this->element['preview_height'] : 200;
            $style = '';
            $style .= ($width > 0) ? 'max-width:' . $width . 'px;' : '';
            $style .= ($height > 0) ? 'max-height:' . $height . 'px;' : '';

            $imgattr = array(
                'id' => $this->id . '_preview',
                'class' => 'media-preview',
                'style' => $style,
            );
            $img = JHtml::image($src, JText::_('JLIB_FORM_MEDIA_PREVIEW_ALT'), $imgattr);
            $previewImg = '<div id="' . $this->id . '_preview_img"' . ($src ? '' : ' style="display:none"') . '>' . $img . '</div>';
            $previewImgEmpty = '<div id="' . $this->id . '_preview_empty"' . ($src ? ' style="display:none"' : '') . '>'
                . JText::_('JLIB_FORM_MEDIA_PREVIEW_EMPTY') . '</div>';

            $html[] = '<div class="media-preview add-on">';
            if ($showAsTooltip) {
                $tooltip = $previewImgEmpty . $previewImg;
                $options = array(
                    'title' => JText::_('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE'),
                    'text' => '<i class="icon-eye"></i>',
                    'class' => 'hasTipPreview'
                );
                $html[] = JHtml::tooltip($tooltip, $options);
            } else {
                $html[] = ' ' . $previewImgEmpty;
                $html[] = ' ' . $previewImg;
            }
            $html[] = '</div>';
        }

        $html[] = '	<input type="text" class="input-small" name="' . $this->name . '" id="' . $this->id . '" value="'
            . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" readonly="readonly"' . $attr . ' />';

        $directory = (string)$this->element['directory'];
        if ($this->value && file_exists(JPATH_ROOT . '/' . $this->value)) {
            $folder = explode('/', $this->value);
            $folder = array_diff_assoc($folder, explode('/', JComponentHelper::getParams('com_media')->get('image_path', 'images')));
            array_pop($folder);
            $folder = implode('/', $folder);
        } elseif (file_exists(JPATH_ROOT . '/' . JComponentHelper::getParams('com_media')->get('image_path', 'images') . '/' . $directory)) {
            $folder = $directory;
        } else {
            $folder = '';
        }

        // The button.
        if ($this->element['disabled'] != true) {
            JHtml::_('bootstrap.tooltip');

            $html[] = '<a class="modal btn" title="' . JText::_('JLIB_FORM_BUTTON_SELECT') . '" href="'
                . ($this->element['readonly'] ? ''
                    : ($link ? $link
                        : 'index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset=' . $asset . '&amp;author='
                        . $this->form->getValue($authorField)) . '&amp;fieldid=' . $this->id . '&amp;folder=' . $folder) . '"'
                . ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
            $html[] = JText::_('JLIB_FORM_BUTTON_SELECT') . '</a><a class="btn hasTooltip" title="' . JText::_('JLIB_FORM_BUTTON_CLEAR') . '" href="#" onclick="';
            $html[] = 'jInsertFieldValue(\'\', \'' . $this->id . '\');';
            $html[] = 'return false;';
            $html[] = '">';
            $html[] = '<i class="icon-remove"></i></a>';
        }

        $html[] = '</div>';

        return implode("\n", $html);
    }

    //------------------------------------

    // Create Editor with type is editor
    protected function getInputEditor()
    {
        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration('window.jInsertEditorText = function (text, editor) {
                            if (editor.match(/^image-thumb/)) {

                                var d = $(editor);
                                var src = text.match(/src=\".*?\"/i);
                                alert("test");
                            } else tinyMCE.execInstanceCommand(editor, "mceIframeContainer",false,text);
                        };');
        $rows = (int)$this->element['rows'];
        $cols = (int)$this->element['cols'];
        $height = ((string)$this->element['height']) ? (string)$this->element['height'] : '250';
        $width = ((string)$this->element['width']) ? (string)$this->element['width'] : '100%';
        $assetField = $this->element['asset_field'] ? (string)$this->element['asset_field'] : 'asset_id';
        $authorField = $this->element['created_by_field'] ? (string)$this->element['created_by_field'] : 'created_by';
        $asset = $this->form->getValue($assetField) ? $this->form->getValue($assetField) : (string)$this->element['asset_id'];

        // Build the buttons array.
        $buttons = (string)$this->element['buttons'];

        if ($buttons == 'true' || $buttons == 'yes' || $buttons == '1') {
            $buttons = true;
        } elseif ($buttons == 'false' || $buttons == 'no' || $buttons == '0') {
            $buttons = false;
        } else {
            $buttons = explode(',', $buttons);
        }

        $hide = ((string)$this->element['hide']) ? explode(',', (string)$this->element['hide']) : array();

        // Get an editor object.
        $editor = $this->getTZEditor();

        $clr    = null;
        if(!version_compare(JVERSION,'3.0','>=')){
            $clr    = '<div class="clr"></div>';
        }

        return $clr.$editor
            ->display(
                $this->name, htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8'), $width, $height, $cols, $rows,
                $buttons ? (is_array($buttons) ? array_merge($buttons, $hide) : $hide) : false, $this->id, $asset,
                $this->form->getValue($authorField)
            );
    }

    //***** Method to get a JEditor object based on the form field.
    protected function getTZEditor()
    {
        // Only create the editor if it is not already created.
        if (empty($this->editor)) {
            $editor = null;

            // Get the editor type attribute. Can be in the form of: editor="desired|alternative".
            $type = trim((string)$this->element['editor']);

            if ($type) {
                // Get the list of editor types.
                $types = explode('|', $type);

                // Get the database object.
                $db = JFactory::getDbo();

                // Iterate over teh types looking for an existing editor.
                foreach ($types as $element) {
                    // Build the query.
                    $query = $db->getQuery(true)
                        ->select('element')
                        ->from('#__extensions')
                        ->where('element = ' . $db->quote($element))
                        ->where('folder = ' . $db->quote('editors'))
                        ->where('enabled = 1');

                    // Check of the editor exists.
                    $db->setQuery($query, 0, 1);
                    $editor = $db->loadResult();

                    // If an editor was found stop looking.
                    if ($editor) {
                        break;
                    }
                }
            }

            // Create the JEditor instance based on the given editor.
            if (is_null($editor)) {
                $conf = JFactory::getConfig();
                $editor = $conf->get('editor');
            }
            $this->editor = JEditor::getInstance($editor);
        }

        return $this->editor;
    }

    //-----------------------------------------


    // Create textarea with type is textarea
    protected function getInputTextarea()
    {
        // Initialize some field attributes.
        $class = $this->element['class'] ? ' class="' . (string)$this->element['class'] . '"' : '';
        $disabled = ((string)$this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
        $columns = $this->element['cols'] ? ' cols="' . (int)$this->element['cols'] . '"' : '';
        $rows = $this->element['rows'] ? ' rows="' . (int)$this->element['rows'] . '"' : '';
        $required = $this->required ? ' required="required" aria-required="true"' : '';

        // Initialize JavaScript field attributes.
        $onchange = $this->element['onchange'] ? ' onchange="' . (string)$this->element['onchange'] . '"' : '';

        return '<textarea name="' . $this->name . '" id="' . $this->id . '"' . $columns . $rows . $class . $disabled . $onchange . $required . '>'
        . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';
    }

    //-------------------------------------------------------

    // Create dropdown or multiple select with type is list
    protected function getInputList()
    {
        $html = array();
        $attr = '';

        // Initialize some field attributes.
        $attr .= $this->element['class'] ? ' class="' . (string)$this->element['class'] . '"' : '';

        // To avoid user's confusion, readonly="true" should imply disabled="true".
        if ((string)$this->element['readonly'] == 'true' || (string)$this->element['disabled'] == 'true') {
            $attr .= ' disabled="disabled"';
        }

        $attr .= $this->element['size'] ? ' size="' . (int)$this->element['size'] . '"' : '';
        $attr .= $this->multiple ? ' multiple="multiple"' : '';
        $attr .= $this->required ? ' required="required" aria-required="true"' : '';

        // Initialize JavaScript field attributes.
        $attr .= $this->element['onchange'] ? ' onchange="' . (string)$this->element['onchange'] . '"' : '';

        // Get the field options.
        $options = (array)$this->getOptionsList();

        // Create a read-only list (no name) with a hidden input to store the value.
        if ((string)$this->element['readonly'] == 'true') {
            $html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
            $html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
        } // Create a regular list.
        else {
            $html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
        }

        return implode($html);
    }

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   11.1
     */
    protected function getOptionsList()
    {
        $options = array();

        foreach ($this->element->children() as $option) {

            // Only add <option /> elements.
            if ($option->getName() != 'option') {
                continue;
            }

            // Create a new option object based on the <option /> element.
            $tmp = JHtml::_(
                'select.option', (string)$option['value'],
                JText::alt(trim((string)$option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text',
                ((string)$option['disabled'] == 'true')
            );

            // Set some option attributes.
            $tmp->class = (string)$option['class'];

            // Set some JavaScript option attributes.
            $tmp->onclick = (string)$option['onclick'];

            // Add the option object to the result set.
            $options[] = $tmp;
        }

        reset($options);

        return $options;
    }

    //-------------------------------------------------------

    // Create radio field
    protected function getInputRadio()
    {
        $html = array();

        // Initialize some field attributes.
        $class = $this->element['class'] ? ' class="radio ' . (string)$this->element['class'] . '"' : ' class="radio"';

        // Start the radio field output.
        $html[] = '<fieldset id="' . $this->id . '"' . $class . '>';

        // Get the field options.
        $options = $this->getOptionsRadio();

        // Build the radio field output.
        foreach ($options as $i => $option) {

            // Initialize some option attributes.
            $checked = ((string)$option->value == (string)$this->value) ? ' checked="checked"' : '';
            $class = !empty($option->class) ? ' class="' . $option->class . '"' : '';
            $disabled = !empty($option->disable) ? ' disabled="disabled"' : '';
            $required = !empty($option->required) ? ' required="required" aria-required="true"' : '';

            // Initialize some JavaScript option attributes.
            $onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';

            $html[] = '<input type="radio" id="' . $this->id . $i . '" name="' . $this->name . '" value="'
                . htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $onclick . $disabled . $required . '/>';

            $html[] = '<label for="' . $this->id . $i . '"' . $class . '>'
                . JText::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)) . '</label>';
        }

        // End the radio field output.
        $html[] = '</fieldset>';

        return implode($html);
    }

    /**
     * Method to get the field options for radio buttons.
     *
     * @return  array  The field option objects.
     *
     * @since   11.1
     */
    protected function getOptionsRadio()
    {
        $options = array();

        foreach ($this->element->children() as $option) {

            // Only add <option /> elements.
            if ($option->getName() != 'option') {
                continue;
            }

            // Create a new option object based on the <option /> element.
            $tmp = JHtml::_(
                'select.option', (string)$option['value'], trim((string)$option), 'value', 'text',
                ((string)$option['disabled'] == 'true')
            );

            // Set some option attributes.
            $tmp->class = (string)$option['class'];

            // Set some JavaScript option attributes.
            $tmp->onclick = (string)$option['onclick'];

            // Add the option object to the result set.
            $options[] = $tmp;
        }

        reset($options);

        return $options;
    }
    //--------------------------------------------------------
}
