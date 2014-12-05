<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');

class CTemplateHelper {

    public function getTemplateName() {
        $config = CFactory::getConfig();
        return $config->get('template');

        // Until the day when we allow
        // JomSocial template overriding via url.
        // return JRequest::getVar('jstmpl', $config->get('template'), 'GET');
    }

    public function getTemplatePath($file, $templateName = '', $type = 'path') {
        if (empty($templateName)) {
            $templateName = $this->getTemplateName();
        }

        switch ($type) {
            case 'path' :
                $path = COMMUNITY_TEMPLATE_PATH . '/' . $templateName . '/' . (($file) ? $file : '');
                break;
            case 'url' :
                $path = COMMUNITY_TEMPLATE_URL . '/' . $templateName . '/' . (($file) ? $file : '');
                break;
        }

        return $path;
    }

    public function getOverrideTemplatePath($file, $templateName = '', $type = 'path') {
        $mainframe = JFactory::getApplication();

        if (empty($templateName)) {
            $templateName = $mainframe->getTemplate();
        }

        switch ($type) {
            case 'path' :
                $path = JPATH_ROOT . '/templates/' . $templateName . '/html/com_community/' . (($file) ? $file : '');
                break;
            case 'url' :
                $path = rtrim(JURI::root(), '/') . '/templates/' . $templateName . '/html/com_community/' . (($file) ? $file : '');
                break;
        }

        return $path;
    }

    public function getAssetPath($file, $type = 'path') {
        $file = basename($file);

        switch ($type) {
            case 'path' :
                $path = COMMUNITY_COM_PATH . '/assets/' . (($file) ? $file : '');
                break;
            case 'url' :
                $path = rtrim(JURI::root(), '/') . '/components/com_community/assets/' . (($file) ? $file : '');
                break;
        }

        return $path;
    }

    public function hasTemplateOverride($file) {
        $result = false;

        if (empty($file)) {
            $result = JFolder::exists($this->getOverrideTemplatePath());
        } else {
            $result = JFile::exists($this->getOverrideTemplatePath($file));
        }

        return $result;
    }

    public function getSources($file) {
        $sources = array(
            'override' => $this->getOverrideTemplatePath($file),
            'template' => $this->getTemplatePath($file),
            'default' => $this->getTemplatePath($file, 'default'),
            'asset' => $this->getAssetPath($file),
        );

        return $sources;
    }

    public function getFile($file) {
        $sources = $this->getSources($file);

        foreach ($sources as $source => $file) {
            if (JFile::exists($file))
                break;
        }

        return $file;
    }

    public function getUrl($file) {
        $url = str_replace('\\', '/', $file);
        $sources = $this->getSources($file);

        foreach ($sources as $source => $file) {
            if (JFile::exists($file)) {
                switch ($source) {
                    case 'override':
                        $url = $this->getOverrideTemplatePath($url, '', 'url');
                        break;
                    case 'template':
                        $url = $this->getTemplatePath($url, '', 'url');
                        break;
                    case 'default':
                        $url = $this->getTemplatePath($url, 'default', 'url');
                        break;
                    case 'asset':
                        $url = $this->getAssetPath($url, 'url');
                        break;
                }

                break;
            }
        }

        return $url;
    }

    public function getFolder() {
        return $this->getFile();
    }

    public function getTemplateFile($file) {
        if (!JString::strpos($file, '.php')) {
            $file = $file . '.php';
        }

        return $this->getFile($file);
    }

    public function getMobileTemplateFile($file) {
        $mobileFile = $this->getTemplateFile($file . '.mobile');

        if (!JFile::exists($mobileFile)) {
            $mobileFile = $this->getTemplateFile($file);
        }

        return $mobileFile;
    }

    /**
     * 
     * @param type $file
     * @param type $assetType
     * @return \stdClass
     */
    public function getTemplateAsset($file, $assetType = '') {
        $config = CFactory::getConfig();

        switch ($assetType) {
            case 'js':
                $file = 'js/' . $file . '.min.js';
                break;

            case 'css':
                $file = 'css/' . $file . '.css';
                break;

            case 'images':
                $file = 'images/' . $file;
                break;

            default:
                break;
        }

        $asset = new stdClass();
        $asset->file = $this->getFile($file);
        $asset->url = $this->getUrl($file);
        $asset->path = dirname($asset->url) . '/';
        $asset->filename = basename($asset->url);

        return $asset;
    }

}