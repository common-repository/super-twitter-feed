<?php
/**
 * @package notifwidget
 * @author CitizenNet
 * @version 1.0
 */
/*
Plugin Name: Super Twitter Feed
Plugin URI: http://www.citizennet.com/
Description: Super Twitter Feed, A Tool for realtime social streams, embedding, searching, and ranking twitter.
Author: CitizenNet
Version: 1.0
Author URI: http://www.citizennet.com/
*/

/* Add our function to the widgets_init hook. */
add_action( 'widgets_init', 'notification_widget_load_widgets' );

/* Function that registers our widget. */
function notification_widget_load_widgets() {
	register_widget( 'Notification_Widget' );
	$pluginsettings = get_settings('home');
	$plugindir = $pluginsettings.'/wp-content/plugins/'.dirname(plugin_basename(__FILE__));
	$includesdir = $pluginsettings.'/wp-includes/js/scriptaculous/';
	
	wp_enqueue_script('load_prototype',$includesdir.'prototype.js');
	wp_enqueue_script('load_effects', $includesdir.'effects.js');
	wp_enqueue_script('load_dragdrop', $includesdir.'dragdrop.js');
	wp_enqueue_script('load_widgetjs', $plugindir.'/script/notif_widget_js.js');
	wp_enqueue_script('load_windoweffectsjs', $plugindir.'/script/window_effects.js');
	wp_enqueue_script('load_widgetwindowjs', $plugindir.'/script/window.js');
}

class Notification_Widget extends WP_Widget {
	public $call_back_url;
  public $instance_id;
	public $user_id;
	public $widget_name;
	public $id_base;
	public $plugindir;
	public $referrer;
	public $authkey;
	
	function Notification_Widget() {
		//$this->call_back_url = 'http://192.168.0.106/cncms/index.php/xmlrpc/';
	  $this->call_back_url = 'http://c2dev1.citizennet.com/cncms/web/index.php/xmlrpc/';
	  $this->id_base = 'notification-feed-widget';
		$this->widget_name = "widget_".$this->id_base;
		$this->plugindir = get_settings('home').'/wp-content/plugins/'.dirname(plugin_basename(__FILE__));
	  $this->referrer = urlencode($_SERVER['HTTP_HOST']);
		$this->authkey = md5($this->referrer);
		
		/* Widget settings. */
	  $widget_ops = array( 'classname' => 'Notification_Widget', 'description' => 'Notification widget allows you to optimize the options you receive and display them. ' );

	  /* Widget control settings. */
	  $control_ops = array( 'width' => 949,  'id_base' => $this->id_base );		
		
	  /* Create the widget. */
	  $this->WP_Widget( 'notification-feed-widget', 'Notification Feed', $widget_ops, $control_ops );
		
		/*register user and add new instance */
		$this->registerXmlRpcUser();
  }
	
	function widget( $args, $instance ) {
		 $this->isAlreadyActivated();
		/* Before widget (defined by themes). */
		echo $before_widget;

		/*Title of widget (before and after defined by themes). */
		echo $before_title.$after_title;
			
    // This just echoes the chosen line, we'll position it later
		$includesdir = get_settings('home').'/wp-includes/js/jquery/jquery.js';
		wp_enqueue_script('load_widget_jquery',$includesdir);
		echo $this->getNonIframeCode();
		 
	  /* After widget (defined by themes). */
		echo $after_widget;
	}
	
	
	function update( $new_instance, $old_instance ) {
  }
	
