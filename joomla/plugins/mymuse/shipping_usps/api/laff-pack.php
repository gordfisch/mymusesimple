<?php
defined ('_JEXEC') or die('Restricted access');
/**
 * Largest Area Fit First (LAFF) 3D box packing algorithm class
 *
 **
 * @author Park Beach Systems, Inc <info@parkbeachsystems.com>
 * @copyright 2013-2015
 * @license GNU General Public License version 3, or later http://www.gnu.org/licenses/gpl.html
 * @version 1.2
 * 
 * Original LAFFPack updated significantly by PBS to ensure boxes being packed do not modify outer container. Also modified
 *  to properly fit boxes into each layer and track boxes that do not fit. 
 *  Added additional logic to provide container maximum weight allowed and to ensure boxes are added only when weight of 
 *  container does not exceed max weight.
 *
 * Based on Original Program written by:
 * @author Maarten de Boer <info@maartendeboer.net>
 * @copyright Maarten de Boer 2012
 * @version 1.0
 *
 * Also see this PDF document for an explanation about the LAFF algorithm:
 * @link http://www.zahidgurbuz.com/yayinlar/An%20Efficient%20Algorithm%20for%203D%20Rectangular%20Box%20Packing.pdf
 *
 * Copyright (C) 2012 Maarten de Boer
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
class LAFFPack_1_2 {

	/** @var array $boxes Array of boxes to pack */
	private $boxes = null;

	/** @var array $packed_boxes Array of boxes that have been packed */
	private $packed_boxes = null;

	/** @var int $level Current level we're packing (0 based) */
	private $level = -1;

	/** @var array $container_dimensions Current container dimensions */
	private $container_dimensions = null;

	/** @var float $container_max_weight Maximum weight allowed in container */
	private $container_max_weight = 0;
	
	/** @var float $packed_height Current height of packed levels */
	private $packed_height = 0;
	
	private $previous_biggest_box_index = null;
	
	/**
	 * Constructor of the BoxPacking class
	 *
	 * @access public
	 * @param array $boxes Array of boxes to pack
	 */
	function __construct($boxes = null, $container = null)
	{		
		if(isset($boxes) && is_array($boxes)) {
			$this->boxes = $boxes;
			$this->packed_boxes = array();

			// Calculate container size
			if(!is_array($container)) {
				$this->container_dimensions = $this->_calc_container_dimensions();
			}
			else {
				if(!array_key_exists('length', $container) ||
					!array_key_exists('width', $container)) {
						throw new InvalidArgumentException("Function _pack only accepts array (length, width, height) as argument for $container");
				}

				$this->container_dimensions['length'] = $container['length'];
				$this->container_dimensions['width'] = $container['width'];
				$this->container_dimensions['height'] = $container['height'];
				if(array_key_exists('max_weight', $container)) {
					$this->container_max_weight = $container['max_weight'];	
				}
			}
		}
	}

	/**
	 * Start packing boxes
	 * 
	 * @access public
	 * @param array $boxes
	 * @param array $container Set fixed container dimensions
	 * @returns void
	 */
	function pack($boxes = null, $container = null) {
		if(isset($boxes) && is_array($boxes)) {
			$this->boxes = $boxes;
			$this->packed_boxes = array();
			$this->level = -1;
			$this->container_dimensions = null;
			
			// Calculate container size
			if(!is_array($container)) {
				$this->container_dimensions = $this->_calc_container_dimensions();
			}
			else {
				if(!array_key_exists('length', $container) ||
					!array_key_exists('width', $container)) {
						throw new InvalidArgumentException("Pack function only accepts array (length, width, height, weight) as argument for \$container");
				}

				$this->container_dimensions['length'] = $container['length'];
				$this->container_dimensions['width'] = $container['width'];
				$this->container_dimensions['height'] = $container['height'];
				if(array_key_exists('max_weight', $container)) {
					$this->container_max_weight = $container['max_weight'];	
				}
				//echo'<br />Container Dimensions '.$this->container_dimensions['length'].'x'.$this->container_dimensions['width'].'x'.$this->container_dimensions['height'].' max_weight='.$this->container_max_weight;
			}
		}

		if(!isset($this->boxes)) {
			throw new InvalidArgumentException("Pack function only accepts array (length, width, height) as argument for \$boxes or no boxes given!");
		}

		$this->pack_level();
		//If not all boxes fit attempt to rotate container and repackage boxes
		if(count($this->boxes) > 0){
			//echo'<br />Attempting to rotate container and repack.';
			$this->boxes = $boxes;
			$this->packed_boxes = array();
			$this->level = -1;
			$this->packed_height = 0;
			$this->container_dimensions = null;
			$this->container_dimensions['length'] = $container['width'];
			$this->container_dimensions['width'] = $container['length'];
			$this->container_dimensions['height'] = $container['height'];
			$this->pack_level();
		}
		
	}

	/**
	 * Get remaining boxes to pack
	 *
	 * @access public
	 * @returns array
	 */
	function get_remaining_boxes() {
		return $this->boxes;
	}
	/**
	* Get remaining number of boxes to pack
	*
	* @access public
	* @returns float
	*/
	function get_remaining_number_boxes() {
		$r_boxes = $this->get_remaining_boxes();

		return count($r_boxes);
	}
	
	/**
	 * Get packed boxes
	 *
	 * @access public
	 * @returns array
	 */
	function get_packed_boxes() {	
		return $this->packed_boxes;
	}

	/**
	 * Get container dimensions
	 *
	 * @access public
	 * @returns array
	 */
	function get_container_dimensions() {
		return $this->container_dimensions;
	}

	/**
	 * Get container max_weight
	 *
	 * @access public
	 * @returns float
	 */
	function get_container_max_weight() {
		return $this->container_max_weight;
	}

	/**
	 * Get container volume
	 *
	 * @access public
	 * @returns float
	 */
	function get_container_volume() {
		if(!isset($this->container_dimensions)) {
			return 0;
		}

		return $this->_get_volume($this->container_dimensions);
	}

	/**
	 * Get number of levels
	 *
	 * @access public
	 * @returns int
	 */
	function get_levels() {
		return $this->level + 1;
	}

	/**
	 * Get total volume of packed boxes
	 *
	 * @access public
	 * @returns float
	 */
	function get_packed_volume() {
		if(!isset($this->packed_boxes)) {
			return 0;
		}

		$volume = 0;

		for($i = 0; $i < count(array_keys($this->packed_boxes)); $i++) {
			foreach($this->packed_boxes[$i] as $box) {
				$volume += $this->_get_volume($box);
			}
		}

		return $volume;
	}
	
	/**
	 * Get total weight of packed boxes
	 *
	 * @access public
	 * @returns float
	 */
	function get_packed_weight() {
		if(!isset($this->packed_boxes)) {
			return 0;
		}

		$weight = 0;

		for($i = 0; $i < count(array_keys($this->packed_boxes)); $i++) {
			foreach($this->packed_boxes[$i] as $box) {
				$weight += $this->_get_weight($box);
			}
		}

		return $weight;
	}

	/**
	 * Get total value of packed boxes
	 *
	 * @access public
	 * @returns float
	 */
	function get_packed_value() {
		if(!isset($this->packed_boxes)) {
			return 0;
		}

		$value = 0;

		for($i = 0; $i < count(array_keys($this->packed_boxes)); $i++) {
			foreach($this->packed_boxes[$i] as $box) {
				$value += $this->_get_value($box);
			}
		}

		return $value;
	}

	/**
	 * Get volume of remaining boxes
	 *
	 * @access public
	 * @returns int
	 */
	function get_remaining_volume() {
		//if(!isset($this->packed_boxes)) {
		if(!isset($this->boxes)) {
			return 0;
		}

		$volume = 0;

		foreach($this->boxes as $box) {
			$volume += $this->_get_volume($box);
		}

		return $volume;
	}
	
	/**
	 * Get weight of remaining boxes
	 *
	 * @access public
	 * @returns float
	 */
	function get_remaining_weight() {
		if(!isset($this->boxes)) {
			return 0;
		}

		$weight = 0;

		foreach($this->boxes as $box) {
				$weight += $this->_get_weight($box);
		}

		return $weight;
	}

	/**
	 * Get dimensions of specified level
	 *
	 * @access public
	 * @param int $level
	 * @returns array
	 */
	function get_level_dimensions($level = 0) {
		if($level < 0 || $level > $this->level || !array_key_exists($level, $this->packed_boxes)) {
			throw new OutOfRangeException("Level {$level} not found!");
		}

		$boxes = $this->packed_boxes;
		$edges = array('length', 'width', 'height');

		// Get longest edge
		//$le = $this->_calc_longest_edge($boxes[$level], $edges);
		$ll = $this->_calc_longest_edge($boxes[$level], array('length'));
		$edges = array_diff($edges, array($ll['edge_name']));

		// Re-iterate and get longest edge now (second longest)
		//$sle = $this->_calc_longest_edge($boxes[$level], $edges);
		$lw = $this->_calc_longest_edge($boxes[$level], array('width'));

		return array(
			//'width' => $le['edge_size'],
			//'length' => $sle['edge_size'],
			'width' => $lw['edge_size'],
			'length' => $ll['edge_size'],
			'height' => $boxes[$level][0]['height']
		);
	}

	/**
	 * Get longest edge from boxes
	 *
	 * @access public
	 * @param array $edges Edges to select the longest from
	 * @returns array
	 */
	function _calc_longest_edge($boxes, $edges = array('length', 'width', 'height')) {
		if(!isset($boxes) || !is_array($boxes)) {
			throw new InvalidArgumentException('_calc_longest_edge function requires an array of boxes, '.typeof($boxes).' given');
		}

		// Longest edge
		$le = null;		// Longest edge
		$lef = null;	// Edge field (length | width | height) that is longest

		// Get longest edges
		foreach($boxes as $k => $box) {
			foreach($edges as $edge) {
				if(array_key_exists($edge, $box) && $box[$edge] > $le) {
					$le = $box[$edge];	
					$lef = $edge;
				}
			}
		}

		return array(
			'edge_size' => $le,
			'edge_name' => $lef
		);
	}

	/**
	 * Calculate container dimensions
	 *
	 * @access public
	 * @returns array
	 */
	function _calc_container_dimensions() {
		if(!isset($this->boxes)){
			return array(
				'length' => 0,
				'width' => 0,
				'height' => 0
			);
		}

		$boxes = $this->boxes;

		$edges = array('length', 'width', 'height');

		// Get longest edge
		$le = $this->_calc_longest_edge($boxes, $edges);
		$edges = array_diff($edges, array($le['edge_name']));

		// Re-iterate and get longest edge now (second longest)
		$sle = $this->_calc_longest_edge($boxes, $edges);

		return array(
			'length' => $sle['edge_size'],
			'width' => $le['edge_size'],
			'height' => 0
		);
	}

	/**
	 * Utility function to swap two elements in an array
	 * 
	 * @access public
	 * @param array $array
	 * @param mixed $el1 Index of item to be swapped
	 * @param mixed $el2 Index of item to swap with
	 * @returns array
	 */ 
	function _swap($array, $el1, $el2) {
		if(!array_key_exists($el1, $array) || !array_key_exists($el2, $array)) {
			throw new InvalidArgumentException("Both element to be swapped need to exist in the supplied array");
		}

		$tmp = $array[$el1];
		$array[$el1] = $array[$el2];
		$array[$el2] = $tmp;

		return $array;
	}

	/**
	 * Utility function that returns the total volume of a box / container
	 *
	 * @access public
	 * @param array $box
	 * @returns float
	 */
	function _get_volume($box)  {	
		if(!is_array($box) || count(array_keys($box)) < 3) {
			throw new InvalidArgumentException("_get_volume function only accepts arrays with 3 values (length, width, height)");
		}

		//$box = array_values($box);
		//return $box[0] * $box[1] * $box[2]; 
		return $box['length'] * $box['width'] * $box['height']; 
	}
	
	/**
	 * Utility function that returns the total weight of a box / container
	 *
	 * @access public
	 * @param array $box
	 * @returns float
	 */
	function _get_weight($box)  {	
		if(!is_array($box) || !array_key_exists('weight', $box)) {
			throw new InvalidArgumentException("_get_weight function only accepts array with weight key");
		}

		return $box['weight']; 
	}
	
	/**
	 * Utility function that returns the total value of a box / container
	 *
	 * @access public
	 * @param array $box
	 * @returns float
	 */
	function _get_value($box)  {	
		if(!is_array($box) || !array_key_exists('value', $box)) {
			throw new InvalidArgumentException("_get_value function only accepts array with value key");
		}

		return $box['value']; 
	}

	/**
	 * Check if box fits in specified space
	 *
	 * @access private
	 * @param array $box Box to fit in space
	 * @param array $space Space to fit box in
	 * @returns bool
	 */
	private function _try_fit_box($box, $space)  {
		if(count($box) < 3) {
			throw new InvalidArgumentException("_try_fit_box function parameter $box only accepts arrays with 3 values (length, width, height)");
		}

		if(count($space) < 3) {
			throw new InvalidArgumentException("_try_fit_box function parameter $space only accepts arrays with 3 values (length, width, height)");
		}

		for($i = 0; $i < count($box); $i++) {
			if(array_key_exists($i, $space)) {
				//echo'<br />  Compare dimension box'.$i.'='.$box[$i].' with space'.$i.'='.$space[$i];
				if($box[$i] > $space[$i]) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Check if box fits in specified space
	 * and rotate (3d) if necessary
	 * 
	 * @access public
	 * @param array $box Box to fit in space
	 * @param array $space Space to fit box in
	 * @returns bool
	 */
	function _box_fits($box, $space) {
		//$box = array_values($box);
		$box = array($box['length'],$box['width'],$box['height']);
		$space = array_values($space);
		//echo'<br /> Original sides '.$box[0].'x'.$box[1].'x'.$box[2];
		if($this->_try_fit_box($box, $space)) {
			//return true;
			return $box;		}

		for($i = 0; $i < count($box); $i++) {
			// Temp box size
			$t_box = $box;

			// Remove fixed column from list to be swapped
			unset($t_box[$i]);

			// Keys to be swapped
			$t_keys = array_keys($t_box);

			// Temp box with swapped sides
			$s_box = $this->_swap($box, $t_keys[0], $t_keys[1]);
			if($this->_try_fit_box($s_box, $space)){
				// Change box dimensions to swapped box
				//echo'<br />  Swapped sides '.$s_box[0].'x'.$s_box[1].'x'.$s_box[2];
				//return true;
				return $s_box;
			}
		}

		//return false;
		return null;
	}

	/**
	 * Start a new packing level
	 *
	 * @access private
	 * @returns void
	 */
	private function pack_level() {
		//echo'<br />';
		$biggest_box_index = null;
		//$previous_biggest_box_index = null;
		$biggest_surface = 0;
		//echo'<br />$previous_biggest_box_index '.$this->previous_biggest_box_index;
		// Check if all boxes have been packed
		if(count($this->boxes) == 0)
		return;

		// Find biggest (widest surface) box with minimum height to build first packing level
		foreach($this->boxes as $k => $box){
			//Get all edge sizes to build a loop of all edge comparisons
			//$edges = array_values($box); //l,w,h
			$edges = array($box['length'],$box['width'],$box['height']);
			rsort($edges); //put height as smallest dimension
			//update box dimensions since could be rotated
			//echo'<br /> Fitted Box dimensions '.$fittedbox[0].'x'.$fittedbox[1].'x'.$fittedbox[2];
			$this->boxes[$k]['length'] = $edges[0];
			$this->boxes[$k]['width'] = $edges[1];
			$this->boxes[$k]['height'] = $edges[2];	
			//$surface = $box['length'] * $box['width'];
			$surface = $edges[0] * $edges[1];

			if($surface > $biggest_surface) {
				$biggest_surface = $surface;
				$biggest_box_index = $k;
			}
			elseif($surface == $biggest_surface) {
				if(!isset($biggest_box_index) || (isset($biggest_box_index) && $edges[2] < $this->boxes[$biggest_box_index]['height']))
					$biggest_box_index = $k;
			}
		}

		// Get biggest box as object
		$biggest_box = $this->boxes[$biggest_box_index];
		// Check if attempting to pack same box as last pack_level. if so exit.
		if((isset($this->previous_biggest_box_index)) && ($this->previous_biggest_box_index === $biggest_box_index))
		return;
		
		$this->previous_biggest_box_index = $biggest_box_index;
	
		$c_area = $this->container_dimensions['length'] * $this->container_dimensions['width'];
		//$p_area = $biggest_box['length'] * $biggest_box['width'];
		$p_area = $biggest_surface;
		//echo '<br />&nbsp;Container area='.$c_area.' Package area='.$p_area;
		// No space left (not even when rotated / length and width swapped)
		//if($c_area - $p_area <= 0) {
		if(($c_area - $p_area < 0) || ($biggest_box['height'] > $this->container_dimensions['height'] - $this->packed_height)) {
			//echo '<br />&nbsp;Largest package bigger than container. Boxes left: '.count($this->boxes);
			//$this->pack_level();
		}
		else { // Space left, check if a package fits in
			$this->level++;
			//echo'<br />Packing level '.$this->level.': Largest surface with smallest height box is index '.$biggest_box_index.' '.$biggest_box['length'].'x'.$biggest_box['width'].'x'.$biggest_box['height'];
			$spaces = array();
			$spaces[] = array(
			 					'length' => $this->container_dimensions['length'],
			 					'width' => $this->container_dimensions['width'],
			 					//'height' => $this->container_dimensions['height'] - $this->packed_height);
								'height' => $biggest_box['height']);
			
			// Fill each space with boxes
			foreach($spaces as $space) {				
				//echo '<br />&nbsp;Space '.$space['length'].'x'.$space['width'].'x'.$space['height'];
				$this->_fill_space($space);
			}
			
			if(isset($this->packed_boxes[$this->level])){
				$ld = $this->get_level_dimensions($this->level);
				$this->packed_height = $this->packed_height + $ld['height'];
				//echo'<br /> Packed_height='.$this->packed_height;
			}else{
				$this->level--; //No boxes were packed into level so remove level
			}		
						
			//echo '<br />&nbsp;Remaining boxes '.count($this->boxes);
			// Start packing remaining boxes on a new level
			if(count($this->boxes) > 0)
				$this->pack_level();
		}
	}

	/**
	 * Fills space with boxes recursively
	 *
	 * @access private
	 * @returns void
	 */
	private function _fill_space($space) {	
		// Total space volume
		$s_volume = $this->_get_volume($space);

		$fitting_box_index = null;
		$fitting_box_volume = null;

		foreach($this->boxes as $k => $box)
		{
			// Skip boxes that have a higher volume than target space
			//echo '<br />box vol'.$this->_get_volume($box).' space vol'.$s_volume;
			if($this->_get_volume($box) > $s_volume) {
				continue;
			}
			// Skip boxes that cause weight to be greater than maximum container weight
			//echo '<br />box weight'.$this->_get_weight($box). ' packed='.$this->get_packed_weight();
			if($this->container_max_weight > 0 && ($this->_get_weight($box) + $this->get_packed_weight()) > $this->container_max_weight) {
				continue;
			}

			//if($this->_box_fits($box, $space)) {
			//if(isset($this->_box_fits($box, $space))) {
			$fittedbox = $this->_box_fits($box, $space);
			if(isset($fittedbox)) {
				$b_volume = $this->_get_volume($box);

				if(!isset($fitting_box_volume) || $b_volume > $fitting_box_volume) {
					$fitting_box_index = $k;
					$fitting_box_volume = $b_volume;
					//update box dimensions since could be rotated
					//echo'<br /> Fitted Box dimensions '.$fittedbox[0].'x'.$fittedbox[1].'x'.$fittedbox[2];
					$this->boxes[$k]['length'] = $fittedbox[0];
					$this->boxes[$k]['width'] = $fittedbox[1];
					$this->boxes[$k]['height'] = $fittedbox[2];
				}
			}
		}

		if(isset($fitting_box_index))
		{
			$box = $this->boxes[$fitting_box_index];

			// Pack box
			//echo'<br />&nbsp;Packing Box '.$fitting_box_index.': '.$box['length'].'x'.$box['width'].'x'.$box['height'];
			$this->packed_boxes[$this->level][] = $this->boxes[$fitting_box_index];
			unset($this->boxes[$fitting_box_index]);

			// Calculate remaining space left (in current space)
			$new_spaces = array();

			if($space['length'] - $box['length'] > 0) {
				$new_spaces[] = array(
					'length' => $space['length'] - $box['length'],
					'width' => $space['width'],
					'height' => $box['height']
				);					
			}

			if($space['width'] - $box['width'] > 0) {
				$new_spaces[] = array(
					'length' => $box['length'],
					'width' => $space['width'] - $box['width'],
					'height' => $box['height']
				);
			}

			if(count($new_spaces) > 0) {
				foreach($new_spaces as $new_space) {
					$this->_fill_space($new_space);
				}
			}
		}
	}
}

?>
