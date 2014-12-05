<?php
/**
 * XEF - Expert Extension Framework
 *
 * An extension development framework for ThemeXpert
 *
 * @package		XEF - Expert Extension Framework
 * @author		ThemeXpert Team
 * @copyright	Copyright (c) 2010 - 2012, ThemeXpert.
 * @license		GNU General Public License version 3, or later
 * @link		http://www.themexpert.com
 * @since		1.0
 *
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Expert Extension Framework (XEF) helper class
 *
 * Expert Extension Framework (XEF) is a set of classes which extends Joomla! 2.5 and later's
 * MVC framework with features making maintaining all ThemeXpert's extensions much easier.
 *
 * Initially designed for module development and more coming...
 */

class XEFHelper
{
    public $params;        // Hold the extension params. we'll not allow to access it from outside.
    public $module;        // Module object hold the module information

    /*
     * Constructor
     * @params object $params get the module params object
     *
     * @return NULL
     *
     **/
    public function __construct( $module, $params )
    {
        $this->module       = $module;
        $this->params       = $params;
    }

    /*
     * Get the value of given param or set to default
     *
     * @params string $param    name of the field
     *
     * @default string $default set default value if no value found on param
     *
     * @return string/int $value    Value return for given field
     *
     **/
    public function get( $param , $default=NULL )
    {
        $value = ( $this->params->get($param) != NULL ) ? $this->params->get($param) : $default;

        return $value;
    }

    /*
     * Set the value to given param
     *
     * @params string $field    name of the field
     *
     * @value string/int $value  set value to the field
     *
     **/
    public function set( $field, $value )
    {
        $this->params->set( $field, $value );
    }

    /*
     * Prepare items before going to view.
     *
     * @params object $items Items object
     *
     * @return Object $items modified items object
     *
     **/
    public function prepareItems($items)
    {
        //$source = $this->get('content_source');

        foreach ($items as $item)
        {
            // Clean title
            $item->title = JFilterOutput::ampReplace($item->title);

            // Category name & link
            $item->catname = $this->getCategory($item);
            $item->catlink = $this->getCategoryLink($item);

            // Link
            $item->link = $this->getLink($item);

            // Image
            $item->image = $this->getImage($item);

            // Date
            $item->date = $this->getDate($item);

            // Set image dimension
            $dimensions = array(
                'width'  => $this->get('image_width',400),
                'height' => $this->get('image_height',300)
            );

            // If thumbnail is enable set its property
            if( $this->get('navigation') == 'thumb' OR
                $this->get('thumb') )
            {

                $thumb_dimensions = array(
                    'width'  => $this->get('thumb_width',100),
                    'height' => $this->get('thumb_height',100)
                );
                $item->thumb = XEFUtility::getResizedImage($item->image, $thumb_dimensions, $this->module, '_thumb');
            }

            // Finally re-sized image if image re-sizer is on
            if($this->get('image_resize'))
            {
                $item->image = XEFUtility::getResizedImage($item->image, $dimensions, $this->module);
            }

            // Intro text
            $filter_by = $this->get('intro_limit_type');

            // Trim intro text based on filter type
            if( $filter_by == 'words' )
            {
                $item->introtext = XEFUtility::wordLimit($item->introtext, $this->get('intro_limit',100) );

            }elseif($filter_by == 'chars')
            {
                $item->introtext = XEFUtility::characterLimit($item->introtext, $this->get('intro_limit',100) );
            }
        }

        return $items;
    }
}