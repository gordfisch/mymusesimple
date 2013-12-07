<?php
		//ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS
$lists 		= $this->lists;
$listOrder	= $this->escape($this->state->get('item.ordering'));
$listDirn	= $this->escape($this->state->get('item.direction'));

$saveOrder	= $listOrder == 'a.ordering';
$user = JFactory::getUser();
$app		= JFactory::getApplication();
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_mymuse&task=product.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'articleList2', 'adminForm2', strtolower($listDirn), $saveOrderingUrl);
}
$sortFields = $this->getSortFields2();
$assoc		= isset($app->item_associations) ? $app->item_associations : 0;



		if(isset($lists['isNew'])){
			echo JText::_("MYMUSE_SAVE_THEN_ADD_ITEMS");
		}else{
			$ordering = ($listOrder == 'a.ordering');
		?>
		<script type="text/javascript">
		/**
		* Submit the item form
		*/
		function submitform2(pressbutton){
			if (pressbutton) {
				document.adminForm2.task.value=pressbutton;
			}
			if (typeof document.adminForm2.onsubmit == "function") {
				document.adminForm2.onsubmit();
			}
			document.adminForm2.submit();
		}
		
		function submitbutton2(pressbutton)
		{
			var form = document.adminForm2;

			if (pressbutton == 'cancel') {
				submitform2( pressbutton );
				return;
			}

			// do field validation
			if (form.title.value == ""){
				alert( "<?php echo JText::_( 'MYMUSE_ITEM_MUST_HAVE_A TITLE', true ); ?>" );

			} else {

				submitform2( pressbutton );
			}
		}








		/**
		* Submit the attribute list
		*/
		function submitform4(pressbutton){
			if (pressbutton) {
				document.adminForm4.task.value=pressbutton;
			}
			if (typeof document.adminForm4.onsubmit == "function") {
				document.adminForm4.onsubmit();
			}
			document.adminForm4.submit();
		}

		/**
		* Submit the attribute form
		*/
		function submitform5(pressbutton){
			if (pressbutton) {
				document.adminForm5.task.value=pressbutton;
			}
			if (typeof document.adminForm5.onsubmit == "function") {
				document.adminForm5.onsubmit();
			}
			document.adminForm5.submit();
		}
		
		Joomla.tableOrdering2=function(a,b,c,d){
			if("undefined"===typeof d&&(d=document.getElementById("adminForm2"),!d))d=document.adminForm2;
			d.filter_item_order.value=a;
			d.filter_item_order_Dir.value=b;
			d.id.value=<?php echo $this->item->id; ?>;
			Joomla.submitform(c,d)
		}



		Joomla.orderTable2 = function()
		{
			
			table = document.getElementById("sortTable2");
			direction = document.getElementById("directionTable2");
			order = table.options[table.selectedIndex].value;
			form = document.getElementById("adminForm2");
			if (order != '<?php echo $listOrder; ?>')
			{
				dirn = 'asc';
			}
			else
			{
				dirn = direction.options[direction.selectedIndex].value;
			}
			alert(order+" "+dirn);
			Joomla.tableOrdering2(order, dirn, '', form);
		}




		</script>
<div id="items">
<h2><?php echo JText::_( 'MYMUSE_ITEMS' ); ?></h2>
	<div id="content-box">
		<div id="toolbar-box">
			<div class="m">
				<div class="toolbar-list" id="toolbar">
	<ul  style="list-style-type: none;">

		<li class="button" id="toolbar-edit" style="display: inline;"><a href="#"
			onclick="javascript: submitform4('list')" class="toolbar"> <span
			class="icon-32-publish"> </span> <?php echo JText::_( 'MYMUSE_LIST_ATTRIBUTES' ); ?> </a>
		</li>

		<li class="button" id="toolbar-new" style="display: inline;"><a href="#"
			onclick="javascript: submitform5('productattributesku.add')" class="toolbar"> <span
			class="icon-32-new"> </span> <?php echo JText::_( 'MYMUSE_ADD_ATTRIBUTES' ); ?> </a>
		</li>	
		<li class="button" id="toolbar-all" style="display: inline;"><a href="#"
			onclick="javascript: submitbutton2('product.additem')" class="toolbar"> <span
			class="icon-32-new"> </span> <?php echo JText::_( 'MYMUSE_NEW_ITEM' ); ?> </a>
		</li>
		<li class="button" id="toolbar-new" style="display: inline;"><a href="#"
			onclick="javascript: submitbutton2('product.removeitem')" class="toolbar"> <span
			class="icon-32-delete"> </span> <?php echo JText::_( 'MYMUSE_DELETE_ITEM' ); ?> </a>
		</li>
	</ul>
				</div>
			</div>
		</div>
	</div>
<?php } ?>	

	<form action="index.php" method="post" name="adminForm2" id="adminForm2">
	<input type="hidden" name="view" value="product" /> 
	<input type="hidden" name="layout" value="edit" /> 
	<input type="hidden" name="option" value="com_mymuse" /> 
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" /> 
	<input type="hidden" name="task" value="" /> 
	<input type="hidden" name="subtype" value="item" /> 
	<input type="hidden" name="boxchecked" value="" /> 
	<input type="hidden" name="parentid" value="<?php echo $this->item->id; ?>" /> 
	<input type="hidden" name="filter_item_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_item_order_Dir" value="<?php echo $listDirn; ?>" />
	
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
				<label for="filter_search" class="element-invisible"><?php echo JText::_('JSEARCH'); ?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText('COM_CONTENT_FILTER_SEARCH_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left hidden-phone">
				<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>

			<div class="btn-group pull-right hidden-phone">
				<label for="directionTable2" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
				<select name="directionTable2" id="directionTable2" class="input-medium" onchange="Joomla.orderTable2()">
					<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
					<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?></option>
					<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');  ?></option>
				</select>
			</div>
			<div class="btn-group pull-right">
				<label for="sortTable2" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY'); ?></label>
				<select name="sortTable2" id="sortTable2" class="input-medium" onchange="Joomla.orderTable2()">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
				</select>
			</div>
		</div>
		<div class="clearfix"> </div>
	<?php 
	if(count($this->items) > 0){ 
	?>
		<table class="adminlist table-striped"  id="articleList2">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo MyMuseHelper::sort2('<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
					<th width="1%" class="hidden-phone">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th width="1%" style="min-width:55px" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th width="25%">
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th class="title" width="10%">
						<?php echo JHtml::_('grid.sort', 'MYMUSE_PRICE', 'a.price', $listDirn, $listOrder); ?>
					</th>
					<th class="title" width="10%">
						<?php echo JHtml::_('grid.sort', 'MYMUSE_DISCOUNT', 'a.discount', $listDirn, $listOrder); ?>
					</th>
		
					<?php foreach($lists['attribute_sku'] as $a_sku){ ?>
						<th><?php echo $a_sku->name; ?>
						</th>
					<?php } ?>
					<th width="1%" class="title">
						<?php echo JHtml::_('grid.sort', 'MYMUSE_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			$config =& JFactory::getConfig();
		    $tzoffset = $config->get('config.offset');
			$now =& JFactory::getDate();
			for ($i=0, $n=count( $this->items ); $i < $n; $i++)
			{
				
				$item = &$this->items[$i];
				$item->checked_out = 0;
				$link 	= 'index.php?option=com_mymuse&task=product.edit&type=item&id='. $item->id;
				$checked 	= JHTML::_('grid.checkedout',  $item, $i );
				$alt = "p";
				
				$checked 	= JHTML::_('grid.checkedout',  $item, $i );
				
				$canCreate	= $user->authorise('core.create',		'com_mymuse.category.'.$item->catid);
				$canEdit	= $user->authorise('core.edit',			'com_mymuse.product.'.$item->id);
				$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $item->checked_out == $userId || $file->checked_out == 0;
				$canEditOwn	= $user->authorise('core.edit.own',		'com_mymuse.product.'.$item->id) && $item->created_by == $user->get('id');
				$canChange	= $user->authorise('core.edit.state',	'com_mymuse.product.'.$item->id) && $canCheckin;
				
				
				?>
				<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid; ?>">
					<td class="order nowrap center hidden-phone">
						<?php
						$iconClass = '';
						if (!$canChange)
						{
							$iconClass = ' inactive';
						}
						elseif (!$saveOrder)
						{
							$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
						}
						?>
						<span class="sortable-handler<?php echo $iconClass ?>">
							<i class="icon-menu"></i>
						</span>
						<?php if ($canChange && $saveOrder) : ?>
							<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
						<?php endif; ?>
					</td>
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="center">
						<div class="btn-group">
							<?php echo JHtml::_('jgrid.published', $item->state, $i, 'products.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
							<?php //echo JHtml::_('contentadministrator.featured', $file->featured, $i, $canChange); ?>
						</div>
					</td>
					<td>
					<a href="<?php echo $link ?>"><?php echo htmlspecialchars($item->title, ENT_QUOTES); ?></a>
					</td>
					<td align="right"><?php echo $item->price; ?></td>
					<td align="right"><?php echo $item->product_discount; ?></td>
					<?php foreach($lists['attribute_sku'] as $a_sku){?>
						<td align="center"><?php echo $item->attributes[$a_sku->name]; ?>
						</td>
					<?php } ?>
					<td>
						<?php echo $item->id; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
			</table>

		<?php } ?>



	
	<?php echo JHTML::_( 'form.token' ); ?>
	</form>
	
	<form action="index.php" method="post" name="adminForm4">
	<input type="hidden" name="parentid" value="<?php echo $this->item->id; ?>" /> 
	<input type="hidden" name="option" value="com_mymuse" /> 
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="productattributeskus" />
	<?php echo JHTML::_( 'form.token' ); ?>
	</form>
	
	<form action="index.php" method="post" name="adminForm5">
	<input type="hidden" name="parentid" value="<?php echo $this->item->id; ?>" /> 
	<input type="hidden" name="option" value="com_mymuse" /> 
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="productattributesku" />
	<?php echo JHTML::_( 'form.token' ); ?>
	</form>
    
    </div>
</div>