<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * marker_class: Class based on the selection of text, none, or icons
 * jicon-text, jicon-none, jicon-icon
 */

?>
<?php if ((($this->params->get('address_check') > 0) &&
		($this->contact->address || $this->contact->suburb  ||
            $this->contact->state || $this->contact->country ||
            $this->contact->postcode))
    ||($this->contact->email_to && $this->params->get('show_email'))
    || ($this->contact->telephone && $this->params->get('show_telephone'))
    ||($this->contact->fax && $this->params->get('show_fax'))
    ||($this->contact->mobile && $this->params->get('show_mobile'))
    ||($this->contact->webpage && $this->params->get('show_webpage'))) : ?>
<ul class="contact-address dl-horizontal">
	<?php if (($this->params->get('address_check') > 0) &&
		($this->contact->address || $this->contact->suburb  || $this->contact->state || $this->contact->country || $this->contact->postcode)) : ?>

    <li>
        <?php if ($this->params->get('address_check') > 0) : ?>
        <span class="<?php echo $this->params->get('marker_class'); echo $this -> params -> get('marker_address_class')?>" >
            <?php echo $this->params->get('marker_address'); ?>
        </span>
        <?php endif; ?>

        <?php if(($this->contact->address || $this->contact->suburb  || $this->contact->state
            || $this->contact->country || $this->contact->postcode)):?>
        <span class="right">
            <?php if ($this->contact->address && $this->params->get('show_street_address')) : ?>
            <span class="contact-street">
                <?php echo $this->contact->address .'<br/>'; ?>
            </span>
            <?php endif; ?>

            <?php if ($this->contact->suburb && $this->params->get('show_suburb')) : ?>
            <span class="contact-suburb">
                <?php echo $this->contact->suburb .'<br/>'; ?>
            </span>
            <?php endif; ?>
            <?php if ($this->contact->state && $this->params->get('show_state')) : ?>
            <span class="contact-state">
                <?php echo $this->contact->state . '<br/>'; ?>
            </span>
            <?php endif; ?>
            <?php if ($this->contact->postcode && $this->params->get('show_postcode')) : ?>
            <span class="contact-postcode">
                <?php echo $this->contact->postcode .'<br/>'; ?>
            </span>
            <?php endif; ?>
            <?php if ($this->contact->country && $this->params->get('show_country')) : ?>
            <span class="contact-country">
                <?php echo $this->contact->country .'<br/>'; ?>
            </span>
            <?php endif; ?>
        </span>
        <?php endif; ?>
    </li>
	<?php endif; ?>

    <?php if (($this->contact->telephone && $this->params->get('show_telephone')) OR
        ($this->contact->fax && $this->params->get('show_fax')) OR
        ($this->contact->mobile && $this->params->get('show_mobile'))) : ?>
        <li>
        <?php if ($this->contact->telephone && $this->params->get('show_telephone')) : ?>
            <span class="<?php echo $this->params->get('marker_class'); echo $this -> params -> get('marker_telephone_class'); ?>" >
                <?php echo $this->params->get('marker_telephone'); ?>
            </span>
            <span class="contact-telephone">
                <?php echo nl2br($this->contact->telephone); ?>
            </span>
            <div class="clearfix"></div>
        <?php endif; ?>
        <?php if ($this->contact->fax && $this->params->get('show_fax')) : ?>
            <span class="<?php echo $this->params->get('marker_class'); echo $this -> params -> get('marker_fax_class');?>" >
                <?php echo $this->params->get('marker_fax'); ?>
            </span>
            <span class="contact-fax">
            <?php echo nl2br($this->contact->fax); ?>
            </span>
            <div class="clearfix"></div>
        <?php endif; ?>

        <?php if ($this->contact->mobile && $this->params->get('show_mobile')) :?>
            <span class="<?php echo $this->params->get('marker_class'); echo $this -> params -> get('marker_mobile_class'); ?>" >
                <?php echo $this->params->get('marker_mobile'); ?>
            </span>
            <span class="contact-mobile">
                <?php echo nl2br($this->contact->mobile); ?>
            </span>
        <?php endif; ?>
        </li>
    <?php endif; ?>

    <?php if ($this->contact->email_to && $this->params->get('show_email')) : ?>
        <li>
		<span class="<?php echo $this->params->get('marker_class'); echo $this ->params -> get('marker_email_class') ?>" >
			<?php echo nl2br($this->params->get('marker_email')); ?>
		</span>
		<span class="contact-emailto">
			<?php echo $this->contact->email_to; ?>
		</span>
        </li>
    <?php endif; ?>

    <?php if ($this->contact->webpage && $this->params->get('show_webpage')) : ?>
    <li>
        <span class="<?php echo $this->params->get('marker_class'); echo $this -> params -> get('marker_webpage_class'); ?>" >
        </span>
        <span class="contact-webpage">
            <a href="<?php echo $this->contact->webpage; ?>" target="_blank">
            <?php echo JStringPunycode::urlToUTF8($this->contact->webpage); ?></a>
        </span>
    </li>
    <?php endif; ?>
</ul>
<?php endif;?>