<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
?>
<form action="<?php echo CRoute::getURI();?>" method="post" name="login" id="form-login" >
	<ul class="cAjax-Login cFormList cFormFull cFormVertical cResetList">
		<li>
			<label class="form-label">
				<span class="small cFloat-R">
					<a href="<?php echo CRoute::_( 'index.php?option='.COM_USER_NAME.'&view=remind' ); ?>" tabindex="5">
						<?php echo JText::_('COM_COMMUNITY_FORGOT'); ?>
					</a>
				</span>
				<?php echo JText::_('COM_COMMUNITY_USERNAME'); ?>
			</label>
			<div class="form-field"><input type="text" class="input text frontlogin" name="username" id="username" tabindex="1" /></div>
		</li>
		<li>
			<label class="form-label">
				<span class="small cFloat-R">
					<a href="<?php echo CRoute::_( 'index.php?option='.COM_USER_NAME.'&view=reset' ); ?>" tabindex="6">
						<?php echo JText::_('COM_COMMUNITY_FORGOT'); ?>
					</a>
				</span>
				<?php echo JText::_('COM_COMMUNITY_PASSWORD'); ?>
			</label>
			<div class="form-field">
				<input type="password" class="input password frontlogin" name="<?php echo COM_USER_PASSWORD_INPUT;?>" id="password"  tabindex="2" />
			</div>
		</li>
		<li>
			<div class="form-field">
				<input type="submit" value="<?php echo JText::_('COM_COMMUNITY_LOGIN_BUTTON');?>" name="submit" id="submit" class="btn btn-primary cFloat-R"  tabindex="4" />
				<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
				<label for="remember" class="label-checkbox">
					<input type="checkbox" alt="<?php echo JText::_('COM_COMMUNITY_REMEMBER_MY_DETAILS'); ?>" value="yes" id="remember" name="remember"  tabindex="3" />
					<?php echo JText::_('COM_COMMUNITY_REMEMBER_MY_DETAILS'); ?>
				</label>
				<?php endif; ?>
			</div>
		</li>
		<li class="register-activation has-seperator" style="text-align:center">
			<?php if ($useractivation) { ?>
			<a href="<?php echo CRoute::_( 'index.php?option=com_community&view=register&task=activation' ); ?>" class="login-forgot-username">
				<?php echo JText::_('COM_COMMUNITY_RESEND_ACTIVATION_CODE'); ?>
			</a>
			<?php } ?>
			<?php if ($allowUserRegister) : ?>
			- <a  href="<?php echo CRoute::_( 'index.php?option=com_community&view=register' , false ); ?>">
				<?php echo JText::_('COM_COMMUNITY_REGISTER_NOW_TO_GET_CONNECTED'); ?>
			</a>
	<?php endif; ?>
		</li>
	</ul>

	<input type="hidden" name="option" value="<?php echo COM_USER_NAME;?>" />
	<input type="hidden" name="task" value="<?php echo COM_USER_TAKS_LOGIN;?>" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

<?php echo $fbHtml;?>