	function form( $instance ) {
		/* Set up some default widget settings. */
		$defaults = array( 'param_width' => '520', 'param_height' => '1000', 'param_colorscheme' => 'light', 'param_highlighttext' => '1985B5', 'param_lighttext' => '999999', 'param_regulartext' => '444444', 'param_separator' => 'EEEEEE', 'param_new' => 'FAF2CE', 'param_accordion_bar' => 'A2C2EA', 'param_person' => 'FFFFFF' );
		$flag = $this->isAlreadyActivated();
		
		$response = $this->getInstanceRemoteData();
	if( $response ){
		//echo "<pre>";
		//print_r($response);
		echo "<link rel='stylesheet' type='text/css' href='".get_option('siteurl').'/wp-content/plugins/'.basename(dirname(__FILE__)).'/css/notif_widget_styles.css'."' />";
    echo "<link rel='stylesheet' type='text/css' href='".get_option('siteurl').'/wp-content/plugins/'.basename(dirname(__FILE__)).'/css/default.css'."' />";
    echo "<link rel='stylesheet' type='text/css' href='".get_option('siteurl').'/wp-content/plugins/'.basename(dirname(__FILE__)).'/css/alphacube.css'."' />";?>
    
    <div class="sidebar-name" style="height:20px;">&nbsp;</div>
    <div class="dark-grey">
      <div class="pl20 pr20 pt10">
        <div class="step_left_panel">      
          <div class="div_edit_result_top" id="tabs_notif_div">
            <div id="div_stream_keywords_link" class="cn_wizard_lb_link_style_selected" onclick="javascript:show_left_box(2);" style="padding-left:9px">Keywords</div>
            <div class="cn_wizard_lb_link_style">|</div>
            <div id="div_stream_controls_link" class="cn_wizard_lb_link_style_attribute_unselect" onclick="javascript:show_left_box(1);">Filters</div>
            <div class="cn_wizard_lb_link_style">|</div>            
            <!--<div id="div_stream_attributes_link" class="cn_wizard_lb_link_style_attribute_unselect2" onclick="javascript:show_left_box(3);">Appearance</div>
            <div class="cn_wizard_lb_link_style">|</div>-->
            <div id="div_stream_options_link" class="cn_wizard_lb_link_style_attribute_unselect3" onclick="javascript:show_left_box(4);">Interaction Options</div>
            <div class="cn_wizard_lb_link_style">|</div>
        		<div id="div_contest_options_link" class="cn_wizard_lb_link_style_attribute_unselect4" onclick="javascript:show_left_box(5);">Contest</div>           
          </div>
          <div class="div_edit_result_repeat">
            <div class="clear1"></div>
            <!--CONTROLS DIV ST-->
            <div id="div_stream_controls" class="div_stream_controls" style="display:none;">
              <!--Topic Box Start-->
              <div class="round-container w318 mb20">
                <div class="fleft round-top-left"></div>
                <div class="fright round-top-right"></div>
                <div class="clear"></div>
                <div>
                  <!--Topic-Relevance Box Start-->
                  <div class="home_page_sub_heading_styles pt5 pl10">Topic relevance</div>
                  <div class="pl20 pt5 pr10">Select a topic you would like to constrain the stream to. Off-topic messages will be removed.</div>
                  <div id="div_topics_selection" class="pt10 pl15 w300">
                    <?php if( isset($response->topics_list) && isset($response->topics_list->topic) && count($response->topics_list->topic) > 0 ) {?>
                      <table cellspacing="5" cellpadding="5" width="100%">
                        <tr>
                          <td>&nbsp;</td>
                          <td align="center" valign="middle">
                            <select id="topics_list" name="topics_list" onchange="javascript:changeThisTopic(this);">		
		                        	<option value="">Select a topic</option>
                              <?php 
															  $topicid = ( isset($response->topic_id) && $response->topic_id!='' ? (int)$response->topic_id : '');																
															  for( $i=0; $i<count($response->topics_list->topic); $i++ ){
																	echo '<option value="'.( $response->topics_list->topic[$i]->id ).'" '.( ( (int)$response->topics_list->topic[$i]->id ) ==$topicid ? 'selected="selected"' : "" ).'>'.$response->topics_list->topic[$i]->name.'</option>';
																}
															?>
                            </select>
                          </td>
                          <td>
                            <img src="<?php echo $this->plugindir.'/images/CN_rotator_v01.gif';?>" alt="CN_rotator_v01" id="loading_topic" class="loading_topic" style="display:none ;" title=""/>
                          </td>
                        </tr>
                      </table>
                    <?php } ?>
                  </div>
                  <!--Noise-Filter Box Start-->
                  <div id="div_noise_filter" class="div_noise_filter" style=" <?php echo ( isset($response->topic_id) && ((int)$response->topic_id)!=0 ? '' : 'display:none' ); ?>">
                    <div class="pt5 pl20 pr10 text-left">Adjust your results' relevance in relation to this topic.</div>
                    <div id="topic_level_div" class="topic_level_div">
                     <script>
										   var topic_level_id = 0;
                      <?php
											if( isset($response->topic_id) && $response->topic_id!='' )
											  echo 'topic_level_id = '.(int)$response->topic_id.';';
											?>
											</script>
											<?php echo $this->getTopicLevelHtml( ( isset($response->topic_level) && $response->topic_level!='' ? (int)$response->topic_level : 0) , ( isset($response->boost) && $response->boost!='' ? (int)$response->boost: 0) );?>
                    </div>
                    <img id="loading_topic_level" class="loading_topic_level" style="display:none;" align="center" src="<?php echo $this->plugindir.'/images/CN_rotator_v01.gif';?>" alt="CN_rotator_v01"  title=""/>                   
                  </div>
                  <!--Noise-Filter Box End-->
                </div>
                <div class="fleft round-bottom-left"></div>
                <div class="fright round-bottom-right"></div>
                <div class="clear"></div>
              </div>
              <!--Topic Box END-->
              <!--Filters Mood Box Start-->
              <div class="round-container w318 mb20">
                <div class="fleft round-top-left"></div>
                <div class="fright round-top-right"></div>
                <div class="clear"></div>
                <div class="mb10">
                  <div class="home_page_sub_heading_styles pt5 pl10">Mood</div>
                  <div class="pl20 pt5 pr10">Only show messages with the following range of moods:</div>
                  <div id="div_mood_filter" class="mt10 mb10">
                    <center>
                      <table>
                        <tr>
                          <td align="center" valign="middle">
                            <select onchange="javascript:moods_slider(this);" id="opt_moods" name="opt_moods">
                              <?php 
															$polarity = ( isset($response->polarity) && $response->polarity!='' ? $response->polarity : '');
															for( $i=0; $i<count($response->moods_list->mood); $i++ ){
                                echo '<option '.( $response->moods_list->mood[$i]->id!='' && trim($response->moods_list->mood[$i]->id)==trim($polarity) ? 'selected="selected"' : '').' value="'.$response->moods_list->mood[$i]->id.'">'.$response->moods_list->mood[$i]->name.'</option>';
                              }?>                             
                            </select>
                          </td>
                          <td><img src="<?php echo $this->plugindir.'/images/CN_rotator_v01.gif';?>" alt="CN_rotator_v01" id="loading_mood" class="loading_mood" style="display:none ;" title=""/></td>
                        </tr>
                      </table>
                    </center>
                  </div>
                </div>
                <div class="fleft round-bottom-left"></div>
                <div class="fright round-bottom-right"></div>
                <div class="clear"></div>
              </div>
              <!--Filters Mood Box End-->
              
              <!--Filter SPAM Box Start-->
              <div id="div__box">
                <div class="round-container w318 mb20">
                  <div class="fleft round-top-left"></div>
                  <div class="fright round-top-right"></div>
                  <div class="clear"></div>
                  <div class="mb10">
                    <div class="home_page_sub_heading_styles pt5 pl10">Other Filters</div>
                    <div class="pl20 pt5 pr10">
                      <table cellpadding="5" cellspacing="5">
                        <tr>
                          <td align="left" valign="top" class="other_filters_checkbox_toppadding"><input <?php echo ( isset($response->nospam) && ((int)$response->nospam) == 1 ? 'checked="checked"' : '');?> type="checkbox" name="chk_spam_messages" id="chk_spam_messages" class="chk_spam_messages"  onclick="updateSpamMessages(this);"/></td>
                          <td align="left" class="other_filters_text_leftpadding">Detect and remove spam messages <span class="cursor whats-this" id="span_whats_this" onclick="showtooltip(0, this);">What's this?</span>
                            <div id="loading_spamfilter" class="loading_spamfilter" style="display: none;"> <img title="" alt="CN_rotator_v01" src="<?php echo $this->plugindir.'/images/CN_rotator_v01.gif';?>"/> </div></td>
                        </tr>
                        <tr>
                          <td align="left" valign="top" class="other_filters_checkbox_toppadding"><input <?php echo ( isset($response->lang) && ( $response->lang) == 'en' ? 'checked="checked"' : '');?> type="checkbox" name="chk_lang_messages" id="chk_lang_messages" class="chk_lang_messages" onclick="updateLangMessages(this);"/></td>
                          <td align="left" class="other_filters_text_leftpadding">Detect and remove messages that are not in English <span id="language_whats_this"  class="cursor whats-this" onclick="showtooltip(1, this);">What's this?</span>
                            <div style="display: none;" id="loading_langfilter" class="loading_langfilter"> <img title="" alt="CN_rotator_v01" src="<?php echo $this->plugindir.'/images/CN_rotator_v01.gif';?>"/> </div></td>
                        </tr>
                        <tr>
                          <td align="left" valign="top" class="other_filters_checkbox_toppadding"><input <?php echo ( isset($response->nodups) && ( (int)$response->nodups) == 1 ? 'checked="checked"' : '');?> type="checkbox" name="chk_dup_messages" id="chk_dup_messages" class="chk_dup_messages" onclick="updateDuplicateMessages(this);"/></td>
                          <td align="left" class="other_filters_text_leftpadding">Remove similar messages from same user <span id="duplicate_whats_this" class="cursor whats-this" title="" onclick="showtooltip(2, this);">What's this?</span>
                            <div style="display: none;" id="loading_dupfilter" class="loading_dupfilter"> <img title="" alt="CN_rotator_v01" src="<?php echo $this->plugindir.'/images/CN_rotator_v01.gif';?>"/> </div></td>
                        </tr>
                        <tr>
                          <td align="left" valign="top" class="other_filters_checkbox_toppadding"><input <?php echo ( isset($response->noprofanity) && ( (int)$response->noprofanity) == 1 ? 'checked="checked"' : '');?> onclick="javascript:changeThisLinkFilter(this);" type="checkbox" name="chk_link_messages" id="chk_link_messages" class="chk_link_messages" /></td>
                          <td align="left" class="other_filters_text_leftpadding">Remove messages with links
                            <div style="display: none;" id="loading_linkfilter" class="loading_linkfilter"> <img title="" alt="CN_rotator_v01" src="<?php echo $this->plugindir.'/images/CN_rotator_v01.gif';?>"/> </div></td>
                        </tr>
                        <tr>
                          <td align="left" valign="top" class="other_filters_checkbox_toppadding"><input type="checkbox" <?php echo ( ( (int)$response->noprofanity) == 1 ? 'checked="checked"' : '');?> name="chk_profanity" onclick="javascript:changeThisProfanityFilter(this);" id="chk_profanity" class="chk_profanity"/></td>
                          <td align="left" class="other_filters_text_leftpadding">Remove messages with profanity
                            <div style="display: none;" id="loading_profanityfilter" class="loading_profanityfilter"> <img title="" alt="CN_rotator_v01" src="<?php echo $this->plugindir.'/images/CN_rotator_v01.gif';?>"/> </div></td>
                        </tr>
                      </table>
                      <div class="draggable w250" style="display:none" id="div_span_whats_this"> We look for two types of spam: messages that use a lot of trending topics at once, or messages that link to websites with viruses, malware, or adult content <br/>
                        <center>
                          <input type="button" value="ok" onclick="hideDraggableElement(this);"/>
                        </center>
                      </div>
                      <div class="draggable w250" style="display:none" id="div_language_whats_this"> We look at the language a message is written in, regardless of the setting the user has provided to Twitter <br/>
                        <center>
                          <input type="button" value="ok" onclick="hideDraggableElement(this);"/>
                        </center>
                      </div>
                      <div class="draggable w250" style="display:none" id="div_duplicate_whats_this"> Many times, people delete a Twitter message, only to create a new one with a typo fixed. In other cases, people keep sending the same message over and over. Selecting this will only show the latest message from this user and remove previous ones that are similar. <br/>
                        <center>
                          <input type="button" value="ok" onclick="hideDraggableElement(this);"/>
                        </center>
                      </div>
                      
                    </div>
                  </div>
                  <div class="fleft round-bottom-left"></div>
                  <div class="fright round-bottom-right"></div>
                  <div class="clear"></div>
                </div>
              </div>
              <!--Filter SPAM Box End-->
            </div>
            <!--CONTROLS DIV END-->
            <!--Keywords-Discovered Box Start-->
            <div id="div_keywords_box" class="div_keywords_box">
              <div id="div_keywrods_discovered" class="mt10 pl4 fsize12">
                <div class="round-container w318 mb20">
                  <div class="fleft round-top-left"></div>
                  <div class="fright round-top-right"></div>
                  <div class="clear"></div>
                  <div class="mb10">
                    <div class="pt5 pl10 pr10">     
                      <!-- Automatic keywords Start-->
                      <div id="automatic_keywords_box" class="automatic_keywords_box" style=" <?php echo ( isset($response->automatic_keywords->auto) && count($response->automatic_keywords->auto) > 0 ? '' : 'display:none;' );?>">
                        <div class="home_page_sub_heading_styles">Automatic Keywords</div>
                        <div class="pt10 pl10">These are keywords that best match your site. Select the keywords you want to include.</div>
                        <div id="div_keywords" class="pl10 pt10">
                         <?php if( isset($response->automatic_keywords) && isset($response->automatic_keywords->auto) && count($response->automatic_keywords->auto) > 0 ) {
												   $search_html = '<table cellpadding="2" cellspacing="4">';	
													 for( $i=0; $i<count($response->automatic_keywords->auto); $i++ ){
													   $ext = '';
														 if($i==0){
														   $ext = '(best search phrase)';
														 }elseif($i>0 && $i==(count($response->automatic_keywords->auto)-1)){
															 $ext = '(worst search phrase)';
														 }				
														 $search_html = $search_html.'<tr><td>'.($i+1).'. '.'<input value="'.htmlentities($response->automatic_keywords->auto[$i]->name.'~~'.$response->automatic_keywords->auto[$i]->type).'" '.($response->automatic_keywords->auto[$i]->is_active==1 ? 'checked="checked"' : '').' type="checkbox" name="keywords[]" id="chk_'.str_replace('"','',$response->automatic_keywords->auto[$i]->name).'" /></td><td>'.$response->automatic_keywords->auto[$i]->name.' '.$ext.'</td></tr>';	
													 }#end-for
													 $search_html = $search_html.'</table>';
													 echo $search_html;
												 }?>
											  </div>
                      </div>
                      <!-- Automatic keywords end-->
                      <!-- Manual keywords Start-->
                      <div id="manual_keywords_box">
                        <div class="home_page_sub_heading_styles mt5">Manual Keywords</div>
                        <div class="pt10 pl10">You can</div>
                        <div class="pt10 pl10">
                          <textarea class="w250 h60" id="txt_keywords" name="txt_keywords"><?php echo ( isset($response->additional_keywords) && $response->additional_keywords!='' ? trim($response->additional_keywords) : '');?></textarea>
                        </div>
                      </div>
                      <!-- Manual keywords Start-->
                      <!-- Blacklisted keywords Start-->
                      <div id="black_keywords_box">
                        <div class="home_page_sub_heading_styles mt10">Blacklisted Keywords </div>
                        <div class="pt10 pl10">These are terms you do not want to appear in the stream</div>
                        <div class="pt10 pl10">
                          <textarea class="w250 h60" id="black_keywords" name="black_keywords"><?php echo ( isset($response->black_keywords) && $response->black_keywords!='' ?  trim($response->black_keywords) : '')?></textarea>
                          <br />
                        </div>
                      </div>
                      <!-- Blacklisted keywords Start-->
                      <table>
                        <tr>
                          <td><input onclick="javascript:refreshFeedGenWithKeywords(this); return false;" class="button-refresh" type="button" value="Refresh" name="btn_refresh" id="btn_refresh" /></td>
                          <td><img src="<?php echo $this->plugindir.'/images/CN_rotator_v01.gif';?>" alt="" title="" id="loading_keywords" class="loading_keywords" style="display:none ;"/></td>
                        </tr>
                      </table>
                      <input type="hidden" id="key_instance_id" name="key_instance_id" value="" />
                      <input type="hidden" id="param_filter_link" name="param_filter_link" value="" />
                    </div>
                  </div>
                  <div class="fleft round-bottom-left"></div>
                  <div class="fright round-bottom-right"></div>
                  <div class="clear"></div>
                </div>
              </div>
            </div>
            <!--Keywords-Discovered Box End-->
            
            <!--INTERACTION OPTIONS DIV ST-->
            <div id="div_stream_options" class="div_stream_options" style="display:none">
              <!--Topic Box Start-->
              <div class="round-container w318 mb20">
                <div class="fleft round-top-left"></div>
                <div class="fright round-top-right"></div>
                <div class="clear"></div>
                <div class="mb10">
                  <table cellpadding="0" cellspacing="0" width="95%">
                    <tr><td colspan="2" class="heading-padd home_page_sub_heading_styles">Outbound links:</td></tr>
                    <tr><td colspan="2" class="text-padd" style="font-size:12px;">If a user clicks on a link in the stream:</td></tr>
                    <tr><td width="7%" class="text-padd-1"><input type="checkbox" id="open_in_new" name="open_in_new" value="1" <?php echo ( $response->open_in_new==0 ? '' : 'checked="checked"' );?> /></td><td valign="top" class="text-padd-2" style="font-size:12px;">Open in a new screen</td></tr>
                    <tr><td width="7%" class="text-padd-1"><input type="checkbox" id="show_header" name="show_header" value="1" <?php echo ( $response->show_header==0 ? '' : 'checked="checked"' );?> /></td><td valign="top" class="text-padd-2" style="font-size:12px;">Use personalized wrapped header</td></tr>
                    <tr><td colspan="2" class="pt20">&nbsp;</td></tr>
                     <tr><td colspan="2" class="heading-padd home_page_sub_heading_styles">Welcome Message:</td></tr>
                    <tr><td width="7%" class="text-padd-1"><input type="checkbox" id="greet_visitor" name="greet_visitor" value="1" onclick="showElement(this, 'welcome_info');" <?php echo (trim($response->title_text)) ? 'checked="checked"' : ''?> /></td><td valign="top" class="text-padd-2" style="font-size:12px;">Greet visitors with a message</td></tr>
                    <tr id="welcome_info" class="welcome_info" <?php echo (trim($response->title_text)) ? '' : 'style="display:none"'?> >
                        <td colspan="2" class="text-padd-1" style="font-size:12px;">
                            Text to display when users first log into your stream:
                            <br/><br/>
                            <textarea class="w260 h80" id="ttitle" name="ttitle"><?php echo (trim($response->title_text)) ? $response->title_text : ''?></textarea>
                            <br/><br/>
                            <input type="checkbox" id="tfollower_chk" name="tfollower_chk" value="1" <?php echo (trim($response->to_follow )) ? 'checked="checked"' : ''?> />
                            &nbsp;&nbsp;Prompt the user to follow this Twitter account:
                            <br/><br/>
                            <input type="text" value="<?php echo (trim($response->to_follow)) ? $response->to_follow : 'enter text...'?>" class="w260" id="tfollower_txt" name="tfollower_txt" onfocus="if(this.value=='enter text...'){this.value='';}" onblur="if(this.value.replace(/\s+/,'')==''){this.value='enter text...';}" />
                        </td>
                    </tr>
                    <tr><td colspan="2" class="pt20">&nbsp;</td></tr>
                     <tr><td colspan="2" class="heading-padd home_page_sub_heading_styles">Twitter account options:</td></tr>
                    <tr><td width="7%" valign="top" class="text-padd-1"><input type="checkbox" id="taccount_chk" name="taccount_chk" value="1" <?php echo (trim($response->tweet_end)) ? 'checked="checked"' : ''?> onclick="showElement(this, 'twitter_info');" /></td><td valign="top" class="text-padd-2" style="font-size:12px;">Create links to my page when comments are inserted into twitter</td></tr>
                    <tr id="twitter_info" class="twitter_info" <?php echo (trim($response->tweet_end )) ? '' : 'style="display:none"'?> >
                        <td colspan="2" class="text-padd-1">
                            Text to display at the end of the Tweet:
                            <br/><br/>
                            <input type="text" value="<?php echo (trim($response->tweet_end)) ? $response->tweet_end : ''?>" class="w260" id="taccount_txt" name="taccount_txt" />
                        </td>
                    </tr>
                    <tr><td colspan="2">&nbsp;</td></tr>
                  </table>
                  <table cellpadding="0" cellspacing="0" width="95%">
                    <tr>
                      <td align="left" style="padding-left:20px;">
                      	<input onclick="javascript:refreshIntrctvOptions(this); return false;" type="button" value="Refresh" class="button-refresh" name="btn_refresh" id="btn_refresh" />
                      </td>
                      <td><img src="<?php echo $this->plugindir.'/images/CN_rotator_v01.gif';?>" alt="" title="" id="loading_interactive" class="loading_interactive" style="display:none ;"/></td>
                    </tr>
                  </table>
                </div>
                <div class="fleft round-bottom-left"></div>
                <div class="fright round-bottom-right"></div>
                <div class="clear"></div>
              </div>
            </div>
            <!--INTERACTION OPTIONS DIV END-->
            
            <!--CONTEST OPTIONS DIV START-->
            <div id="div_contest_options" class="div_contest_options" style="display:none;">
              <div class="round-container w318" style="margin-bottom:0">
                <div class="fleft round-top-left"></div>
                <div class="fright round-top-right"></div>
                <div class="clear"></div>
                <div class="mb10">
                	<div class="home_page_sub_heading_styles pl10 pt5">Contest Options</div>
                  <table cellpadding="2" cellspacing="2" width="100%" class="pl15">
                  	<tr>
                      <td class="pt5"><input type="checkbox" id="enable_contest" class="enable_contest" onClick="toogleContestOptions(this);" name="enable_contest"  <?php echo ( isset($response->contest_list->contest_descp) && $response->contest_list->contest_descp!='' ? 'checked="checked"' : '') ;?> value="1" />&nbsp;<span class="vertical-top">Enable contests for this feed</span></td>
                    </tr>
                    <tr><td>&nbsp;</td></tr>
                  </table>
                  <div id="contest_form_div" class="contest_form_div">
                    <div class="home_page_sub_heading_styles pl10 pt5">Contest Description</div>
                    <table cellpadding="2" cellspacing="2" width="100%" class="pl15">
                      <tr>
                        <td class="pt5">This is the description users will see when they click on the Contest tab of the stream</td>
                      </tr>
                      <tr>
                        <td class="pt5"><textarea id="contest_descp" name="contest_descp" class="contest_descp"><?php echo ( isset($response->contest_list->contest_descp) && $response->contest_list->contest_descp!='' ? $response->contest_list->contest_descp : '');?></textarea></td>
                      </tr>
                      <tr><td>&nbsp;</td></tr>
                    </table>
                    <div class="home_page_sub_heading_styles pl10 pt5">Viral Message</div>
                    <table cellpadding="2" cellspacing="2" width="100%" class="pl15">                		
                      <tr>
                        <td class="pt5">This is the message that you are asking users to spread and promote</td>
                      </tr>
                      <tr>
                        <td class="pt5"><textarea id="contest_vmsg" name="contest_vmsg" class="contest_vmsg"><?php echo ( isset($response->contest_list->contest_vmessage) && $response->contest_list->contest_vmessage!='' ? $response->contest_list->contest_vmessage : '');?></textarea></td>
                      </tr>
                      <tr>
                        <td class="pt5"><i>Use %u to represent a link to the contest tab of this stream</i></td>
                      </tr>
                      <tr><td>&nbsp;</td></tr>
                    </table>
                    <div class="home_page_sub_heading_styles pl10 pt5">Points</div>
                    <table cellpadding="2" cellspacing="2" width="100%" class="pl15">                		
                      <tr>
                        <td class="pt5"> Assign points to the various activities users can do</td>
                      </tr>
                      <tr>
                        <td class="pt5">Each retweet:&nbsp;&nbsp;<input type="text" id="retweet_pts" name="retweet_pts" class="retweet_pts w35 h20" value="<?php echo ( isset($response->contest_list->contest_rpoints) && $response->contest_list->contest_rpoints!=0 ? $response->contest_list->contest_rpoints : 0);?>" />&nbsp;&nbsp;points</td>
                      </tr>
                      <tr>
                        <td class="pt5">Each unique click:&nbsp;&nbsp;<input type="text" id="unique_click_pt" name="unique_click_pt" class="unique_click_pt w35 h20" value="<?php echo ( isset($response->contest_list->contest_uclicks) && $response->contest_list->contest_uclicks!=0 ? $response->contest_list->contest_uclicks : 0);?>" />&nbsp;&nbsp;points</td>
                      </tr>
                     <!-- <tr><td>&nbsp;</td></tr>
                      <tr><td><input onclick="javascript:saveContestData(this); return false;" type="button" value="Refresh" class="button-refresh" name="btn_refresh" id="btn_refresh" />&nbsp;&nbsp;<img src="<?php echo $this->plugindir.'/images/CN_rotator_v01.gif';?>" alt="" title="" id="loading_contest" class="loading_contest" style="display:none ;"/></td></tr>-->
                      <tr><td>&nbsp;</td></tr>
                    </table>
                    <div class="home_page_sub_heading_styles pl10 pt5">Prizes</div>
                    <table cellpadding="2" cellspacing="2" width="100%" class="pl15">                		
                      <tr>
                        <td class="pt5">You can add your own prize names, point levels, images and descriptions.</td>
                      </tr>
                      <tr>
                        <td class="pt5" align="center"><input type="button" id="edit_prizes" name="edit_prizes" value="Edit Prizes" class="button-refresh" onClick="showPrizeDialog();" class="cursor" /></td>
                      </tr>                  	
                      <tr><td>&nbsp;</td></tr>
                    </table>
                  </div>
                  <table cellpadding="2" cellspacing="2" width="100%" class="pl15">                		
                    <tr><td><input onclick="javascript:saveContestData(this); return false;" type="button" value="Refresh" class="button-refresh" name="btn_refresh" id="btn_refresh" />&nbsp;&nbsp;<img src="<?php echo $this->plugindir.'/images/CN_rotator_v01.gif';?>" alt="" title="" id="loading_contest" class="loading_contest" style="display:none ;"/></td></tr>
                  </table>
                </div>
                <div class="fleft round-bottom-left"></div>
                <div class="fright round-bottom-right"></div>
                <div class="clear"></div>
              </div>
            </div>
        		<!--CONTEST OPTIONS DIV END-->
        
            <div class="clear1"></div>
          </div>
          <div class="div_edit_result_bottom"></div>
        </div>
        <div class="step_center_panel"></div>
        <div class="step_right_panel" id="preview_iframe_div">
          <iframe width="525px" height="575px" frameborder="0" scrolling="no" src="<?php echo $this->call_back_url.'previewframe?id='.$response->StreamID.'&authkey='.$this->authkey.'&refer='.$this->referrer; ?>"></iframe>
        </div>
      </div>
      <div class="clear3"></div>
      <div class="fleft round-bottom-left"></div>
    	<div class="fright round-bottom-right"></div>
    	<div class="clear"></div>
		</div>		

    <script>
      /**** SCRIPt URLS VARIABLES **/
		  var referrer = "<?php echo $this->referrer;?>";
			var authkey = "<?php echo $this->authkey;?>";
			var param_str = '&authkey='+authkey+'&refer='+referrer;
			var updatefilters_url = "<?php echo $this->call_back_url.'updateFilters'; ?>";
			var load_instance_topichange_url = "<?php echo $this->call_back_url.'changetopic'; ?>";
			var change_topic_level_url = "<?php echo $this->call_back_url.'changeTopicLevel'; ?>";
			var update_keywords_url = "<?php echo $this->call_back_url.'updatekeywords';?>";
			var appearance_form_url = "<?php echo $this->call_back_url.'saveCnstep5'; ?>";
			var IntrctvOptions_url = "<?php echo $this->call_back_url.'interactionstep'; ?>";
			var embed_code_url = "<?php echo $this->call_back_url.'getWorkableEmbedCode'; ?>";
			var iframe_code_url = "<?php echo $this->call_back_url.'getIframeEmbedCode'; ?>";
			var iframe_src_url = "<?php echo $this->call_back_url.'previewframe'; ?>";
			var instance_id = <?php echo $this->instance_id; ?>;
			var save_contest_url = "<?php echo $this->call_back_url.'saveCncontest'; ?>";
			var w = 600;
			var h = 600;
			var prize_url = "<?php echo $this->call_back_url.'addprize?id='.$this->instance_id;?>";
      var images_url = "<?php echo $this->plugindir.'/images/';?>";
			jQuery(document).ready(function() {
				hideSaveButton();
			});
			<?php
			if( isset($response->contest_list->contest_descp) && $response->contest_list->contest_descp!='' ){?>
				fadeOptions(false, '');
			<?php
			}else{ ?>
				fadeOptions(true, 'transparent');
			<?php 
  		}?>
		</script>
    
		<?php 
	}else{
	  echo "Error in showing results";	
	}
}

