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
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
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
					<th width="20%">
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?></th>
				<?php if(!$this->params->get('my_price_by_product')){ ?>
					<th class="title"><?php echo JText::_('MYMUSE_PRICE');?></th>
					<th class="title"><?php echo JText::_('MYMUSE_DISCOUNT');?></th>
				<?php } ?>
					
					<th class="title" colspan="2"><?php echo JText::_('MYMUSE_FILE_NAME');?>
					<th class="title"><?php echo JText::_('MYMUSE_DOWNLOADS');?></th></th>
					<th class="title"><?php echo JText::_('MYMUSE_FILE_SIZE');?></th>
					<th class="title" colspan="2"><?php echo JText::_('MYMUSE_PREVIEW_NAME');?></th>
					
					<th width="1%" class="title">ID</th>
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
				$track = &$this->tracks[$i];
				$files = json_decode($track->file_name);
				$track->max_ordering = 0;
				if($track->product_allfiles == "1"){
					$link 	= 'index.php?option=com_mymuse&task=product.edit_allfiles&type=allfiles&id='. $track->id.'&parentid='.$track->parentid;
				}else{
					$link 	= 'index.php?option=com_mymuse&task=product.editfile&type=file&id='. $track->id.'&parentid='.$track->parentid;
				}

				$ordering   = ($listOrder == 'a.ordering');
				$canCreate	= $user->authorise('core.create',		'com_joomlamymuse.comtegory.'.$track->catid);
				$canEdit	= $user->authorise('core.edit',			'com_mymuse.product.'.$track->id);
				$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $track->checked_out == $userId || $track->checked_out == 0;
				$canEditOwn	= $user->authorise('core.edit.own',		'com_mymuse.product.'.$track->id) && $track->created_by == $userId;
				$canChange	= $user->authorise('core.edit.state',	'com_mymuse.product.'.$track->id) && $canCheckin;

				
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
						<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $track->ordering; ?>" class="width-20 text-area-order " />
					<?php else : ?>
						<span class="sortable-handler inactive" >
							<i class="icon-menu"></i>
						</span>
					<?php endif; ?>
					</td>
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $track->id); ?>
					</td>
					<td class="center">
						<div class="btn-group">
								<?php echo JHtml::_('jgrid.published', $track->state, $i, 'products.', $canChange, 'cb', $track->publish_up, $track->publish_down); ?>
								<?php echo JHtml::_('mymuseadministrator.featured', $track->featured, $i, $canChange); ?>
								<?php // Create dropdown items and render the dropdown list.
								if ($canChange)
								{
									JHtml::_('actionsdropdown.' . ((int) $track->state === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'products');
									JHtml::_('actionsdropdown.' . ((int) $track->state === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'products');
									echo JHtml::_('actionsdropdown.render', $this->escape($track->title));
								}
								?>
							</div>
					</td>
					
					<td>
					<a href="<?php echo $link ?>"><?php echo $this->escape($track->title); ?></a> 
					<?php  if($track->product_allfiles == "1"){ 
						echo JText::_("MYMUSE_ALL_TRACKS");
					 } ?>
					 
					</td>
					<td class="small hidden-phone">
						<?php echo $this->escape($track->access_level); ?>
					</td>
					
				<?php if(!$this->params->get('my_price_by_product')){ ?>	
					<td><?php echo $track->price; ?></td>
					<td><?php echo $track->product_discount; ?></td>
				<?php } ?>	
					
					
					<td><div id="product_player"></div></td>
					<td  align="center">
						<?php 
						foreach($files as $file){
							echo stripslashes($file->file_name)."<br />"; 
						}
						?>

					</td>
					<td nowrap="nowrap" align="center">
						<?php 
						foreach($files as $file){
							echo stripslashes($file->file_downloads)."<br />"; 
						}
						?>
					</td>
					<td  align="center">
						<?php 
						foreach($files as $file){
							echo MyMuseHelper::ByteSize($file->file_length)."<br />"; 
						}
						?>
					</td>
					
					<td><div id="product_player"></div></td>
					<td  align="center">
						<?php echo htmlspecialchars($track->file_preview, ENT_QUOTES); 						
						?>
					</td>
					

					<td>
						<?php echo $track->id; ?>
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