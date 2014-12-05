<?php


// Protect from unauthorized access
defined('_JEXEC') or die();


    if(defined('PHP_VERSION')) {
        $version = PHP_VERSION;
    } elseif(function_exists('phpversion')) {
        $version = phpversion();
    } else {
        // No version info. I'll lie and hope for the best.
        $version = '5.0.0';
    }

    // Old PHP version detected. EJECT! EJECT! EJECT!
    if(!version_compare($version, '5.2.7', '>='))
    {
        return;
    }

/*
 * ------------------------------------------------------
 *  Set the framework
 * ------------------------------------------------------
 */
    if( !defined('XEF_INCLUDED') )
    {
        define('XEF_INCLUDED', 1);
    }

/*
 * ------------------------------------------------------
 *  Check Joomla version and set the constant
 * ------------------------------------------------------
 */
    if( !defined('XEF_JVERSION') )
    {
        if ( version_compare(JVERSION, '2.5', 'ge') && version_compare(JVERSION, '3.0', 'lt') )
        {
            define('XEF_JVERSION', '25');
        }else{
            define('XEF_JVERSION', '30');
        }
    }
/*
 * ------------------------------------------------------
 *  Load the global utilities class
 * ------------------------------------------------------
 */


// ------------------------------------------------------
if ( ! function_exists('importSource'))
{
    /**
     * Import source file by given source name
     *
     * @params string $name name of the content soruce
     *
     * @return string $class_name Name of the class
     */
    function importSource($name)
    {

        $path = JPATH_LIBRARIES . '/tzm/sources/';

        // Class prefix utilize XEF framework
        $prefix = 'XEFSource';

        // Class name based on given source name
        $class_name = $prefix. ucfirst($name);

        if(!class_exists($class_name))
        {

            require_once ($path . strtolower($name) . '/' . $name . '.php');
        }

        return $class_name;
    }
}

// ------------------------------------------------------
if ( ! function_exists('curlExist'))
{
    function curlExist()
    {

    }
}

