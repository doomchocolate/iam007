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

if(!class_exists('plgCommunityMyContacts'))
{
	class plgCommunityMyContacts extends CApplications
	{
		var $name         = "My Contacts";
		var $_name        = 'mycontacts';
		var $_path        = '';
		var $_user        = '';
		var $_my        = '';

		function onProfileDisplay()
		{
			$this->loadUserParams();

			//get enable
			$enable = new stdClass();

			$enable->personalInfo = new stdClass();
			$enable->personalInfo->home_address   = $this->params->get('home_address', TRUE);
			$enable->personalInfo->city           = $this->params->get('city', TRUE);
			$enable->personalInfo->postal_code    = $this->params->get('postal_code', TRUE);
			$enable->personalInfo->country        = $this->params->get('country', TRUE);
			$enable->personalInfo->phone_number   = $this->params->get('phone_number', TRUE);
			$enable->personalInfo->mobile_number  = $this->params->get('mobile_number', TRUE);
			$enable->personalInfo->fax_number     = $this->params->get('fax_number', TRUE);

			$enable->workInfo = new stdClass();
			$enable->workInfo->my_company   = $this->params->get('my_company', TRUE);
			$enable->workInfo->work_address = $this->params->get('work_address', TRUE);
			$enable->workInfo->website      = $this->params->get('website', TRUE);
			$enable->workInfo->department   = $this->params->get('department', TRUE);
			$enable->workInfo->job_title    = $this->params->get('job_title', TRUE);
			$enable->workInfo->main_im_id   = $this->params->get('main_im_id', TRUE);

			$enable->im_list = $this->params->get('im_list', TRUE);

			//get info
			$info = new stdClass();

			$info->personal = new stdClass();
			$info->personal->home_address   = $this->userparams->get('home_address', '');
			$info->personal->city           = $this->userparams->get('city', '');
			$info->personal->postal_code    = $this->userparams->get('postal_code', '');
			$info->personal->country        = $this->userparams->get('country', '');
			$info->personal->phone_number   = $this->userparams->get('phone_number', '');
			$info->personal->mobile_number  = $this->userparams->get('mobile_number', '');
			$info->personal->fax_number     = $this->userparams->get('fax_number', '');

			$info->work = new stdClass();
			$info->work->my_company     = $this->userparams->get('my_company', '');
			$info->work->work_address   = $this->userparams->get('work_address', '');
			$info->work->website        = $this->userparams->get('website', '');
			$info->work->department     = $this->userparams->get('department', '');
			$info->work->job_title      = $this->userparams->get('job_title', '');
			$info->work->main_im_id     = $this->userparams->get('main_im_id', '');

			$info->im = new stdClass();
			$info->im->icq      = $this->userparams->get('icq', '');
			$info->im->aim      = $this->userparams->get('aim', '');
			$info->im->yim      = $this->userparams->get('yim', '');
			$info->im->msn      = $this->userparams->get('msn', '');
			$info->im->google   = $this->userparams->get('google', '');
			$info->im->skype    = $this->userparams->get('skype', '');

			$mainframe	= JFactory::getApplication();
			$document	= JFactory::getDocument();

			$document->addStylesheet( rtrim( JURI::root(), '/' ) . '/plugins/community/mycontacts/mycontacts/style.css' );

			$caching = $this->params->get('cache', 1);

			if($caching){
				$caching = $mainframe->getCfg('caching');
			}

			$cache = JFactory::getCache('plgCommunityMyContacts');
			$cache->setCaching($caching);
			$callback = array('plgCommunityMyContacts', '_getMyContactsHTML');

			//Moving this out of _getMyContactsHTML because it's causing error in Joomla 1.6
			JPlugin::loadLanguage('plg_community_mycontacts', JPATH_ADMINISTRATOR);

			return $cache->call($callback, $enable, $info , $this->params);
		}

		static public function _getMyContactsHTML($enable, $info , $params )
		{
			//JPlugin::loadLanguage('plg_community_mycontacts', JPATH_ADMINISTRATOR);

			ob_start();
			?>
			<div id="application-mycontact">
				<div class="contact-basic clearfix">
					<div class="l-side">
						<?php
						foreach($enable->personalInfo as $key=>$value)
						{
							if($value)
							{
								if( !$params->get( 'hide_empty') || $params->get( 'hide_empty') && (!empty($info->personal->$key) ) )
								{
						?>
								<p id="<?php echo $key; ?>">
									<div class="contact_key"><?php echo JText::_( strtoupper('mycontact_'.$key)); ?></div>
									<b class="contact_value"><?php echo (!empty($info->personal->$key))? $info->personal->$key : JText::_('mycontacts_notavailable'); ?></b>
								</p>
						<?php
								}
							}
						}
						?>
					</div>
					<div class="r-side">
						<?php
						foreach($enable->workInfo as $key=>$value)
						{
							if($value)
							{
								if( !$params->get( 'hide_empty') || $params->get( 'hide_empty') && (!empty($info->work->$key) ) )
								{
						?>
								<p id="<?php echo $key; ?>" class="block">
									<div class="contact_key"><?php echo JText::_('mycontact_'.$key); ?></div>
									<b class="contact_value"><?php echo (!empty($info->work->$key))? $info->work->$key : JText::_('mycontacts_notavailable'); ?></b>
								</p>
						<?php
								}
							}
						}
						?>
					</div>
				</div>


				<div class="contact-screen clearfix">
					<?php
					if($enable->im_list)
					{
						foreach($info->im as $key=>$value)
						{
							if( !$params->get( 'hide_empty') || $params->get( 'hide_empty') && (!empty($value) ) )
							{
					?>
							<div id="<?php echo $key; ?>" class="icons">
								<div class="icon icon_<?php echo $key; ?>"><?php echo JText::_('mycontacts_'.$key); ?></div>
								<b class="im_info"><?php echo (!empty($value))? $value : JText::_('mycontacts_notavailable'); ?></b>
							</div>
					<?php
							}
						}
					}
					?>
				</div>
			</div>
			<?php
			$contents    = ob_get_contents();
			ob_end_clean();
			return $contents;
		}
	}
}