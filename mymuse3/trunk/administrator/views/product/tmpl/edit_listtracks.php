<?php
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.switcher');


$lists = $this->lists;
$listOrder	= $this->escape($this->state->get('file.ordering'));
$listDirn	= $this->escape($this->state->get('file.direction'));
$saveOrder	= $listOrder == 'a.ordering';

$user		= JFactory::getUser();
$userId		= $user->get('id');






//TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS

		
		//See if there is an all files zip
		$all_files = 0;
		for ($i=0, $n=count( $this->tracks ); $i < $n; $i++)
		{
			if($this->tracks[$i]->product_allfiles == "1"){ 
				$all_files = 1;
			}
		}
			
		if(isset($lists['isNew'])){
			echo JText::_("MYMUSE_SAVE_THEN_ADD_TRACKS");
		}else{
		//Ordering allowed ?
		$ordering = ($listOrder == 'a.ordering');

		?>
<script language="javascript" type="text/javascript">
		/**
		* Submit the tracks form
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
			submitform2( pressbutton );

		}

		// needed for Table Column ordering
		function tableOrdering2( order, dir, task ) {
			var form = document.adminForm2;

			form.filter_order.value 	= order;
			form.filter_order_Dir.value	= dir;
			form.use_parentid.value 	= 1;
			submitform2( task );
		}

		function saveorder2( n,  task ) {
			checkAll_button2( n, task );
		}

		//needed by saveorder2 function
		function checkAll_button2( n, task ) {

		    if (!task ) {
				task = 'saveorder';
			}

			for ( var j = 0; j <= n; j++ ) {
				box = eval( "document.adminForm2.cb" + j );
				if ( box ) {
					if ( box.checked == false ) {
						box.checked = true;
					}
				} else {
					alert("You cannot change the order of items, as an item in the list is `Checked Out`");
					return;
				}
			}
			submitform2(task);
		}
		//tracks orderup and orderdown, publish/unpublish
		function listItemTask2( id, task ) {
		    var f = document.adminForm2;
		    cb = eval( 'f.' + id );
		    if (cb) {
		        for (i = 0; true; i++) {
		            cbx = eval('f.cb'+i);
		            if (!cbx) break;
		            cbx.checked = false;
		        } // for
		        cb.checked = true;
		        f.boxchecked.value = 1;
		        submitbutton2(task);
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
		function checkAll2( n, fldName ) {
		  if (!fldName) {
		     fldName = 'cb';
		  }
			var f = document.adminForm2;
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
				document.adminForm2.boxchecked.value = n2;
			} else {
				document.adminForm2.boxchecked.value = 0;
			}
		}

		Joomla.tableOrdering2=function(a,b,c,d){if("undefined"===typeof d&&(d=document.getElementById("adminForm2"),!d))d=document.adminForm2;d.filter_order.value=a;d.filter_order_Dir.value=b;d.id.value=<?php echo $this->item->id; ?>;Joomla.submitform(c,d)};

		</script>

<h2><?php echo JText::_( 'MYMUSE_TRACKS' ); ?></h2>
	<div id="content-box">
		<div id="toolbar-box">
			<div class="m">
				<div class="toolbar-list" id="toolbar">
	<ul  style="list-style-type: none;">

		<li class="button" id="toolbar-edit"  style="display: inline;"><a href="#"
			onclick="javascript: submitbutton2('product.edit')" class="toolbar"> <span
			class="icon-32-edit"> </span> <?php echo JText::_( 'MYMUSE_EDIT_TRACK' ); ?> </a>
		</li>

		<li class="button" id="toolbar-new"  style="display: inline;"><a href="#"
			onclick="javascript: submitbutton2('product.addfile')" class="toolbar"> <span
			class="icon-32-new"> </span> <?php echo JText::_( 'MYMUSE_NEW_TRACK' ); ?> </a>
		</li>
<?php if(!$all_files){ ?>		
		<li class="button" id="toolbar-all"  style="display: inline;"><a href="#"
			onclick="javascript: submitbutton2('product.new_allfiles')" class="toolbar"> <span
			class="icon-32-new"> </span> <?php echo JText::_( 'MYMUSE_ALL_TRACKS' ); ?> </a>
		</li>
<?php } ?>	
		<li class="button" id="toolbar-new"  style="display: inline;"><a href="#"
			onclick="javascript: submitbutton2('product.removefile')" class="toolbar"> <span
			class="icon-32-delete"> </span> <?php echo JText::_( 'MYMUSE_DELETE_TRACKS' ); ?> </a>
		</li>
	</ul>
				</div>
			</div>
		</div>
	</div>

<?php } ?>
<div style="clear: both;"></div>
<div class="tracks">
<form action="index.php" method="post" name="adminForm2">
	<input type="hidden" name="view" value="product" /> 
	<input type="hidden" name="layout" value="edit" /> 
	<input type="hidden" name="option" value="com_mymuse" /> 
	<input type="hidden" name="id" value="" /> 
	<input type="hidden" name="task" value="" /> 
	<input type="hidden" name="subtype" value="file" /> 
	<input type="hidden" name="boxchecked" value="" /> 
	<input type="hidden" name="parentid" value="<?php echo $this->item->id; ?>" /> 
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php 

	if(count($this->tracks) > 0){ 

		?>
		<table class="adminlist">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
					<th width="5">
						<input type="checkbox" name="toggle" value="" onclick="checkAll2(<?php echo count($this->tracks ); ?>);" />
					</th>
					<th>
					 <?php echo MyMuseHelper::sort2('Title', 'a.title', $listDirn, $listOrder ); ?>
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
					<th width="1%" nowrap="nowrap">
						<?php echo MyMuseHelper::sort2('Published', 'a.state', $listDirn, $listOrder ); ?>
					</th>
					<th width="1%" class="title">ID
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
			for ($i=0, $n=count( $this->tracks ); $i < $n; $i++)
			{
				$file = &$this->tracks[$i];

				if($file->product_allfiles == "1"){
					$link 	= 'index.php?option=com_mymuse&task=product.edit&type=allfiles&id='. $file->id;
				}else{
					$link 	= 'index.php?option=com_mymuse&task=product.edit&type=file&id='. $file->id;
				}
				$alt = "p";
				
				$checked 	= JHTML::_('grid.checkedout',  $file, $i );
				
				
				$canCreate	= $user->authorise('core.create',		'com_mymuse.category.'.$file->catid);
				$canEdit	= $user->authorise('core.edit',			'com_mymuse.product.'.$file->id);
				$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $file->checked_out == $userId || $file->checked_out == 0;
				$canEditOwn	= $user->authorise('core.edit.own',		'com_mymuse.product.'.$file->id) && $file->created_by == $userId;
				$canChange	= $user->authorise('core.edit.state',	'com_mymuse.product.'.$file->id) && $canCheckin;

				
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
						<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $file->ordering; ?>" class="width-20 text-area-order " />
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
					<a href="<?php echo $link ?>"><?php echo htmlspecialchars($file->title, ENT_QUOTES); ?></a> 
					<?php  if($file->product_allfiles == "1"){ 
						echo JText::_("MYMUSE_ALL_TRACKS");
					 } ?>
					</td>
					<td><?php echo $file->price; ?></td>
					<td><?php echo $file->product_discount; ?></td>
					
					<td><div id="product_player"><?php echo $file->stream; ?></div></td>
					<td  align="center">
						<?php echo stripslashes($file->file_name); ?>
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
					<td align="center">
								<?php echo MGrid::published( $file->state, $i, 'products.', 1, 'cb', $file->publish_up, $file->publish_down, 2); ?>
					</td>
					<td>
						<?php echo $file->id; ?>
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