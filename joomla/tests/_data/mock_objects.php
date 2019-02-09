<?php
        $this->mock_cd                                          = new stdClass;
        $this->mock_cd->jform_title                             = "Are You My Sister";
        $this->mock_cd->jform_alias                             = "are-you-my-sister";
        $this->mock_cd->jform_product_in_stock                  = "";
        $this->mock_cd->jform_price                             = "";
        $this->mock_cd->jform_artist                            = "- - Iron Brew";
        $this->mock_cd->jform_cat                               = "- - World Beat";
        $this->mock_cd->jform_product_sku                       = "IronBrew01";
        $this->mock_cd->jform_product_physical                  = "No";
        $this->mock_cd->jform_list_image                        = "images/mymuse/sister.jpg";
        $this->mock_cd->jform_detail_image                      = "images/mymuse/sister.jpg";
        $this->mock_cd->jform_product_made_date                 = "2018-11-28";
        $this->mock_cd->jform_product_full_time                 = "45:10";
        $this->mock_cd->jform_product_publisher                 = "Iron Filings";
        $this->mock_cd->jform_product_producer                  = "Gord Fisch";
        $this->mock_cd->jform_product_country                   = "38"; 
        $this->mock_cd->jform_product_studio                    = "Tanglewood";
        $this->mock_cd->jform_product_weight                    = "";
        $this->mock_cd->jform_product_length                    = "";
        $this->mock_cd->jform_product_width                     = "";
        $this->mock_cd->jform_product_height                    = "";
        $this->mock_cd->jform_attribs['media_rls']              = "";
        $this->mock_cd->jform_attribs['media_link']             = "";
        $this->mock_cd->jform_articletext                       = '<p>The great first album</p>';

        $this->mock_track = new stdClass;
        $this->mock_track->jform_title                          = "Are You My Sister Song";
        $this->mock_track->jform_product_in_stock               = "";
        $this->mock_track->jform_price                          = "2.00";
        $this->mock_track->jform_artist                         = "- - Iron Brew";
        $this->mock_track->jform_cati                           = "- - World Beat";
        $this->mock_track->jform_product_sku                    = "IronBrew01-Track1";
        $this->mock_track->jform_product_physical               = "No";
        $this->mock_track->jform_list_image                     = "";
        $this->mock_track->jform_detail_image                   = "";
        $this->mock_track->jform_product_made_date              = "2018-11-28";
        $this->mock_track->jform_product_full_time              = "3:15";
        $this->mock_track->jform_product_publisher              = "Iron Filings";
        $this->mock_track->jform_product_producer               = "Gord Fisch";
        $this->mock_track->jform_product_country                = "38"; 
        $this->mock_track->jform_product_studio                 = "Tanglewood";
        $this->mock_track->jform_product_weight                 = "";
        $this->mock_track->jform_product_length                 = "";
        $this->mock_track->jform_product_width                  = "";
        $this->mock_track->jform_product_height                 = "";
        $this->mock_track->jform_attribs['media_rls']           = "";
        $this->mock_track->jform_attribs['media_link']          = "";
        $this->mock_track->jform_articletext                    = '';
        $this->mock_track->product_alias                        = 'are-you-my-sister';
        $this->mock_track->artist_alias                         = 'iron-brew';
        $this->mock_track->mp3                                = 'are-you-my-sister.mp3';
        $this->mock_track->wav                                   = 'are-you-my-sister.wav';
        $this->mock_track->preview                              = 'are-you-my-sister-preview.mp3';


        $this->mock_track1 = new stdClass;
        $this->mock_track1->jform_title                          = "The Foggy Dew";
        $this->mock_track1->jform_product_in_stock               = "";
        $this->mock_track1->jform_price                          = "2.00";
        $this->mock_track1->jform_artist                         = "- - Iron Brew";
        $this->mock_track1->jform_cati                           = "- - World Beat";
        $this->mock_track1->jform_product_sku                    = "IronBrew01-Track2";
        $this->mock_track1->jform_product_physical               = "No";
        $this->mock_track1->jform_list_image                     = "";
        $this->mock_track1->jform_detail_image                   = "";
        $this->mock_track1->jform_product_made_date              = "2018-11-28";
        $this->mock_track1->jform_product_full_time              = "4:10";
        $this->mock_track1->jform_product_publisher              = "Iron Filings";
        $this->mock_track1->jform_product_producer               = "Gord Fisch";
        $this->mock_track1->jform_product_country                = "38"; 
        $this->mock_track1->jform_product_studio                 = "Tanglewood";
        $this->mock_track1->jform_product_weight                 = "";
        $this->mock_track1->jform_product_length                 = "";
        $this->mock_track1->jform_product_width                  = "";
        $this->mock_track1->jform_product_height                 = "";
        $this->mock_track1->jform_attribs['media_rls']           = "";
        $this->mock_track1->jform_attribs['media_link']          = "";
        $this->mock_track1->jform_articletext                    = '';
        $this->mock_track1->product_alias                        = 'are-you-my-sister';
        $this->mock_track1->artist_alias                         = 'iron-brew';
        $this->mock_track1->mp3                                = 'the-foggy-dew.mp3';
        $this->mock_track1->wav                                  = 'the-foggy-dew.wav';
        $this->mock_track1->preview                              = 'the-foggy-dew-preview.mp3';

        //all track
        $this->mock_all_track                                   = new stdClass;
        $this->mock_all_track->jform_title                      = "Sister All Tracks";
        $this->mock_all_track->jform_alias                      = "sister-all-tracks";
        $this->mock_all_track->jform_product_sku                = "AYMS-all";


        //for single menu
        $this->mock_single_menu                                 = new stdClass;
        $this->mock_single_menu->jform_title                    = 'Single';
        $this->mock_single_menu->menu_item_type                 = 'MyMuse';
        $this->mock_single_menu->menu_type                      = 'Single Product';
        $this->mock_single_menu->jform_request_id_name          = 'Single';
        $this->mock_single_menu->jform_request_id_id            = '';


        //for cart menu
        $this->mock_cart_menu                                   = new stdClass;
        $this->mock_cart_menu->jform_title                      = 'My Cart';
        $this->mock_cart_menu->menu_item_type                   = 'MyMuse';
        $this->mock_cart_menu->menu_type                        = 'Shopping Cart';
        $this->mock_cart_menu->jform_request_id_name            = 'Shopping Cart';
        $this->mock_cart_menu->jform_request_id_id              = '';

        //for list my orders menu
        $this->mock_list_orders_menu                             = new stdClass;
        $this->mock_list_orders_menu->jform_title                = 'List My Orders';
        $this->mock_list_orders_menu->menu_item_type             = 'MyMuse';
        $this->mock_list_orders_menu->menu_type                  = 'List My Orders';
        $this->mock_list_orders_menu->jform_request_id_name      = 'List My Orders';
        $this->mock_list_orders_menu->jform_request_id_id        = '';

        //for edit profile menu
        $this->mock_list_orders_menu                             = new stdClass;
        $this->mock_list_orders_menu->jform_title                = 'Edit Profile';
        $this->mock_list_orders_menu->menu_item_type             = 'Users';
        $this->mock_list_orders_menu->menu_type                  = 'Edit User Profile';
        $this->mock_list_orders_menu->jform_request_id_name      = 'Edit User Profile';
        $this->mock_list_orders_menu->jform_request_id_id        = '';

        //for order cd
        $this->mock_order_cd                                    = new stdClass;
        $this->mock_order_cd->menu_link                         = 'Single';
        $this->mock_order_cd->select[]                          = 'box_1';

        //for order track
        $this->mock_order_track                                 = new stdClass;
        $this->mock_order_track->menu_link                      = 'Single';
        $this->mock_order_track->select[]                       = 'box_2';

        //for order track1
        $this->mock_order_track1                                 = new stdClass;
        $this->mock_order_track1->menu_link                      = 'Single';
        $this->mock_order_track1->select[0]                       = 'box_3';

        //for order all tracks
        $this->mock_order_all_track                             = new stdClass;
        $this->mock_order_all_track->menu_link                  = 'Single';
        $this->mock_order_all_track->select[]                   = 'box_4';


        //Defaults Joomla Registration to go back to
        $this->mock_user_default_config                         = new StdClass;
        $this->mock_user_default_config->component              = "com_users";
        $this->mock_user_default_config->tab                    = "User Options";
        $this->mock_user_default_config->select[0]['type']      = "radio";
        $this->mock_user_default_config->select[0]['option']    = "Allow User Registration";
        $this->mock_user_default_config->select[0]['value']     = "No";
        $this->mock_user_default_config->select[1]['type']      = "select";
        $this->mock_user_default_config->select[1]['option']    = "jform_useractivation";
        $this->mock_user_default_config->select[1]['value']     = "Administrator";

        //Joomla user config
        $this->mock_user_config                                 = new StdClass;
        $this->mock_user_config->component                      = "com_users";
        $this->mock_user_config->tab                            = "User Options";

        $this->mock_user_config->select[0]['option']            = "Allow User Registration";
        $this->mock_user_config->select[0]['value']             = "Yes";
        $this->mock_user_config->select[0]['type']              = "radio";

        $this->mock_user_config->select[1]['option']            = "jform_useractivation";
        $this->mock_user_config->select[1]['value']             = "None";
        $this->mock_user_config->select[1]['type']              = "select";

        //Full Reg Config
        $this->mock_regFull_config                              = new StdClass;
        $this->mock_regFull_config->component                   = "com_mymuse";
        $this->mock_regFull_config->tab                         = "Store Options";
        
        $this->mock_regFull_config->select[0]['option']         = "jform_params_my_registration";
        $this->mock_regFull_config->select[0]['value']          = "Full";
        $this->mock_regFull_config->select[0]['type']           = "select";

        //NoReg config
        $this->mock_noReg_config                      	         = new StdClass;
        $this->mock_noReg_config->component           	         = "com_mymuse";
        $this->mock_noReg_config->tab                 	         = "Store Options";
        
        $this->mock_noReg_config->select[0]['option'] 	         = "jform_params_my_registration";
        $this->mock_noReg_config->select[0]['value']  	         = "No Registration";
        $this->mock_noReg_config->select[0]['type']              = "select";

        //Delay config
        $this->mock_delay_config                              = new StdClass;
        $this->mock_delay_config->component                   = "com_mymuse";
        $this->mock_delay_config->tab                         = "Store Options";
        
        $this->mock_delay_config->select[0]['option']         = "jform_params_my_delay_fadeout";
        $this->mock_delay_config->select[0]['value']          = "4000";
        $this->mock_delay_config->select[0]['type']           = "text";

        $this->mock_module_config                                 = new StdClass;
        $this->mock_module_config->module                         = "MyMuse Latest Module";
        $this->mock_module_config->tab                            = "Module";
        $this->mock_module_config->select[0]['option']            = "jform_params_type_shown";
        $this->mock_module_config->select[0]['value']             = "Albums";
        $this->mock_module_config->select[0]['type']              = "select";
        

        //for edit profile menu
        $this->mock_edit_profile_menu                                 = new stdClass;
        $this->mock_edit_profile_menu->jform_title                    = 'Edit Profile';
        $this->mock_edit_profile_menu->menu_item_type                 = 'Users';
        $this->mock_edit_profile_menu->menu_type                      = 'Edit user Profile';
        $this->mock_edit_profile_menu->jform_request_id_name          = '';
        $this->mock_edit_profile_menu->jform_request_id_id            = '';


        //mock user
        $this->mock_user                                        = new StdClass;
        $this->mock_user->jform_user                            = 'Test User';
        $this->mock_user->jform_username                        = 'Test-User';
        $this->mock_user->jform_password1                       = 'Test User';
        $this->mock_user->jform_password2                       = 'Test User';
        $this->mock_user->jform_email1                          = 'gord@gordfisch.net';
        $this->mock_user->jform_email2                          = 'gord@gordfisch.net';
        $this->mock_user->jform_profile_address1                = '123 4th St.';
        $this->mock_user->jform_profile_address2                = 'Apt. 5';
        $this->mock_user->jform_profile_city                    = 'Montreal';
        $this->mock_user->jform_profile_phone                   = '514-123-1234';
        $this->mock_user->jform_profile_mobile                  = '514-678-1234';
        $this->mock_user->jform_profile_country                 = 'United States';
        $this->mock_user->jform_profile_region                  = 'Vermont';
        $this->mock_user->jform_profile_postal_code             = '05682';
        
        $this->mock_user->jform_profile_first_name              = 'Test';
        $this->mock_user->jform_profile_last_name               = 'User';
        $this->mock_user->jform_profile_email                   = 'gord@gordfisch.net';


        //mock user
        $this->mock_noreg_user                                   = new StdClass;
        $this->mock_noreg_user->jform_user                       = 'NoReg User';
        $this->mock_noreg_user->jform_email1                     = 'gord@gordfisch.net';
        $this->mock_noreg_user->jform_email2                     = 'gord@gordfisch.net';
        $this->mock_noreg_user->jform_profile_address1           = '123 4th St.';
        $this->mock_noreg_user->jform_profile_address2           = 'Apt. 5';
        $this->mock_noreg_user->jform_profile_city               = 'Montreal';
        $this->mock_noreg_user->jform_profile_phone              = '514-123-1234';
        $this->mock_noreg_user->jform_profile_mobile             = '514-678-1234';
        $this->mock_noreg_user->jform_profile_country            = 'United States';
        $this->mock_noreg_user->jform_profile_region             = 'Vermont';
        $this->mock_noreg_user->jform_profile_postal_code        = '05682';
        
        $this->mock_noreg_user->jform_profile_first_name          = 'NoRegFirst';
        $this->mock_noreg_user->jform_profile_last_name           = 'NoRegLast';
        $this->mock_noreg_user->jform_profile_email               = 'gord@gordfisch.net';


        // Edit  a Product
        $this->mock_product_config                                = new StdClass;
        $this->mock_product_config->id                            = "1";
        $this->mock_product_config->tab                           = "Details";
        $this->mock_product_config->select[0]['option']           = "Product in Stock";
        $this->mock_product_config->select[0]['value']            = "5";
        $this->mock_product_config->select[0]['type']             = "text";