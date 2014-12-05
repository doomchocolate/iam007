<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
JHtml::_('bootstrap.framework');
require_once JPATH_COMPONENT_ADMINISTRATOR . '/libraries/troubleshoots.php';
$troubleshoots = new CTroubleshoots();
$phpChecks[] = array(
    'required' => '5.3 or higher',
    'current' => array(version_compare(PHP_VERSION, '5.3') >= 0, phpversion()),
    'description' => 'PHP should running on Apache / nix system'
);
$phpChecks[] = array(
    'required' => 'imagecreatefromjpeg',
    'current' => array(function_exists('imagecreatefromjpeg')),
    'description' => 'Supported image function'
);
$phpChecks[] = array(
    'required' => 'imagecreatefrompng',
    'current' => array(function_exists('imagecreatefrompng')),
    'description' => 'Supported image function'
);
$phpChecks[] = array(
    'required' => 'imagecreatefromgif',
    'current' => array(function_exists('imagecreatefromgif')),
    'description' => 'Supported image function'
);
$phpChecks[] = array(
    'required' => 'imagecreatefromgd',
    'current' => array(function_exists('imagecreatefromgd')),
    'description' => 'Supported image function'
);
$phpChecks[] = array(
    'required' => 'imagecreatefromgd2',
    'current' => array(function_exists('imagecreatefromgd2')),
    'description' => 'Supported image function'
);
$phpChecks[] = array(
    'required' => 'curl',
    'current' => array(in_array('curl', get_loaded_extensions())),
    'description' => ''
);
$phpChecks[] = array(
    'required' => 'max_execution_time: 1200',
    'current' => array(ini_get('max_execution_time') > 1200, ini_get('max_execution_time')),
    'description' => 'Check your php.ini'
);
$phpChecks[] = array(
    'required' => 'max_input_time: 1200',
    'current' => array(ini_get('max_input_time') > 1200, ini_get('max_input_time')),
    'description' => 'Check your php.ini'
);
$phpChecks[] = array(
    'required' => 'memory_limit: 512M',
    'current' => array(ini_get('memory_limit') > 512, ini_get('memory_limit')),
    'description' => 'Check your php.ini'
);
$phpChecks[] = array(
    'required' => 'post_max_size: 512M',
    'current' => array(ini_get('post_max_size') > 512, ini_get('post_max_size')),
    'description' => 'Check your php.ini'
);
$phpChecks[] = array(
    'required' => 'upload_max_filesize: 20M',
    'current' => array(ini_get('upload_max_filesize') > 20, ini_get('upload_max_filesize')),
    'description' => 'Check your php.ini'
);
$communityPlugins['community'] = array(
    'allvideo',
    'editormyphotos',
    'events',
    'feeds',
    'friendslocation',
    'groups',
    'icontact',
    'input',
    'inputlink',
    'invite',
    'jsnote',
    'kunena',
    'kunenagroups',
    'kunenamenu',
    'latestphoto',
    'log',
    'myarticles',
    'myblog',
    'myblogtoolbar',
    'mycontacts',
    'mygoogleads',
    'mykunena',
    'mytaggedvideos',
    'myvideos',
    'nicetalk',
    'system',
    'twitter',
    'walls',
    'wordfilter'
);
$communityPlugins['content'] = array(
    'groupdiscuss',
    'jomsocial_fb_comments',
    'jomsocial_fb_likes'
);
$communityPlugins['editors-xtd'] = array(
    'myphotos'
);
$communityPlugins['kunena'] = array(
    'community'
);
$communityPlugins['system'] = array(
    'azrul.system',
    'jomsocial',
    'jomsocialconnect',
    'jomsocialinprofile',
    'jomsocialredirect',
    'jomsocialupdate'
);
$communityPlugins['user'] = array(
    'jomsocialuser',
    'registeractivity'
);
$plugins = $troubleshoots->getCommunityPlugins();
?>
<ul id="myTab" class="nav nav-tabs">
    <li class="active"><a href="#home" data-toggle="tab">Overview</a></li>
    <li class=""><a href="#jQuery" data-toggle="tab">jQuery</a></li>
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Files checking <b class="caret"></b></a>
        <ul class="dropdown-menu">
            <li><a href="#files-backend" data-toggle="tab">@Backend</a></li>
            <li><a href="#files-frontend" data-toggle="tab">@Frontend</a></li>
        </ul>
    </li>
    <li class=""><a href="#plugins" data-toggle="tab">Plugins</a></li>
    <li class=""><a href="#requirements" data-toggle="tab">System requirements</a></li>
</ul>
<div id="myTabContent" class="tab-content">
    <div class="tab-pane fade active in" id="home">
        <p>Raw denim you probably haven't heard of them jean shorts Austin. Nesciunt tofu stumptown aliqua, retro synth master cleanse. Mustache cliche tempor, williamsburg carles vegan helvetica. Reprehenderit butcher retro keffiyeh dreamcatcher synth. Cosby sweater eu banh mi, qui irure terry richardson ex squid. Aliquip placeat salvia cillum iphone. Seitan aliquip quis cardigan american apparel, butcher voluptate nisi qui.</p>
    </div>
    <div class="tab-pane fade" id="jQuery">
        <?php
        $troubleshoots->filesCheck(JPATH_ROOT);
        ?>
    </div>
    <div class="tab-pane fade" id="files-backend">
        <?php
        $troubleshoots->coreFilesCheck(JPATH_ADMINISTRATOR . '/components/com_community');
        ?>
    </div>
    <div class="tab-pane fade" id="files-frontend">
        <?php
        $troubleshoots->coreFilesCheck(JPATH_ROOT . '/components/com_community');
        ?>
    </div>
    <div class="tab-pane fade" id="plugins">
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Ordering</th>
                    <th>Folder</th>
                    <th>Name</th>
                    <th>Element</th>
                    <th>Enabled</th>                  
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plugins as $plugin) { ?>
                    <tr class="<?php echo (in_array($plugin->element, $communityPlugins[$plugin->folder])) ? '' : 'error'; ?>">
                        <td>
                            <?php echo $plugin->extension_id; ?>
                        </td>
                        <td>
                            <?php echo $plugin->ordering; ?>
                        </td>
                        <td>
                            <?php echo $plugin->folder; ?>
                        </td>
                        <td>
                            <?php echo $plugin->name; ?>
                        </td>
                        <td>
                            <?php echo $plugin->element; ?>
                        </td>
                        <td>
                            <?php echo $plugin->enabled; ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <div class="tab-pane fade" id="requirements">
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th></th>
                    <th>Required</th>
                    <th>Current</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Web server</td>
                    <td>Apache</td>
                    <td>Apache/2.2.x or higher</td>
                    <td>PHP should running on Apache / nix system</td>
                </tr>
                <!--php-->
                <?php foreach ($phpChecks as $phpCheck) {
                    ?>
                    <tr class="<?php echo ($phpCheck['current'][0]) ? '' : 'error'; ?>">
                        <td>PHP</td>
                        <?php foreach ($phpCheck as $key => $php) { ?>
                            <?php if ($key == 'current') { ?>
                                <?php if (count($php) == 2) { ?>
                                    <td><?php echo $php[1]; ?> <i class="<?php echo ($php[0]) ? "icon-ok" : "icon-remove"; ?>"></i></td>
                                <?php } else { ?>
                                    <td><i class="<?php echo ($php[0]) ? "icon-ok" : "icon-remove"; ?>"></i></td>
                                <?php } ?>
                            <?php } else { ?>
                                <td><?php echo $php; ?></td>
                            <?php } ?>
                        <?php } ?>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

