<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=taxation" method="post"  name="adminForm" id="adminForm">
	<table>
		<tr>
			<td width="100%">
			</td>
			<td nowrap="nowrap">
				<?php echo $this->taxType->display("taxation_type",$this->pageInfo->filter->taxation_type,false);?>
				<?php echo $this->ratesType->display("tax_namekey",$this->pageInfo->filter->tax_namekey,false);?>
			</td>
		</tr>
	</table>
	<table id="hikashop_taxation_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('ZONE'), 'd.zone_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('TAXATION_CATEGORY'), 'c.category_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('RATE'), 'b.tax_rate', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',    JText::_( 'CUSTOMER_TYPE' ), 'a.taxation_type',$this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JHTML::_('grid.sort',   JText::_('HIKA_PUBLISHED'), 'a.taxation_published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.taxation_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="8">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$k = 0;
				$config =& hikashop_config();
				for($i = 0,$a = count($this->rows);$i<$a;$i++){
					$row =& $this->rows[$i];
					$publishedid = 'taxation_published-'.$row->taxation_id;
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center">
					<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td align="center">
						<?php echo JHTML::_('grid.id', $i, $row->taxation_id ); ?>
					</td>
					<td>
						<?php if(hikashop_isAllowed($config->get('acl_zone_manage','all'))){ ?>
							<a href="<?php echo hikashop_completeLink('zone&task=edit&zone_id='.@$row->zone_id); ?>">
						<?php } ?>
								<?php echo @$row->zone_name; ?>
						<?php if(hikashop_isAllowed($config->get('acl_zone_manage','all'))){ ?>
							</a>
						<?php } ?>
					</td>
					<td>
						<?php if(hikashop_isAllowed($config->get('acl_category_manage','all'))){ ?>
							<a href="<?php echo hikashop_completeLink('category&task=edit&category_id='.@$row->category_id); ?>">
						<?php } ?>
								<?php echo @$row->category_name; ?>
						<?php if(hikashop_isAllowed($config->get('acl_category_manage','all'))){ ?>
							</a>
						<?php } ?>
					</td>
					<td>
						<?php if(!empty($row->tax_namekey)){?>
							<?php if($this->manage){ ?>
								<a href="<?php echo hikashop_completeLink('tax&task=edit&return=taxation&tax_namekey='.@$row->tax_namekey); ?>">
							<?php } ?>
									<?php echo $row->tax_namekey.' ('.(@$row->tax_rate*100).'%)'; ?>
							<?php if($this->manage){ ?>
								</a>
							<?php } ?>
						<?php }else{
							echo '0%';
						}?>
					</td>
					<td>
						<?php echo JText::_(strtoupper($row->taxation_type)); ?>
					</td>
					<td align="center">
						<?php if($this->manage){ ?>
							<span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->taxation_published,'taxation') ?></span>
						<?php }else{ echo $this->toggleClass->display('activate',$row->taxation_published); } ?>
					</td>
					<td width="1%" align="center">
						<?php echo $row->taxation_id; ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
