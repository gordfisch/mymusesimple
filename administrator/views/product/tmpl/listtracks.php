<?php
/**
 * @version     $$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */


// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

$app		= JFactory::getApplication();
$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$saveOrder	= $listOrder == 'a.ordering';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_mymuse&task=products.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();
$assoc		= isset($app->item_associations) ? $app->item_associations : 0;

require_once JPATH_COMPONENT.'/helpers/mymuse.php';


$params 	= $this->params;
$lists 		= $this->lists;

//TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS

	
if(isset($lists['isNew'])){
echo JText::_("MYMUSE_SAVE_THEN_ADD_TRACKS");
}else{
//Ordering allowed ?
$ordering = ($listOrder == 'a.ordering');

$js = '
	/**
	* Submit the item form
	*/

	function submitform(pressbutton){
		
		if (pressbutton) {
			document.adminForm.task.value=pressbutton;
		}
		if (typeof document.adminForm.onsubmit == "function") {
			document.adminForm.onsubmit();
		}
		
		if(pressbutton == "product.addfile"
		|| pressbutton == "product.new_allfiles" ){
			document.adminForm.id.value = "";
		}
		
		document.adminForm.submit();
	}
	
	function submitbutton1(pressbutton)
	{
		submitform( pressbutton );
	}
	';

$document = JFactory::getDocument();
$document->addScriptDeclaration($js);
?>
 <script src="http://code.jquery.com/ui/1.11.2/jquery-ui.min.js" type="text/javascript"></script>
<script type="text/javascript">
Joomla.orderTable = function()
{
	table = document.getElementById("sortTable");
	direction = document.getElementById("directionTable");
	order = table.options[table.selectedIndex].value;
	if (order != '<?php echo $listOrder; ?>')
	{
		dirn = 'asc';
	}
	else
	{
		dirn = direction.options[direction.selectedIndex].value;
	}
	Joomla.tableOrdering(order, dirn, '');
}


</script>

<h2><?php echo JText::_( 'MYMUSE_TRACKS' ); ?></h2>




<div style="clear: both;"></div>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="view" value="product" /> 
	<input type="hidden" name="layout" value="listtracks" /> 
	<input type="hidden" name="option" value="com_mymuse" /> 
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" /> 
	<input type="hidden" name="task" value="" /> 
	<input type="hidden" name="subtype" value="file" /> 
	<input type="hidden" name="boxchecked" value="" /> 
	<input type="hidden" name="parentid" value="<?php echo $this->item->id; ?>" /> 
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('MYMUSE_FILTER_SEARCH_DESC'); ?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" 
				class="hasTooltip" title="<?php echo JHtml::tooltipText('MYMUSE_FILTER_SEARCH_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left hidden-phone">
				<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>

			<div class="btn-group pull-right hidden-phone">
				<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
					<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?></option>
					<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');  ?></option>
				</select>
			</div>
			<div class="btn-group pull-right">
				<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY'); ?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
				</select>
			</div>
		</div>
		<div class="clearfix"> </div>
		
		
	<div class="tracks">
		<div id="product_player" 
			<?php if($params->get('product_player_height')){ ?>
			style="height: <?php echo $params->get('product_player_height'); ?>px"
			<?php } ?>
			><?php if(isset($this->item->flash)){ echo $this->item->flash; }?>
		</div>
		<div id="playing_title">Now Playing</div>
		<div id="jp-title-li"></div>

	<?php 

	if(count($this->tracks) > 0){ 

		?>
		<table id="articleList" class="table table-striped">
			<thead>
				<tr>

					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
					<th width="1%">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th width="1%" style="min-width:55px" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th width="25%">
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
					</th>
					<th class="title"><?php echo JText::_('MYMUSE_PRICE');?>
					</th>
					<th class="title"><?php echo JText::_('MYMUSE_DISCOUNT');?>
					</th>
					<th class="title" colspan="2"><?php echo JText::_('MYMUSE_FILE_NAME');?>
					</th>
					<th class="title"><?php echo JText::_('MYMUSE_FILE_SIZE');?>
					</th>
					<th class="title" colspan="2"><?php echo JText::_('MYMUSE_PREVIEW_NAME');?>
					</th>
					<th class="title"><?php echo JText::_('MYMUSE_DOWNLOADS');?>
					</th>
					<th width="1%" class="title">ID
					</th>
					<th width="1%" class="title">Order
					</th>
				</tr>
			</thead>
			<tfoot>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			$config = JFactory::getConfig();
		    $tzoffset = $config->get('config.offset');
		    $now = JFactory::getDate();
			for ($i=0, $n=count( $this->tracks ); $i < $n; $i++)
			{
				$file = &$this->tracks[$i];
				$file->max_ordering = 0;
				if($file->product_allfiles == "1"){
					$link 	= 'index.php?option=com_mymuse&task=product.edit_allfiles&type=allfiles&id='. $file->id.'&parentid='.$file->parentid;
				}else{
					$link 	= 'index.php?option=com_mymuse&task=product.editfile&type=file&id='. $file->id.'&parentid='.$file->parentid;
				}

				$ordering   = ($listOrder == 'a.ordering');
				$canCreate	= $user->authorise('core.create',		'com_mymuse.category.'.$file->catid);
				$canEdit	= $user->authorise('core.edit',			'com_mymuse.product.'.$file->id);
				$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $file->checked_out == $userId || $file->checked_out == 0;
				$canEditOwn	= $user->authorise('core.edit.own',		'com_mymuse.product.'.$file->id) && $file->created_by == $userId;
				$canChange	= $user->authorise('core.edit.state',	'com_mymuse.product.'.$file->id) && $canCheckin;

				
				?>
				
				
				<tr class="row<?php echo $i % 2; ?>">
					<td class="order nowrap center hidden-phone">
					<?php if ($canChange) :
						$disableClassName = '';
						$disabledLabel	  = '';

						if (!$saveOrder) :
							$disabledLabel    = JText::_('JORDERINGDISABLED');
							$disableClassName = 'inactive tip-top';
						endif; ?>
						<span class="sortable-handler hasTooltip <?php echo $disableClassName; ?>" title="<?php echo $disabledLabel; ?>">
							<i class="icon-menu"></i>
						</span>
						<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $file->ordering; ?>" class="width-20 text-area-order " />
					<?php else : ?>
						<span class="sortable-handler inactive" >
							<i class="icon-menu"></i>
						</span>
					<?php endif; ?>
					</td>
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $file->id); ?>
					</td>
					<td class="center">
						<div class="btn-group">
							<?php echo JHtml::_('jgrid.published', $file->state, $i, 'products.', $canChange, 'cb', $file->publish_up, $file->publish_down); ?>
							<?php echo JHtml::_('mymuseadministrator.featured', $file->featured, $i, $canChange); ?>
						</div>
					</td>
					
					<td>
					<a href="<?php echo $link ?>"><?php echo $this->escape($file->title); ?></a> 
					<?php  if($file->product_allfiles == "1"){ 
						echo JText::_("MYMUSE_ALL_TRACKS");
					 } ?>
					 
					</td>
					<td class="small hidden-phone">
						<?php echo $this->escape($file->access_level); ?>
					</td>
					<td><?php echo $file->price; ?></td>
					<td><?php echo $file->product_discount; ?></td>
					
					<td><div id="product_player"><?php echo $file->stream; ?></div></td>
					<td  align="center">
						<?php echo stripslashes($file->file_name); 
						echo $params->get('my_encode_filenames',0)?>
						<?php if($params->get('my_encode_filenames',0)){ ?>
						<p class="smallsub">
						<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($file->title_alias));?></p>
						<?php } ?>
					</td>
					<td  align="center">
						<?php echo MyMuseHelper::ByteSize($file->file_length); ?>
					</td>
					
					<td><div id="product_player"><?php echo $file->flash; ?></div></td>
					<td  align="center">
						<?php echo htmlspecialchars($file->file_preview, ENT_QUOTES); ?>
						
					</td>
					<td nowrap="nowrap" align="center">
						<?php echo $file->file_downloads; ?>
					</td>

					<td>
						<?php echo $file->id; ?>
					</td>
					<td>
						<?php echo $file->ordering; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
			</table>
		<?php   } ?>

	<?php echo JHTML::_( 'form.token' ); ?>
	</form>
	</div>

</div>
<?php } ?>