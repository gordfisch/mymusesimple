<?php
		//ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS
$lists 		= $this->lists;
$listOrder	= $this->escape($this->state->get('item.ordering'));
$listDirn	= $this->escape($this->state->get('item.direction'));
$saveOrder	= $listOrder == 'a.ordering';
$user = JFactory::getUser();


		if(isset($lists['isNew'])){
			echo JText::_("MYMUSE_SAVE_THEN_ADD_ITEMS");
		}else{
			$ordering = ($listOrder == 'a.ordering');
		?>
		<script type="text/javascript">
		/**
		* Submit the item form
		*/
		function submitform3(pressbutton){
			if (pressbutton) {
				document.adminForm3.task.value=pressbutton;
			}
			if (typeof document.adminForm3.onsubmit == "function") {
				document.adminForm3.onsubmit();
			}
			document.adminForm3.submit();
		}
		function submitbutton3(pressbutton)
		{
			var form = document.adminForm3;

			if (pressbutton == 'cancel') {
				submitform3( pressbutton );
				return;
			}

			// do field validation
			if (form.title.value == ""){
				alert( "<?php echo JText::_( 'MYMUSE_ITEM_MUST_HAVE_A TITLE', true ); ?>" );

			} else {

				submitform3( pressbutton );
			}
		}

		// needed for Table Column ordering
		function tableOrdering3( order, dir, task ) {
			var form = document.adminForm3;

			form.filter_order.value 	= order;
			form.filter_order_Dir.value	= dir;
			submitform3( task );
		}

		function saveorder3( n,  task ) {
			checkAll_button3( n, task );
		}

		//needed by saveorder3 function
		function checkAll_button3( n, task ) {

		    if (!task ) {
				task = 'saveorder';
			}
	
			for ( var j = 0; j <= n; j++ ) {
				box = eval( "document.adminForm3.cb" + j );
				if ( box ) {
					if ( box.checked == false ) {
						box.checked = true;
					}
				} else {
					alert("You cannot change the order of items, as an item in the list is `Checked Out`");
					return;
				}
			}
			submitform3(task);
		}
		//items orderup and orderdown
		function listItemTask3( id, task ) {
		    var f = document.adminForm3;
		    cb = eval( 'f.' + id );
		    if (cb) {
		        for (i = 0; true; i++) {
		            cbx = eval('f.cb'+i);
		            if (!cbx) break;
		            cbx.checked = false;
		        } // for
		        cb.checked = true;
		        f.boxchecked.value = 1;
		        submitbutton3(task);
		    }
		    return false;
		}
		/**
		* Toggles the check state of a group of boxes
		*
		* Checkboxes must have an id attribute in the form cb0, cb1...
		* @param The number of box to 'check'
		* @param An alternative field name
		*/
		function checkAll3( n, fldName ) {
		  if (!fldName) {
		     fldName = 'cb';
		  }
			var f = document.adminForm3;
			var c = f.toggle.checked;
			var n2 = 0;
			for (i=0; i < n; i++) {
				cb = eval( 'f.' + fldName + '' + i );
				if (cb) {
					cb.checked = c;
					n2++;
				}
			}
			if (c) {
				document.adminForm3.boxchecked.value = n2;
			} else {
				document.adminForm3.boxchecked.value = 0;
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
		
		Joomla.tableOrdering3=function(a,b,c,d){if("undefined"===typeof d&&(d=document.getElementById("adminForm3"),!d))d=document.adminForm3;d.filter_order.value=a;d.filter_order_Dir.value=b;d.id.value=<?php echo $this->item->id; ?>;Joomla.submitform(c,d)};
		
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
			onclick="javascript: submitbutton3('product.additem')" class="toolbar"> <span
			class="icon-32-new"> </span> <?php echo JText::_( 'MYMUSE_NEW_ITEM' ); ?> </a>
		</li>
		<li class="button" id="toolbar-new" style="display: inline;"><a href="#"
			onclick="javascript: submitbutton3('product.removeitem')" class="toolbar"> <span
			class="icon-32-delete"> </span> <?php echo JText::_( 'MYMUSE_DELETE_ITEM' ); ?> </a>
		</li>
	</ul>
				</div>
			</div>
		</div>
	</div>
<?php } ?>	

	<form action="index.php" method="post" name="adminForm3" id="adminForm">
	<input type="hidden" name="view" value="product" /> 
	<input type="hidden" name="layout" value="edit" /> 
	<input type="hidden" name="option" value="com_mymuse" /> 
	<input type="hidden" name="id" value="" /> 
	<input type="hidden" name="task" value="" /> 
	<input type="hidden" name="subtype" value="item" /> 
	<input type="hidden" name="boxchecked" value="" /> 
	<input type="hidden" name="parentid" value="<?php echo $this->item->id; ?>" /> 
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php 
	if(count($this->items) > 0){ 
	?>
		<table class="adminlist">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
					<th width="5">
						<input type="checkbox" name="toggle" value="" onclick="checkAll3(<?php echo count( $this->items ); ?>);" />
					</th>
					<th class="title" width="300">
					<?php echo MyMuseHelper::sort3('Title', 'a.title', $listDirn, $listOrder ); ?>
					</th>
					<th class="title" width="30"><?php echo JText::_('MYMUSE_PRICE'); ?></th>
					<th class="title" width="30"><?php echo JText::_('MYMUSE_DISCOUNT'); ?></th>
		
					<?php foreach($lists['attribute_sku'] as $a_sku){ ?>
						<th><?php echo $a_sku->name; ?>
						</th>
					<?php } ?>
					<th width="1%" nowrap="nowrap">
						<?php echo MyMuseHelper::sort3('Published', 'a.state', $listDirn, $listOrder ); ?>
					</th>

					<th width="1%" class="title"><?php echo JText::_('MYMUSE_ID'); ?>
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
				$canCreate	= $user->authorise('core.create',		'com_mymuse.category.'.$item->catid);
				$canEdit	= $user->authorise('core.edit',			'com_mymuse.product.'.$item->id);
				$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $item->checked_out == $userId || $file->checked_out == 0;
				$canEditOwn	= $user->authorise('core.edit.own',		'com_mymuse.product.'.$item->id) && $item->created_by == $user->get('id');
				$canChange	= $user->authorise('core.edit.state',	'com_mymuse.product.'.$item->id) && $canCheckin;
				
				
				?>
				<tr class="<?php echo "row$k"; ?>">
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
						<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
					<?php else : ?>
						<span class="sortable-handler inactive" >
							<i class="icon-menu"></i>
						</span>
					<?php endif; ?>
					</td>
					<td align="center">
						<?php echo $checked; ?>
					</td>
					<td>
					<a href="<?php echo $link ?>"><?php echo htmlspecialchars($item->title, ENT_QUOTES); ?></a>
					</td>
					<td align="right"><?php echo $item->price; ?></td>
					<td align="right"><?php echo $item->product_discount; ?></td>
					<?php foreach($lists['attribute_sku'] as $a_sku){?>
						<th><?php echo $item->attributes[$a_sku->name]; ?>
						</th>
					<?php } ?>
					<td align="center">
								
								<?php echo MGrid::published( $item->state, $i, 'products.', 1, 'cb', $item->publish_up, $item->publish_down, 3); ?>
					</td>

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