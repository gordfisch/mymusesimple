<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/

use Codeception\Module\Locators\Locators;
use Codeception\Lib\ModuleContainer;



class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

	/**
	 * The locator
	 *
	 * @var     Locators
	 * @since   3.7.4.2
	 */
	protected $locator;
/**
  function __construct() 
  {
    $this->locator = new Locators;

  }
*/
   /**
    * Define custom actions here
    */

   function seePageHasElement($element)
   {
       try {
           $this->seeElement($element);
       } catch (Exception $f) {
           return false;
       }
       return true;
   }

   function seePageHasText($text)
   {
    $this->comment('Looking for "'.$text.'"');
       try {
           $this->see($text);
       } catch (Exception $f) {
           return false;
       }
       return true;
   }

   function clearCart()
   {
        $this->amOnPage('index.php');
        $this->click("My Cart");
        if($this->seePageHasText("Clear Cart")){
          $this->click("Clear Cart");
        }
        return true;
   }

   public function selectFromDropdown($selector, $n)
   {
       $option = $this->grabTextFrom($selector . ' option:nth-child(' . $n . ')');
       $this->selectOption($selector, $option);
   }

  /**
   * Selects an option in a Joomla Radio Field based on its id
   *
   * @param   string  $id   The text in the <label> with for attribute that links to the radio element
   * @param   string  $option  The text in the <option> to be selected in the chosen radio button
   *
   * @return  void
   *
   * @since   3.0.0
   */
  public function selectOptionInRadioFieldById($radioId, $option)
  {

    $this->click("//fieldset[@id='$radioId']/label[contains(normalize-space(string(.)), '$option')]");
  }


   /**
    * Create mymuse categories
    *
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
   public function createMymuseCategories()
   {
    $this->locator = new Locators;

   	//MyMuse
   	$this->comment('Category creation in /administrator/ ');
   	$this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->comment('Click new category button');
   	$this->click($this->locator->adminToolbarButtonNew);

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->fillField(array('id' => 'jform_title'), 'MyMuse');

   	$this->comment('Click new category apply button');
   	$this->click($this->locator->adminToolbarButtonApply);

   	$this->comment('see a success message after saving the category');

   	$this->see('Category saved', '#system-message-container');


   	//Artists
   	$this->comment('Category MyMuse creation in /administrator/ ');
   	$this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->comment('Click new category button');
   	$this->click($this->locator->adminToolbarButtonNew);

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->fillField(array('id' => 'jform_title'), 'Artists');
   	//choose the parent
	  $this->click(array('css' => 'a.chzn-single > span'));
	  $this->click(array('xpath' => "//div[@id='jform_parent_id_chzn']/div/ul/li[2]"));


   	$this->comment('Click new category apply button');
   	$this->click($this->locator->adminToolbarButtonApply);

   	$this->comment('see a success message after saving the category');

   	$this->see('Category saved', '#system-message-container');


   	//Genres
   	$this->comment('Category MyMuse creation in /administrator/ ');
   	$this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->comment('Click new category button');
   	$this->click($this->locator->adminToolbarButtonNew);

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->fillField(array('id' => 'jform_title'), 'Genres');
   	//choose the parent
   	$this->click(array('css' => 'a.chzn-single > span'));
   	$this->click(array('xpath' => "//div[@id='jform_parent_id_chzn']/div/ul/li[2]"));

   	$this->comment('Click new category apply button');
   	$this->click($this->locator->adminToolbarButtonApply);

   	$this->comment('see a success message after saving the category');

   	$this->see('Category saved', '#system-message-container');


   	//Iron Brew
   	$this->comment('Category MyMuse creation in /administrator/ ');
   	$this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->comment('Click new category button');
   	$this->click($this->locator->adminToolbarButtonNew);

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->fillField(array('id' => 'jform_title'), 'Iron Brew');
   	//choose the parent
   	$this->click(array('css' => 'a.chzn-single > span'));
   	$this->click(array('xpath' => "//div[@id='jform_parent_id_chzn']/div/ul/li[3]"));
   	$this->comment('Click new category apply button');
   	$this->click($this->locator->adminToolbarButtonApply);

   	$this->comment('see a success message after saving the category');

   	$this->see('Category saved', '#system-message-container');


   	//World Beat
   	$this->comment('Category MyMuse creation in /administrator/ ');
   	$this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->comment('Click new category button');
   	$this->click($this->locator->adminToolbarButtonNew);

   	$this->waitForElement(array('class' => 'page-title'));

   	$this->fillField(array('id' => 'jform_title'), 'World Beat');
   	//choose the parent
   	$this->click(array('css' => 'a.chzn-single > span'));
   	$this->click(array('xpath' => "//div[@id='jform_parent_id_chzn']/div/ul/li[5]"));
   	$this->comment('Click new category apply button');
   	$this->click($this->locator->adminToolbarButtonApply);

   	$this->comment('see a success message after saving the category');

   	$this->see('Category saved', '#system-message-container');

   }


 

   /**
    * Create mymuse product
    * @param object mock : object of a menu 
    * @param object config : the current config
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
   public function createMymuseProduct($mock)
   {
    $this->locator = new Locators;

    $this->comment('Product creation in /administrator/ ');
    $this->amOnPage('administrator/index.php?option=com_mymuse&view=products');
    $this->waitForElement(array('class' => 'page-title'));

    $this->comment('Click new product button');
    $this->click($this->locator->adminToolbarButtonNew);
    $this->waitForElement(array('class' => 'page-title'));

    $this->comment('Fill in image fields');
    $this->waitForElement(array('css' => '#myTabTabs > li:nth-child(2)'));
    $this->click('Images');
    $this->waitForElement(array('id' => 'jform_list_image'));
    $this->executeJS("document.getElementById('jform_list_image').removeAttribute('readonly');");
    $this->fillField(array('id' => 'jform_list_image'), $mock->jform_list_image);

    $this->executeJS("document.getElementById('jform_detail_image').removeAttribute('readonly');");
    $this->fillField(array('id' => 'jform_detail_image'), $mock->jform_detail_image);

    if(isset($mock->jform_product_images) && $mock->jform_product_images != ''){
      $this->selectOptionInChosenById('jform_product_images', $mock->jform_product_images);
    }

    $this->comment('Fill in recording details fields');
    $this->click('Recording Details');
    $this->fillField(array('id' => 'jform_product_made_date'), $mock->jform_product_made_date);
    $this->fillField(array('id' => 'jform_product_full_time'), $mock->jform_product_full_time);

    if(isset($mock->jform_product_country) && $mock->jform_product_country != ''){
      $this->click(["css" => "#jform_product_country_chzn > a.chzn-single > span"]);
      $this->click(["xpath" => "//div[@id='jform_product_country_chzn']/div/ul/li[$mock->jform_product_country]"]);
    }
    

    $this->fillField(array('id' => 'jform_product_publisher'), $mock->jform_product_publisher);
    $this->fillField(array('id' => 'jform_product_producer'), $mock->jform_product_producer);
    $this->fillField(array('id' => 'jform_product_studio'), $mock->jform_product_studio);

    $this->comment('Fill in Dimensions fields');
    $this->click('Dimensions');
    $this->fillField(array('id' => 'jform_product_weight'), $mock->jform_product_weight);
    $this->fillField(array('id' => 'jform_product_length'), $mock->jform_product_length);
    $this->fillField(array('id' => 'jform_product_width'), $mock->jform_product_width);
    $this->fillField(array('id' => 'jform_product_height'), $mock->jform_product_height);


    $this->comment('Fill in Details fields');
    $this->click('Details');
    $this->fillField(array('id' => 'jform_title'), $mock->jform_title);

    $this->selectOptionInChosenById('jform_artistid', $mock->jform_artist);
    $this->selectOptionInChosenById('jform_catid', $mock->jform_cat);


    $this->fillField(array('id' => 'jform_product_in_stock'), $mock->jform_product_in_stock);
    

    if(isset($mock->jform_product_physical)){
      $this->selectOptionInChosenById('jform_product_physical', $mock->jform_product_physical);
    }

    if(isset($mock->jform_attribs_product_price_physical)){
      $this->fillField(array('id' => 'jform_attribs_product_price_physical'), $mock->jform_attribs_product_price_physical);
    }else{
      $this->fillField(array('id' => 'jform_price'), $mock->jform_price);
    }

    if(isset($mock->jform_attribs_product_price_mp3)){
      $this->fillField(array('id' => 'jform_attribs_product_price_mp3'), $mock->jform_attribs_product_price_mp3);
    }
    if(isset($mock->jform_attribs_product_price_mp3_all)){
      $this->fillField(array('id' => 'jform_attribs_product_price_mp3_all'), $mock->jform_attribs_product_price_mp3_all);
    }
    if(isset($mock->jform_attribs_product_price_wav)){
      $this->fillField(array('id' => 'jform_attribs_product_price_wav'), $mock->jform_attribs_product_price_wav);
    }
    if(isset($mock->jform_attribs_product_price_wav_all)){
      $this->fillField(array('id' => 'jform_attribs_product_price_wav_all'), $mock->jform_attribs_product_price_wav_all);
    }
    

    
    $this->waitForElement(array('id' => 'jform_articletext_ifr'));
    $editor_frame_name = 'articletext-frame';
    $this->executeJS("document.getElementById('jform_articletext_ifr').setAttribute('name', '$editor_frame_name');");
    $this->switchToIFrame($editor_frame_name);
    $this->executeJS("document.getElementById('tinymce').innerHTML = \"$mock->jform_articletext\"");


    $this->switchToIFrame();
    
    $this->comment('Click Apply button to save');
    $this->click($this->locator->adminToolbarButtonApply);

    $this->see('saved');
    $id = $this->grabValueFrom('input[id=jform_id]');
    $this->comment('Created product with id '.$id);
    return $id;
  }


   /**
    * Edit MyMuse Product
    * @param object mock : object of a product
    * @param object config : the current config
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
   public function editMymuseProduct($mock)
   {
    $this->locator = new Locators;

    $this->comment('Product editing in /administrator/ ');
    $this->amOnPage('administrator/index.php?option=com_mymuse&view=product&id='.$mock->id);
    $this->waitForElement(array('class' => 'page-title'));


    $this->comment('Fill in image fields');
    $this->waitForElement(array('css' => '#myTabTabs > li:nth-child(2)'));
    $this->click('Images');
    $this->waitForElement(array('id' => 'jform_list_image'));
    $this->executeJS("document.getElementById('jform_list_image').removeAttribute('readonly');");
    $this->fillField(array('id' => 'jform_list_image'), $mock->jform_list_image);

    $this->executeJS("document.getElementById('jform_detail_image').removeAttribute('readonly');");
    $this->fillField(array('id' => 'jform_detail_image'), $mock->jform_detail_image);

    if(isset($mock->jform_product_images) && $mock->jform_product_images != ''){
      $this->selectOptionInChosenById('jform_product_images', $mock->jform_product_images);
    }

    $this->comment('Fill in recording details fields');
    $this->click('Recording Details');
    $this->fillField(array('id' => 'jform_product_made_date'), $mock->jform_product_made_date);
    $this->fillField(array('id' => 'jform_product_full_time'), $mock->jform_product_full_time);

    if(isset($mock->jform_product_country) && $mock->jform_product_country != ''){
      $this->click(["css" => "#jform_product_country_chzn > a.chzn-single > span"]);
      $this->click(["xpath" => "//div[@id='jform_product_country_chzn']/div/ul/li[$mock->jform_product_country]"]);
    }
    

    $this->fillField(array('id' => 'jform_product_publisher'), $mock->jform_product_publisher);
    $this->fillField(array('id' => 'jform_product_producer'), $mock->jform_product_producer);
    $this->fillField(array('id' => 'jform_product_studio'), $mock->jform_product_studio);

    $this->comment('Fill in Dimensions fields');
    $this->click('Dimensions');
    $this->fillField(array('id' => 'jform_product_weight'), $mock->jform_product_weight);
    $this->fillField(array('id' => 'jform_product_length'), $mock->jform_product_length);
    $this->fillField(array('id' => 'jform_product_width'), $mock->jform_product_width);
    $this->fillField(array('id' => 'jform_product_height'), $mock->jform_product_height);


    $this->comment('Fill in Details fields');
    $this->click('Details');
    $this->fillField(array('id' => 'jform_title'), $mock->jform_title);

    $this->selectOptionInChosenById('jform_artistid', $mock->jform_artist);
    $this->selectOptionInChosenById('jform_catid', $mock->jform_cat);


    $this->fillField(array('id' => 'jform_product_in_stock'), $mock->jform_product_in_stock);
    

    if(isset($mock->jform_product_physical)){
      $this->selectOptionInChosenById('jform_product_physical', $mock->jform_product_physical);
    }

    if(isset($mock->jform_attribs_product_price_physical)){
      $this->fillField(array('id' => 'jform_attribs_product_price_physical'), $mock->jform_attribs_product_price_physical);
    }else{
      $this->fillField(array('id' => 'jform_price'), $mock->jform_price);
    }

    if(isset($mock->jform_attribs_product_price_mp3)){
      $this->fillField(array('id' => 'jform_attribs_product_price_mp3'), $mock->jform_attribs_product_price_mp3);
    }
    if(isset($mock->jform_attribs_product_price_mp3_all)){
      $this->fillField(array('id' => 'jform_attribs_product_price_mp3_all'), $mock->jform_attribs_product_price_mp3_all);
    }
    if(isset($mock->jform_attribs_product_price_wav)){
      $this->fillField(array('id' => 'jform_attribs_product_price_wav'), $mock->jform_attribs_product_price_wav);
    }
    if(isset($mock->jform_attribs_product_price_wav_all)){
      $this->fillField(array('id' => 'jform_attribs_product_price_wav_all'), $mock->jform_attribs_product_price_wav_all);
    }

    if(isset($mock->jform_attribs_special_status)){
      $this->selectOptionInChosenById('jform_attribs_special_status', $mock->jform_attribs_special_status);
    }
    

    
    $this->waitForElement(array('id' => 'jform_articletext_ifr'));
    $editor_frame_name = 'articletext-frame';
    $this->executeJS("document.getElementById('jform_articletext_ifr').setAttribute('name', '$editor_frame_name');");
    $this->switchToIFrame($editor_frame_name);
    $this->executeJS("document.getElementById('tinymce').innerHTML = \"$mock->jform_articletext\"");


    $this->switchToIFrame();
    
    $this->comment('Click Apply button to save');
    $this->click($this->locator->adminToolbarButtonApply);

    $this->see('saved');
    $this->comment('Edited product with id '.$mock->id);

  }


  /**
   * Edit MyMuse Product Field
   * @param object mock : object of a product field
   * @param object config : the current config
   * @return  void
   *
   * @since   3.7.5
   * @throws  \Exception
   */
  public function editMymuseProductField($mock)
  {
   $this->locator = new Locators;

   $this->comment('Product edit Field in /administrator/ ');
   $this->amOnPage('administrator/index.php?option=com_mymuse&view=product&id='.$mock->id);
   $this->waitForElement(array('class' => 'page-title'));

   $this->click($mock->tab);
   foreach($mock->select as $select){
     if($select['type'] == "select"){
       $this->selectOptionInChosenById($select['option'], $select['value']);
     }elseif($select['type'] == "radio"){
       $this->selectOptionInRadioField($select['option'], $select['value']);

     }elseif($select['type'] == "multiSelect"){
       $this->selectMultipleOptionsInChosen($select['option'], $select['value']);
       $this->wait(1); 
       $this->click($mock->tab);
     }elseif($select['type'] == "text"){
       $this->fillField(array('id' => $select['option']), $select['value']);

     }   
   }
   $this->comment('Click Apply button to save');
   $this->click($this->locator->adminToolbarButtonApply);

   $this->see('saved');

   $this->comment('Edited product with id '.$mock->id);

 }



   /**
    * Create mymuse product tracks
    * @param object mock : object of a track 
    * @param object config : the current config
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
   public function createMymuseTrack($mock, $config)
   {
      $this->locator = new Locators;
      $joomla_folder = dirname(dirname(dirname(__FILE__)));
      $this->comment('joomla_folder = '.$joomla_folder);

      //move some tracks in there
      $from_dir     = $joomla_folder.'/mymuse-downloads';
      $download_dir = $joomla_folder.$config['my_download_dir'];
      $preview_dir  = $joomla_folder.$config['my_preview_dir'];
      if($config['my_download_dir_format'] == "0"){
          $download_dir .= '/'.$mock->artist_alias.'/'.$mock->product_alias;
          $preview_dir .= '/'.$mock->artist_alias.'/'.$mock->product_alias;
      }
      $this->comment('download dir = '.$download_dir);
      $this->comment('preview dir = '.$preview_dir);

      if(!file_exists($preview_dir.'/are-you-my-sister-preview.mp3')){
        copy($from_dir.'/are-you-my-sister-preview.mp3', $preview_dir.'/are-you-my-sister-preview.mp3');
      }
      if(!file_exists($preview_dir.'/the-foggy-dew-preview.mp3')){
        copy($from_dir.'/the-foggy-dew-preview.mp3', $preview_dir.'/the-foggy-dew-preview.mp3');
      }

      
      if($config['my_download_dir_format'] == "1"){
        if(!file_exists($download_dir.'/are-you-my-sister.wav')){
        copy($from_dir.'/are-you-my-sister.wav', $download_dir.'/wav/are-you-my-sister.wav');
        }
        if(!file_exists($download_dir.'/the-foggy-dew.wav')){
          copy($from_dir.'/the-foggy-dew.wav', $download_dir.'/wav/the-foggy-dew.wav');
        }
        if(!file_exists($download_dir.'/are-you-my-sister.mp3')){
        copy($from_dir.'/are-you-my-sister.mp3', $download_dir.'/mp3/are-you-my-sister.mp3');
        }
        if(!file_exists($download_dir.'/the-foggy-dew.mp3')){
          copy($from_dir.'/the-foggy-dew.mp3', $download_dir.'/mp3/the-foggy-dew.mp3');
        }
      }else{
        if(!file_exists($download_dir.'/are-you-my-sister.mp3')){
        copy($from_dir.'/are-you-my-sister.mp3', $download_dir.'/are-you-my-sister.mp3');
        }
        if(!file_exists($download_dir.'/the-foggy-dew.mp3')){
          copy($from_dir.'/the-foggy-dew.mp3', $download_dir.'/the-foggy-dew.mp3');
        }
      }


      $this->comment('Track creation in /administrator/ ');
      $this->amOnPage("administrator/index.php?option=com_mymuse&view=product&layout=edit&id=".$mock->id);

      $this->comment('List Tracks');
      $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'product.listracks\');"]']);

      $this->comment('New Track');
      $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'product.addfile\');"]']);

      $this->comment('Fill in Fields');
      $this->fillField(array('id' => 'jform_title'), $mock->jform_title);
      $this->fillField(array('id' => 'jform_product_sku'), $mock->jform_product_sku);

      if($config['my_download_dir_format'] != "1"){
        $this->fillField(array('id' => 'jform_price'), $mock->jform_price);
      }

      $this->comment('Choose Track');
      $this->click('TRACKS');
      $this->selectOptionInChosenById('select_file0', $mock->track);
      if($config['my_download_dir_format'] == "1"){
         $this->selectOptionInChosenById('select_file1', $mock->wav);
      }
      

      $this->comment('Choose Preview');
      $this->click('PREVIEWS');
      $this->selectOptionInChosenById('file_preview', $mock->preview);

      $this->comment('Save');
      $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'product.savefile\');"]']);

      $this->see('File saved');

    }

   /**
    * Create mymuse all track
    * @param object mock : object of a all track 
    * @param object config : the current config
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
    public function createMymuseAllTrack($mock, $config)
    {
        $this->comment('ALL Track creation in /administrator/ ');
        $this->amOnPage("administrator/index.php?option=com_mymuse&view=product&layout=edit&id=".$mock->id);

        $this->comment('List Tracks');
        $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'product.listracks\');"]']);

        $this->comment('New Track');
        $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'product.new_allfiles\');"]']);

        $this->comment('Fill in Fields');
        $this->fillField(array('id' => 'jform_title'), $mock->jform_title);
        $this->fillField(array('id' => 'jform_alias'), $mock->jform_alias);
        $this->fillField(array('id' => 'jform_product_sku'), $mock->jform_product_sku);

        if($config['my_download_dir_format'] != "1"){
          $this->fillField(array('id' => 'jform_price'), $mock->jform_price);
        }
        if(isset($mock->jform_product_discount)){
          $this->fillField(array('id' => 'jform_product_discount'), $mock->jform_product_discount);
        }
        $this->comment('Save');
        $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'product.save_allfiles\');"]']);

        $this->see('All File saved');

    }

    /**
    * Create mymuse product items
    * @param object mock : object of an item 
    * @param object config : the current config 
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
   public function createMymuseItems($mock, $config)
   {
      $this->locator = new Locators;


      $this->comment('Item creation in /administrator/ for itemid '.$mock->id);
      $this->amOnPage("administrator/index.php?option=com_mymuse&view=product&layout=edit&id=".$mock->id);

      $this->comment('List Items');
      $this->click(["xpath" => '//button[@onclick="Joomla.submitbutton(\'product.listitems\');"]']);


      //$this->comment('Check attributes '.print_r($mock->jform_attribute, true));
      if(isset($mock->jform_attribute) && is_array($mock->jform_attribute)){
        $att_count = count($mock->jform_attribute);
        foreach($mock->jform_attribute as &$attr){
          $this->comment('New Attribute '.$attr['name']);
          $this->click(["css" => '#toolbar-new > .btn']);
          $this->wait(1);
          $this->fillField(array('id' => 'jform_name'), $attr['name']);
          $this->fillField(array('id' => 'jform_extra_base'), $attr['extra_base']);
          $this->fillField(array('id' => 'jform_extra_css'), $attr['extra_css']);
          $this->click(['xpath' => '//button[@onclick="Joomla.submitbutton(\'productattributesku.save\');"]']);
          $this->wait(1);
          $this->see('Item successfully saved');
        }
        $this->click(['xpath' => '//button[@onclick="Joomla.submitbutton(\'productattributesku.return\');"]']);
      }
      $this->click('Create ITEMS');
      $this->wait(2);
      $this->see('Changes to Item saved');
    }


    /**
    * MakeMenus
    * @param object mock : object of a menu 
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
    public function makeMenus($mock)
    {
      $this->locator = new Locators;
      $this->amOnPage('administrator/index.php?option=com_menus&view=items&menutype=mainmenu');
      
      //$this->comment('mock = '.print_r($mock, true));
      $this->comment('Clear Search');
      $this->click(['css' => '.js-stools-btn-clear']);
      $this->comment('Click new menu button');
      $this->click($this->locator->adminToolbarButtonNew);
      $this->waitForElement(array('class' => 'page-title'));
      $this->fillField(array('id' => 'jform_title'), $mock->jform_title);

      $this->click(['class' =>'btn-primary']);
      $this->switchToIFrame('Menu Item Type');
      $this->wait(1);
      $this->comment('Choose type MyMuse::'.$mock->menu_type);
      $this->click($mock->menu_item_type);
      $this->wait(1);
      $this->click($mock->menu_type);
      $this->switchToIFrame();

      if($mock->jform_request_id_id && $mock->jform_request_id_name){
        $this->comment('Select Product '.$mock->jform_request_id_name);
        $this->executeJS("document.getElementById('jform_request_id_name').removeAttribute('disabled');");
        $this->fillField(array('id' => 'jform_request_id_name'), $mock->jform_request_id_name);
        $this->executeJS("document.getElementById('jform_request_id_id').value='".$mock->jform_request_id_id."';");
      }

      $this->click($this->locator->adminToolbarButtonApply);
      $this->see('Menu item saved');

      $this->comment('Select Home Page');
      $this->amOnPage('index.php');
      $this->see($mock->jform_title);
      $this->click($mock->jform_title);
      $this->see($mock->jform_request_id_name);
 
   }

   public function createMenuItem2($menuTitle, $menuCategory, $menuItem, $menu = 'Main Menu', $language = 'All')
   {
    $this->comment("I open the menus page");
    $this->amOnPage('administrator/index.php?option=com_menus&view=menus');
    $this->waitForText('Menus', 30, array('css' => 'H1'));
    $this->checkForPhpNoticesOrWarnings();

    $this->comment("I click in the menu: $menu");
    $this->click(array('link' => $menu));
    $this->waitForText('Menus: Items', 30, array('css' => 'H1'));
    $this->checkForPhpNoticesOrWarnings();

    $this->comment("I click new");
    $this->click("New");
    $this->waitForText('Menus: New Item', 30, array('css' => 'h1'));
    $this->checkForPhpNoticesOrWarnings();
    $this->fillField(array('id' => 'jform_title'), $menuTitle);

    $this->comment("Open the menu types iframe");
    $this->click("Select");
    $this->waitForElement(array('id' => 'menuTypeModal'), 30);
    $this->wait(1);
    $this->switchToIFrame("Menu Item Type");

    $this->comment("Open the menu category: $menuCategory");

    // Open the category
    $this->wait(1);
    $this->waitForElement(array('link' => $menuCategory), 30);
    $this->click(array('link' => $menuCategory));

    $this->comment("Choose the menu item type: $menuItem");
    $this->wait(1);
    $this->waitForElement(array('xpath' => "//a[contains(text()[normalize-space()], '$menuItem')]"), 30);
    $this->click(array('xpath' => "//div[@id='collapseTypes']//a[contains(text()[normalize-space()], '$menuItem')]"));
    $this->comment('I switch back to the main window');
    $this->switchToIFrame();
    $this->comment('I leave time to the iframe to close');
    $this->wait(2);
    $this->selectOptionInChosen('Language', $language);
    $this->waitForText('Menus: New Item', '30', array('css' => 'h1'));
    $this->comment('I save the menu');
    $this->click("Save");

    $this->waitForText('Menu item saved', 30, array('id' => 'system-message-container'));
   }
    /**
    * placeIteminCart
    * @param object mock : object of an item
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
    public function placeIteminCart($mock)
    {
        $this->comment('Select Page '.$mock->menu_link);
        //$this->comment('mock = '.print_r($mock, true));
        $this->amOnPage('index.php');
        $this->click($mock->menu_link);
        foreach($mock->select as $select){
          $this->click(['id' => $select]);
          $this->wait(6);       
        }

    }

    /**
    * changeStoreConfig
    * @param object mock : object of an item
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
    public function changeStoreConfig($mock)
    {
        //$this->comment('mock = '.print_r($mock, true));
        $this->amOnPage('administrator/index.php?option=com_mymuse&view=store&layout=edit&id=1');
        $this->waitForElement(array('class' => 'page-title'));
        $this->click($mock->tab);
        foreach($mock->select as $select){
          if($select['type'] == "select"){
            $this->selectOptionInChosenById($select['option'], $select['value']);
          }elseif($select['type'] == "radio"){
            $this->selectOptionInRadioField($select['option'], $select['value']);

          }elseif($select['type'] == "multiSelect"){
            $this->selectMultipleOptionsInChosen($select['option'], $select['value']);
            $this->wait(1); 
            $this->click($mock->tab);
          }elseif($select['type'] == "text"){
            $this->fillField(array('id' => $select['option']), $select['value']);

          }   
        }

        $this->click(['xpath' => '//button[@onclick="Joomla.submitbutton(\'store.save\');"]']);
        $this->see('Item successfully saved');
    }

    /**
    * changeGlobalOptions
    * @param object mock : object of an item
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
    public function changeGlobalOptions($mock)
    {

        //$this->comment('mock = '.print_r($mock, true));
        $this->amOnPage('administrator/index.php?option=com_config&view=component&component='.$mock->component);
        $this->waitForElement(array('class' => 'page-title'));
        $this->click($mock->tab);
        foreach($mock->select as $select){
          if($select['type'] == "select"){
            $this->selectOptionInChosenById($select['option'], $select['value']);
            $this->wait(1);   
          }elseif($select['type'] == "radio"){
            $this->selectOptionInRadioField($select['option'], $select['value']);
            $this->wait(1); 
          }

        }
        $this->click(['xpath' => '//button[@onclick="Joomla.submitbutton(\'config.save.component.save\');"]']);
        //$this->see('Configuration saved');

    }

    /**
    * fillFullRegForm
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
    public function fillFullRegForm($mock_user)
    {
      $this->amOnPage('index.php/edit-profile?view=registration');
      $this->fillField(array('id' => 'jform_name'), $mock_user->jform_user);
      $this->fillField(array('id' => 'jform_username'), $mock_user->jform_username);
      $this->fillField(array('id' => 'jform_password1'), $mock_user->jform_password1);
      $this->fillField(array('id' => 'jform_password2'), $mock_user->jform_password2);
      $this->fillField(array('id' => 'jform_email1'), $mock_user->jform_email1);
      $this->fillField(array('id' => 'jform_email2'), $mock_user->jform_email2);
      $this->fillField(array('id' => 'jform_profile_address1'), $mock_user->jform_profile_address1);
      $this->fillField(array('id' => 'jform_profile_address2'), $mock_user->jform_profile_address2);
      $this->fillField(array('id' => 'jform_profile_city'), $mock_user->jform_profile_city);
      $this->fillField(array('id' => 'jform_profile_postal_code'), $mock_user->jform_profile_postal_code);
      $this->fillField(array('id' => 'jform_profile_phone'), $mock_user->jform_profile_phone);
      $this->fillField(array('id' => 'jform_profile_mobile'), $mock_user->jform_profile_mobile);
      $this->selectOptionInChosenByIdUsingJs('jform_profile_country', $mock_user->jform_profile_country); 
      $this->wait(1);
      $this->selectOptionInChosenByIdUsingJs('jform_profile_region', $mock_user->jform_profile_region);
      $this->wait(1);
      $this->click('Register');
      

    }


    /**
    * fillNoRegForm
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
    public function fillNoRegForm($mock_user)
    {
      $this->fillField(array('id' => 'jform_profile_first_name'), $mock_user->jform_profile_first_name);
      $this->fillField(array('id' => 'jform_profile_last_name'), $mock_user->jform_profile_last_name);
      $this->fillField(array('id' => 'jform_profile_email'), $mock_user->jform_profile_email);
      //$this->fillField(array('id' => 'jform_profile_address1'), $mock_user->jform_profile_address1);
      //$this->fillField(array('id' => 'jform_profile_address2'), $mock_user->jform_profile_address2);
      //jform_profile_country
      //jform_profile_region
      $this->click('Save');

    }










   /* ########################## clear function ############################### */

    /**
     * Clear menus
     *
     * @return  void
     *
     * @since   3.7.5
     * @throws  \Exception
     */
    public function clearMenus()
    {
      $this->locator = new Locators;
      $this->comment('Clear MyMuse Menus ');
      $this->amOnPage('administrator/index.php?option=com_menus&view=items&menutype=mainmenu');
      $this->waitForElement(array('class' => 'page-title'));


      $this->comment('Check All');
      $this->click(["xpath" => "//input[@name='checkall-toggle']"]);
      //unclick home
      $this->click(["css" => "#cb0"]);


      $this->comment('Click on Trash button ');
      $this->click(["xpath" => "//div[@id='toolbar-trash']/button"]);


      $this->comment('Open Search Tools ');
      $this->click(["css" => ".js-stools-btn-filter"]);
      $this->wait(2);
      
      $this->executeJS("filters=document.getElementsByClassName('js-stools-container-filters');filters[0].style.display = 'block';");

      $this->executeJS("document.getElementById('filter_published').style.display = 'block';");


      $this->comment('Select Status Trashed ');
      $this->selectOptionInChosenById('filter_published', 'Trashed');

      $this->waitForElement(array("name" => "checkall-toggle"));

      $this->comment('Check All Trashed Items');
      $this->click(["name" => "checkall-toggle"]);

      $this->comment('Empty Trash ');
      $this->click(["css" => "#toolbar-delete > button"]);

      $this->acceptPopup();
      $this->waitForText('deleted', '30', array('id' => 'system-message-container'));


    }
    /**
     * Clear trashed menus
     *
     * @return  void
     *
     * @since   3.7.5
     * @throws  \Exception
     */
    public function clearTrashedMenus()
    {
      $this->locator = new Locators;
      $this->comment('Clear MyMuse Trashed Menus ');
      $this->amOnPage('administrator/index.php?option=com_menus&view=items&menutype=mainmenu');
      $this->waitForElement(array('class' => 'page-title'));


      $this->comment('Open Search Tools ');
      $this->click(["css" => ".js-stools-btn-filter"]);
      $this->wait(2);
      
      $this->executeJS("filters=document.getElementsByClassName('js-stools-container-filters');filters[0].style.display = 'block';");

      $this->executeJS("document.getElementById('filter_published').style.display = 'block';");


      $this->comment('Select Status Trashed ');
      $this->selectOptionInChosenById('filter_published', 'Trashed');

      if($this->seePageHasText("Single")){
        $this->waitForElement(array("name" => "checkall-toggle"));

        $this->comment('Check All Trashed Items');
        $this->click(["name" => "checkall-toggle"]);

        $this->comment('Empty Trash ');
        $this->click(["css" => "#toolbar-delete > button"]);

        $this->acceptPopup();
        $this->waitForText('deleted', '30', array('id' => 'system-message-container'));
      }


    }
    /**
     * Clear users
     *
     * @return  void
     *
     * @since   3.7.5
     * @throws  \Exception
     */
    public function clearUsers()
    {
      $this->locator = new Locators;
      $this->comment('Clear MyMuse Users ');
      $this->amOnPage('administrator/index.php?option=com_users&view=users');
      $this->waitForElement(array('class' => 'page-title'));

      $this->searchForItem("Test User");
      if($this->seePageHasText("Test user")){
        $this->click(['id' => 'cb0']);
        $this->click(['xpath' => '//button[contains(@onclick, "Joomla.submitbutton(\'users.delete\');")]' ]);

        $this->acceptPopup();
        $this->see('1 user deleted');
      }
      $this->searchForItem("Buyer");
      if($this->seePageHasText("Buyer")){
        $this->click(['id' => 'cb0']);
        $this->click(['xpath' => '//button[contains(@onclick, "Joomla.submitbutton(\'users.delete\');")]' ]);

        $this->acceptPopup();
        $this->see('1 user deleted');
      }

      
    }

    /**
     * Clear mymuse categories
     *
     * @return  void
     *
     * @since   3.7.5
     * @throws  \Exception
     */
    public function clearMymuseCategories()
    {
      $this->locator = new Locators;

      $this->comment('Clear Categories  in /administrator/ ');
      $this->amOnPage('administrator/index.php?option=com_categories&extension=com_mymuse');
      $this->waitForElement(array('class' => 'page-title'));

      $this->comment('Check All');
      $this->click(["xpath" => "//input[@name='checkall-toggle']"]);

      $this->comment('Click on Trash button ');
      $this->click(["xpath" => "//div[@id='toolbar-trash']/button"]);


      $this->comment('Open Search Tools ');
      $this->click(["css" => ".js-stools-btn-filter"]);
      $this->wait(2);
      
      $this->executeJS("filters=document.getElementsByClassName('js-stools-container-filters');filters[0].style.display = 'block';");

      $this->executeJS("document.getElementById('filter_published').style.display = 'block';");
      $this->selectOptionInChosenById('filter_published', 'Trashed');


      $this->waitForElement(array("name" => "checkall-toggle"));

      $this->comment('Check All Trashed Items');
      $this->click(["name" => "checkall-toggle"]);

      $this->comment('Empty Trash ');
      $this->click(["css" => "#toolbar-delete > button"]);

      $this->acceptPopup();
      $this->waitForText('deleted', '30', array('id' => 'system-message-container'));

    }


      /**
    * Clear mymuse products
    *
    * @return  void
    *
    * @since   3.7.5
    * @throws  \Exception
    */
   public function clearMymuseProducts()
   {
    $this->locator = new Locators;

    $this->comment('Clear products  in /administrator/ ');
    $this->amOnPage('administrator/index.php?option=com_mymuse&view=products');
    $this->waitForElement(["xpath" => "//input[@name='checkall-toggle']"]);

    $this->comment('Check All');
    $this->click(["xpath" => "//input[@name='checkall-toggle']"]);

    $this->comment('Click on Trash button ');
    $this->click(["xpath" => "//div[@id='toolbar-trash']/button"]);
    $this->see('successfully trashed');

    $this->comment('Open Search Tools ');
    $this->click(["css" => ".js-stools-btn-filter"]);
    $this->wait(2);
    
    $this->executeJS("filters=document.getElementsByClassName('js-stools-container-filters');filters[0].style.display = 'block';");

    $this->executeJS("document.getElementById('filter_published').style.display = 'block';");
    $this->selectOptionInChosenById('filter_published', 'Trashed');


    $this->click(["css" => 'input[name="checkall-toggle"]']);
    $this->click(["css" => '.button-delete']);

   }
}

