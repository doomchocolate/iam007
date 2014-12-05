<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
Class CCarousel{
    function cShowCarousel($id, $total, $jaxCall){
	static $carouselCustomTag = null;

	if(!$carouselCustomTag)
	{
		CAssets::attach('templates/default/carousel.css', 'css');
		CAssets::attach('assets/carousel-1.0.js', 'js');

		$carouselCustomTag = true;
	}

	ob_start();
        ?>
        <div class="carousel-container" id="<?php echo $id;?>">
                <a class="carousel-prev" href="javascript:void(0)" onclick="this.blur();cCarouselPrev('<?php echo $id;?>', '<?php echo $jaxCall;?>');joms.jQuery(this).trigger('onblur');">« Prev</a>
                <a class="carousel-next" href="javascript:void(0)" onclick="this.blur();cCarouselNext('<?php echo $id;?>', '<?php echo $jaxCall;?>');joms.jQuery(this).trigger('onblur');">Next »</a>
                <div class="carousel-content">
                        <div class="carousel-content-wrap" style="display: block;">
                                <div class="carousel-content-clip">
                                        <ul class="carousel-list" style="width: 1600px; left: 0pt;margin:0px">
                                                <?php for($i=0; $i<$total; $i++) { ?>
                                                <li class="carousel-item" id="<?php echo $id;?>-item-<?php echo $i; ?>"><div class="ajax-wait">&nbsp;</div></li>
                                                <?php } ?>
                                        </ul>
                                </div>
                        </div>
                </div>
        </div>
        <script type='text/javascript'>
        cCarouselInit('<?php echo $id; ?>', '<?php echo $jaxCall; ?>');
        </script>
                <?php
                $content	= ob_get_contents();
                ob_end_clean();
                return $content;
        }

}


?>