  function replaceHashFromColors( $col_val ){
    return strip_tags(str_replace('#','', $col_val ));
  }
	
	function registerXmlRpcUser(){
		if( isset($_REQUEST['activate']) && ($_REQUEST['activate']=='true' || $_REQUEST['activate']== true) ){
		  $flag = $this->isAlreadyActivated();
			if( $flag == 0 ){
				$url = $this->call_back_url.'createUser?authkey='.$this->authkey.'&refer='.$this->referrer;
				$response = file_get_contents( $url );
				$response = simplexml_load_string( $response );
				if( $response->responseMessage == 'Success' && $response->UserID!=0){
			  	$this->user_id = $response->UserID; 
					$this->registerXmlRpcInstance();
		  	}
			}
		}
	}
	
	function registerXmlRpcInstance(){
	  $url = $this->call_back_url.'createStream?user_id='.$this->user_id.'&authkey='.$this->authkey.'&refer='.$this->referrer;		
		$response = file_get_contents( $url );
		$response = simplexml_load_string( $response );
		if( $response->responseMessage == 'Success' && $response->StreamID!=0 ){
		 $this->instance_id = $response->StreamID;
		 $this->updateWidgetOptions();
		}
	}
	
	function isAlreadyActivated(){
	  $options = get_option($this->widget_name);
		foreach( $options as $opt ){
		  if( isset($opt['user_id']) && $opt['user_id']!=0 && isset($opt['param_i']) && $opt['param_i']!=0 ){
			  $this->user_id = $opt['user_id'];
				$this->instance_id = $opt['param_i'];
				return 1;
			}
		}
		return 0;
	}
	
