<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */


// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', '.multipleProducts', null, array('placeholder_text_multiple' => JText::_('MYMUSE_SELECT_PRODUCTS')));
JHtml::_('formbehavior.chosen', '.multipleArtists', null, array('placeholder_text_multiple' => JText::_('MYMUSE_FILTER_ARTIST')));
JHtml::_('formbehavior.chosen', '.multipleAccessLevels', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_ACCESS')));
JHtml::_('formbehavior.chosen', '.multipleAuthors', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_AUTHOR')));
JHtml::_('formbehavior.chosen', 'select');


JHtml::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_TAG')));
JHtml::_('formbehavior.chosen', '.multipleCategories', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_CATEGORY')));




$app		= JFactory::getApplication();
$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$saveOrder	= $listOrder == 'a.ordering';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_mymuse&task=tracks.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();
$assoc		= isset($app->item_associations) ? $app->item_associations : 0;

require_once JPATH_COMPONENT.'/helpers/mymuse.php';

?>


<form action="<?php echo JRoute::_('index.php?option=com_mymuse&view=tracks'); ?>" method="post" name="adminForm" id="adminForm">

<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; ?>
		<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>


		
	


	<table id="articleList" class="table table-striped">
		<thead>
			<tr>
				<th width="1%" class="nowrap center hidden-phone">
					<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
				</th>
				<th width="1%" class="center nowrap">
					<?php echo JHtml::_('grid.checkall'); ?>
				</th>
				<th width="1%" class="center nowrap">
					<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
				</th>
				<th width="25%">
					<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<th width="25%">
					<?php echo JHtml::_('searchtools.sort', 'MYMUSE_PRODUCT', 'p.title', $listDirn, $listOrder); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('searchtools.sort', 'MYMUSE_SKU', 'a.product_sku', $listDirn, $listOrder); ?>
				</th>
				
	
				<th width="10%">
					<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
				</th>

				<th width="5%">
					<?php echo JHtml::_('searchtools.sort', 'JDATE', 'a.created', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="15">
					<?php 

					echo $this->pagination->getListFooter(); 

					?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$canEdit   = $user->authorise('core.edit',       'com_mymuse');
			$canChange = $user->authorise('core.edit.state', 'com_mymuse');
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="order nowrap center hidden-phone">
							<?php
							$iconClass = '';
							if (!$canChange)
							{
								$iconClass = ' inactive';
							}
							elseif (!$saveOrder)
							{
								$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::_('tooltipText', 'JORDERINGDISABLED');
							}
							?>
							<span class="sortable-handler<?php echo $iconClass ?>">
								<span class="icon-menu" aria-hidden="true"></span>
							</span>
							<?php if ($canChange && $saveOrder) : ?>
								<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order" />
							<?php endif; ?>
						</td>
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td class="center">
					<div class="btn-group">
						<?php echo JHtml::_('track.published', $item->published, $i); ?>
						<?php echo JHtml::_('track.featured', $item->featured, $i, $canChange); ?>
						<?php // Create dropdown items and render the dropdown list.
						if ($canChange)
						{
							JHtml::_('actionsdropdown.' . ((int) $item->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'tracks');
							JHtml::_('actionsdropdown.' . ((int) $item->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'tracks');
							echo JHtml::_('actionsdropdown.render', $this->escape($item->title));
						}
						?>
					</div>
				</td>
				
				<td>
					<?php if ($item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'tracks.', $canEdit); ?>
					<?php endif; ?>
					<?php if ($canEdit && $item->allfiles) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_mymuse&task=track.edit_allfiles&id='.$item->id);?>">
							<?php echo $this->escape($item->title); ?></a>

					<?php elseif ($canEdit) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_mymuse&task=track.edit&id='.$item->id);?>">
							<?php echo $this->escape($item->title); ?></a>
					<?php else : ?>
						<?php echo $this->escape($item->title); ?>
					<?php endif; ?>
					<?php if ($item->allfiles) : 
						echo '<br />&nbsp;&nbsp;'.JText::_('MYMUSE_PRODUCT_ALLFILES_LABEL');
					else : 
						foreach($item->track as $track){
							echo '<br />&nbsp;&nbsp;'.$track->file_name;
						}
					endif; ?>
				</td>
				<td>
					<?php echo $item->product_title; ?>


				</td>
				<td>
					<?php echo $item->product_sku; ?>
				</td>

				<td>
					<?php echo $this->escape($item->access_level); ?>
				</td>
				<!--  
				<td class="center">
					<?php echo $this->escape($item->author_name); ?>
				</td>
				-->
				<td>
					<?php echo JHtml::_('date',$item->created, JText::_('DATE_FORMAT_LC4')); ?>
				</td>
				<td>
					<?php echo (int) $item->hits; ?>
				</td>
				<td>
					<?php if ($item->language=='*'):?>
						<?php echo JText::alt('JALL','language'); ?>
					<?php else:?>
						<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
					<?php endif;?>
				</td>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
