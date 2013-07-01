<?php
		//ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS

		$title = JText::_( 'MYMUSE_ITEMS' );

		if($lists['isNew']){
			echo JText::_("MYMUSE_SAVE_THEN_ADD_ITEMS");
		}else{
?>
		<script language="javascript" type="text/javascript">
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
			form.use_parentid.value 	= 1;
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
		* Submit the attribute form
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
		function submitbutton4(pressbutton)
		{
			var form = document.adminForm4;

			if (pressbutton == 'cancel') {
				submitform4( pressbutton );
				return;
			}
			submitform4( pressbutton );

		}

		</script>
	<table class="toolbar">
		<tr>

			<td class="button" id="toolbar-publish"><a href="#"
			onclick="javascript: submitbutton4('listattribute')" class="toolbar">
			<span class="icon-32-publish" title="List Attributes"> </span><?php 
			echo JText::_('MYMUSE_LIST_ATTRIBUTES'); ?></a></td>

			<td class="button" id="toolbar-new"><a href="#"
			onclick="javascript: submitbutton4('addattribute')" class="toolbar">
			<span class="icon-32-new" title="Add Attributes"> </span><?php 
			echo JText::_('MYMUSE_ADD_ATTRIBUTES'); ?>
			</a></td>
			
			<td class="button" id="toolbar-new"><a href="#"
			onclick="javascript: submitbutton3('additem')" class="toolbar"> <span
			class="icon-32-new" title="New Item"> </span><?php 
			echo JText::_('MYMUSE_NEW_ITEM'); ?></a></td>

			<td class="button" id="toolbar-delete"><a href="#"
			onclick="javascript: submitbutton3('removeitem')" class="toolbar"> <span
			class="icon-32-delete" title="Delete Item"> </span><?php 
			echo JText::_('MYMUSE_DELETE_ITEM'); ?></a></td>
			
			
		</tr>
	</table>
	
<?php } ?>	

	<form action="index.php" method="post" name="adminForm3">
	
	<?php 

	if(count($lists['items']) > 0){ ?>
		<table class="adminlist" cellspacing="1">
			<thead>
				<tr>
					<th width="5">
						<?php echo JText::_( 'Num' ); ?>
					</th>
					<th width="5">
						<input type="checkbox" name="toggle" value="" onclick="checkAll3(<?php echo count( $lists['items'] ); ?>);" />
					</th>
					<th class="title"><?php echo JText::_('MYMUSE_TITLE'); ?></th>
					<th class="title" width="50"><?php echo JText::_('MYMUSE_PRICE'); ?></th>
					<th class="title" width="50"><?php echo JText::_('MYMUSE_DISCOUNT'); ?></th>
		
					<?php foreach($lists['attribute_sku'] as $a_sku){ ?>
						<th><?php echo $a_sku->name; ?>
						</th>
					<?php } ?>
					<th width="1%" nowrap="nowrap">
						<?php echo JHTML::_('grid.sort',   'Published', 'c.state', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th width="8%" nowrap="nowrap">
					<?php echo MyMuseHelper::sort3('Order', 'c.ordering', @$lists['order_Dir'], '', 'edit' ); ?>

					<?php echo MyMuseHelper::order3($lists['items']); ?>
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
			for ($i=0, $n=count( $lists['items'] ); $i < $n; $i++)
			{
				
				$item = &$lists['items'][$i];
				$item->checked_out = 0;
				$link 	= 'index.php?option=com_mymuse&controller=product&task=edititem&cid[]='. $item->id;
				$checked 	= JHTML::_('grid.checkedout',  $item, $i );
				
				$publish_up =& JFactory::getDate($item->publish_up);
				$publish_down =& JFactory::getDate($item->publish_down);
				
				$publish_up->setTimezone($tzoffset);
				$publish_down->setTimezone($tzoffset);
				
				if ( $now->toUnix() <= $publish_up->toUnix() && $item->state == 1 ) {
					$img = 'publish_y.png';
					$alt = JText::_( 'Published' );
				} else if ( ( $now->toUnix() <= $publish_down->toUnix() || $item->publish_down == $nullDate ) && $item->state == 1 ) {
					$img = 'publish_g.png';
					$alt = JText::_( 'Published' );
				} else if ( $now->toUnix() > $publish_down->toUnix() && $item->state == 1 ) {
					$img = 'publish_r.png';
					$alt = JText::_( 'Expired' );
				} else if ( $item->state == 0 ) {
					$img = 'publish_x.png';
					$alt = JText::_( 'Unpublished' );
				} else if ( $item->state == -1 ) {
					$img = 'disabled.png';
					$alt = JText::_( 'Archived' );
				}
				$times = '';
				if (isset($item->publish_up)) {
					if ($item->publish_up == $nullDate) {
						$times .= JText::_( 'Start: Always' );
					} else {
						$times .= JText::_( 'Start' ) .": ". $publish_up->format();
					}
				}
				if (isset($item->publish_down)) {
					if ($item->publish_down == $nullDate) {
						$times .= "<br />". JText::_( 'Finish: No Expiry' );
					} else {
						$times .= "<br />". JText::_( 'Finish' ) .": ". $publish_down->format();
					}
				}
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo  $i; ?>
					</td>
					<td align="center">
						<?php echo $checked; ?>
					</td>
					<td>
					<a href="<?php echo $link ?>"><?php echo htmlspecialchars($item->title, ENT_QUOTES); ?></a>
					</td>
					<td align="right"><?php echo $item->product_price; ?></td>
					<td align="right"><?php echo $item->product_discount; ?></td>
					<?php foreach($lists['attribute_sku'] as $a_sku){?>
						<th><?php echo $item->attributes[$a_sku->name]; ?>
						</th>
					<?php } ?>
					<?php
					if ( $times ) {
						?>
						<td align="center">
							<span class="editlinktip hasTip" title="<?php echo JText::_( 'MYMUSE_PUBLISH_PRODUCT' );?>::<?php echo $times; ?>"><a href="javascript:void(0);" onclick="return listItemTask3('cb<?php echo $i;?>','<?php echo $item->state ? 'unpublish' : 'publish' ?>')">
								<img src="images/<?php echo $img;?>" width="16" height="16" border="0" alt="<?php echo $alt; ?>" /></a></span>
						</td>
						<?php
					}
					?>
					<td class="order">
						<span><?php echo MyMuseHelper::orderUpIcon3( $i, true, 'orderup', 'Move Up', $ordering, $lists['limitstart']); ?></span>
						<span><?php echo MyMuseHelper::orderDownIcon3( $i, $n, true, 'orderdown', 'Move Down', $ordering, $lists['limitstart'],count($lists['items']) ); ?></span>
						<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
	
					<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" <?php echo $disabled; ?>  class="text_area" style="text-align: center" />

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

	<input type="hidden" name="controller" value="product" /> 
	<input type="hidden" name="parentid" value="<?php echo $row->id; ?>" /> 
	<input type="hidden" name="version" value="<?php echo $row->version; ?>" /> 
	<input type="hidden" name="mask" value="0" /> 
	<input type="hidden" name="option" value="<?php echo $option;?>" /> 
	<input type="hidden" name="task" value="" /> 
	<input type="hidden" name="type" value="item" /> 
	<input type="hidden" name="use_parentid" value="" /> 
	<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $lists['order_Dir']; ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	</form>
	