	function updateWidgetOptions(){
	  $options['user_id'] = htmlspecialchars( $this->user_id );
		$options['param_i'] = htmlspecialchars( $this->instance_id );
    update_option( $this->widget_name , $options);
	}
	
	function getInstanceRemoteData(){
	  if( $this->instance_id ){
			$url = $this->call_back_url.'getStreamOptions?instance_id='.$this->instance_id.'&authkey='.$this->authkey.'&refer='.$this->referrer;		
			$response = file_get_contents( $url );
			$response = simplexml_load_string( $response, 'SimpleXMLElement', LIBXML_NOCDATA);
			if( $response->responseMessage == 'Success' ){
			  return $response;
			}			
		}
		return '';
	}
	
	function getTopicLevelHtml( $topic_level_id, $boost=0 ){
	  $topics_level_selection = array('checked="checked"','','','','');
		if($boost==1 && $topic_level_id==2)
			$topics_level_selection[3] = 'checked="checked"';
		else
			$topics_level_selection[(int)$topic_level_id] = 'checked="checked"';
		
	  $html = '<table cellspacing="5" cellpadding="2" border="0">
				 <tr><td><input name="topic_level" id="topic_level_0" class="topic_level_0" type="radio" onclick="changeTopicLevel(0);" value="0" '.$topics_level_selection[0].'/></td><td>No filtering</td></tr>
				 <tr><td><input name="topic_level" id="topic_level_1" class="topic_level_1" type="radio" onclick="changeTopicLevel(1);" value="1" '.$topics_level_selection[1].'/></td><td><option value="1" '.$topics_level_selection[1].'>Medium filter, some off-topic results removed</option></td></tr>
				 <tr><td><input name="topic_level" id="topic_level_2" class="topic_level_2" type="radio" onclick="changeTopicLevel(2);" value="2" '.$topics_level_selection[2].'/></td><td><option value="2" '.$topics_level_selection[2].'>High filter, most off-topic results removed</option></td></tr>
				 <tr><td><input name="topic_level" id="topic_level_3" class="topic_level_3" type="radio" onclick="changeTopicLevel(3);" value="3" '.$topics_level_selection[3].'/></td><td><option value="3" '.$topics_level_selection[3].'>Deep search, good for rare items</option></td></tr>
			 </table>';
		return $html;
	}
	
	function getNonIframeCode(){
	  $url = $this->call_back_url.'getCode?instance_id='.$this->instance_id.'&authkey='.$this->authkey.'&refer='.$this->referrer;	
		$response = file_get_contents( $url );
		$response = simplexml_load_string( $response, 'SimpleXMLElement', LIBXML_NOCDATA);
		if( $response->responseMessage == 'Success' ){
			return $response->non_embed;
		}
	}
}
?>