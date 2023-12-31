<?php
if(!class_exists('EC_Management')):
require_once(EVENTSCALENDARCLASSPATH . '/ec_calendar.class.php');
require_once(EVENTSCALENDARCLASSPATH . '/ec_db.class.php');
require_once(EVENTSCALENDARCLASSPATH . '/ec_managementjs.class.php');

class EC_Management { // Dashboard management, widget control and edit options
  var $month;
  var $year;
  var $deflinkout;
  var $calendar;
  var $db;

  function EC_Management() {
    $this->month = $_GET['EC_action'] == 'switchMonthAdmin' ? $_GET['EC_month'] : date('m');
    $this->year = $_GET['EC_action'] == 'switchMonthAdmin' ? $_GET['EC_year'] : date('Y');
    $this->deflinkout = "http://";
    $this->calendar = new EC_Calendar();
    $this->db = new EC_DB();
  }

  function display() {
    global $wpdb;
    $js = new EC_ManagementJS();
    if(isset($_POST['EC_addEventFormSubmitted'])) {
      $title = $wpdb->escape($_POST['EC_title']);
      $location = isset($_POST['EC_location']) && !empty($_POST['EC_location']) ? $wpdb->escape($_POST['EC_location']) : null;
      $linkout = isset($_POST['EC_linkout']) && !empty($_POST['EC_linkout']) && ($_POST['EC_linkout'] != $this->deflinkout) ? $wpdb->escape($_POST['EC_linkout']) : null;
      $description = $wpdb->escape($_POST['EC_description']);
      $startDate = isset($_POST['EC_startDate']) && !empty($_POST['EC_startDate'])? $_POST['EC_startDate'] : date('Y-m-d');
      $startTime = isset($_POST['EC_startTime']) && !empty($_POST['EC_startTime']) ? $_POST['EC_startTime'] : null;
      $endDate = isset($_POST['EC_endDate']) && !empty($_POST['EC_endDate']) ? $_POST['EC_endDate'] : $startDate;
      $endDate = strcmp($startDate, $endDate) > 0 ? $startDate : $endDate;
      $endTime = isset($_POST['EC_endTime']) && !empty($_POST['EC_endTime']) ? $_POST['EC_endTime'] : null;
      $accessLevel = $_POST['EC_accessLevel'];
      $output = "<strong>"._c('Title','events-calendar').": </strong>$title<br />";
      if(!empty($location) && !is_null($location))
        $output .= "<strong>"._c('Location','events-calendar').": </strong>$location<br />";
      if(!empty($linkout) && !is_null($linkout))
        $output .= "<strong>"._c('Link out','events-calendar').": </strong><a href=\"".$linkout."\" target=\"_blanck\">"._c('Click here','events-calendar')."</a><br />";
      if(!empty($description) && !is_null($description))
        $output .= "<strong>"._c('Description','events-calendar').": </strong>$description<br />";
      if($startDate != $endDate )
        $output .= "<strong>"._c('Start Date','events-calendar').": </strong>$startDate<br />";
      if(!empty($startTime) || !is_null($startTime))
        $output .= "<strong>"._c('Start Time','events-calendar').": </strong>$startTime<br />";
      if($startDate != $endDate)
        $output .= "<strong>"._c('End Date','events-calendar').": </strong>$endDate<br />";
      if($startDate == $endDate)
        $output .= "<strong>"._c('Date','events-calendar').": </strong>$startDate<br />";
      if(!empty($endTime) && !empty($startTime) || !is_null($endTime) && !is_null($startTime))
        $output .= "<strong>"._c('End Time','events-calendar').": </strong>$endTime<br />";
      $post_id = null;
      if(isset($_POST['EC_doPost'])) {
        $statusPost = $_POST['EC_statusPost'];
        $data = array(
            'post_content' => $output
          , 'post_title' => $title
          , 'post_date' => date('Y-m-d H:i:s')
          , 'post_category' => $wpdb->escape($this->blog_post_author)
          , 'post_status' => $statusPost
          , 'post_author' => $wpdb->escape($this->blog_post_author)
        );
        $post_id = wp_insert_post($data);
        $results = $this->db->getLatestPost();
        $postID = $results[0]->id;
      }
      $this->addEvent($title, $location, $linkout, $description, $startDate, $startTime, $endDate, $endTime, $accessLevel, $postID);
      $splitDate = split("-", $startDate);
      $this->month = $splitDate[1];
      $this->year = $splitDate[0];
    }
    if(isset($_POST['EC_editEventFormSubmitted'])) {
      $id = $_POST['EC_id'];
      $title = $wpdb->escape($_POST['EC_title']);
      $location = isset($_POST['EC_location']) && !empty($_POST['EC_location']) ? $wpdb->escape($_POST['EC_location']) : null;
      $linkout = isset($_POST['EC_linkout']) && !empty($_POST['EC_linkout']) && ($_POST['EC_linkout'] != $this->deflinkout) ? $wpdb->escape($_POST['EC_linkout']) : null;
      $description = $wpdb->escape($_POST['EC_description']);
      $startDate = isset($_POST['EC_startDate']) && !empty($_POST['EC_startDate'])? $_POST['EC_startDate'] : date('Y-m-d');
      $startTime = isset($_POST['EC_startTime']) && !empty($_POST['EC_startTime']) ? $_POST['EC_startTime'] : null;
      $endDate = isset($_POST['EC_endDate']) && !empty($_POST['EC_endDate']) ? $_POST['EC_endDate'] : $startDate;
      $endDate = strcmp($startDate, $endDate) >= 0 ? $startDate : $endDate;
      $endTime = isset($_POST['EC_endTime']) && !empty($_POST['EC_endTime']) ? $_POST['EC_endTime'] : null;
      $accessLevel = $_POST['EC_accessLevel'];
      $postID = isset($_POST['EC_postID']) && !empty($_POST['EC_postID']) ? $_POST['EC_postID'] : null;
      $this->editEvent($id, $title, $location, $linkout, $description, $startDate, $startTime, $endDate, $endTime, $accessLevel, $postID);
      $splitDate = split("-", $startDate);
      $this->month = $splitDate[1];
      $this->year = $splitDate[0];
    }
    if($_GET['EC_action'] == 'edit') {
      $this->editEventForm($_GET['EC_id']);
      // $js->calendarData($this->month, $this->year);
      $js->calendarjs();
    }
    else {
      $this->calendar->displayAdmin($this->year, $this->month);
      $js->calendarData($this->month, $this->year);
      $this->addEventForm();
    }
  }

  function addEvent($title, $location, $linkout, $description, $startDate, $startTime, $endDate, $endTime, $accessLevel, $postID) {
    $this->db->addEvent($title, $location, $linkout, $description, $startDate, $startTime, $endDate, $endTime, $accessLevel, $postID);
    return;
  }

  function editEvent($id, $title, $location, $linkout, $description, $startDate, $startTime, $endDate, $endTime, $accessLevel, $postID) {
    $this->db->editEvent($id, $title, $location, $linkout, $description, $startDate, $startTime, $endDate, $endTime, $accessLevel, $postID);
  }

  function addEventForm() {
?>
    <a name="addEventform"></a><h2><?php _e('Add Event','events-calendar'); ?></h2>
    <form name="EC_addEventForm" method="post" action="?page=events-calendar" onSubmit="return valid_addEventForm();" onClick='jQuery("#alertmsg").fadeOut("slow");'>
      <p class="submit">
        <input type="submit" name="submit" value="<?php _e('Add Event','events-calendar'); ?> &raquo;">
      </p>
    <div id="alertmsg" class="alertmsg">
      <img id="close_message_alert" style="float:right;cursor:pointer" src="<?php echo EVENTSCALENDARIMAGESURL."/cross.png";?>" />
      <img src="<?php echo EVENTSCALENDARIMAGESURL."/alert.png";?>" style="vertical-align:middle;" /> <strong><?php _e('Warning','events-calendar'); ?></strong>
      <p>message</p>
    </div>
      <table summary="Edit Form1" class="editform" width="100%" cellspacing="2" cellpadding="5">
        <tr>
          <th width="33%" scope="row" valign="center" style="text-align:right;"><label for="title"><?php _e('Title','events-calendar'); ?></label></th>
          <td width="67%"><input type="text" name="EC_title" id="EC_title" style="width:200px;"/></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="center" style="text-align:right;"><label for="location"><?php _e('Location','events-calendar'); ?></label></th>
          <td width="67%"><input type="text" name="EC_location" id="EC_location" style="width:200px;"/></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="center" style="text-align:right;"><label for="linkout"><?php _e('Link out','events-calendar'); ?></label></th>
          <td width="67%"><input type="text" name="EC_linkout" id="EC_linkout" style="width:300px;" value="<?php echo $this->deflinkout;?>"/></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="top" style="text-align:right;"><label for="description"><?php _e('Description','events-calendar'); ?></label></th>
          <td width="67%"><textarea style="width:200px;height:75px;" name="EC_description" id="EC_description"></textarea></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="center" style="text-align:right;"><label for="startDate"><?php _e('Start Date (YYYY-MM-DD, if blank will be today)','events-calendar'); ?></label></th>
          <td width="67%"><input autocomplete="OFF" type="text" name="EC_startDate" id="EC_startDate" /></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="center" style="text-align:right;"><label for="startTime"><?php _e('Start Time (HH:MM, can be blank)','events-calendar'); ?></label></th>
          <td width="67%"><input autocomplete="OFF" type="text" name="EC_startTime" id="EC_startTime" /><img src="<?php echo EVENTSCALENDARIMAGESURL."/time.png";?>" width="20" height="20" id="EC_start_clockpick" style="vertical-align:middle;" onClick='jQuery("#alertmsg").fadeOut("slow");'></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="center" style="text-align:right;"><label for="endDate"><?php _e('End Date (YYYY-MM-DD, if blank will be same as start date)','events-calendar'); ?></label></th>
          <td width="67%"><input autocomplete="OFF" type="text" name="EC_endDate" id="EC_endDate" /></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="center" style="text-align:right;"><label for="endTime"><?php _e('End Time (HH:MM, can be blank)','events-calendar'); ?></label></th>
          <td width="67%"><input autocomplete="OFF" type="text" name="EC_endTime" id="EC_endTime" /><img src="<?php echo EVENTSCALENDARIMAGESURL."/time.png";?>" width="20" height="20" id="EC_end_clockpick" style="vertical-align:middle;" onClick='jQuery("#alertmsg").fadeOut("slow");'></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="center" style="text-align:right;"><label for="endTime"><?php _e('Visibility Level','events-calendar'); ?></label></th>
          <td width="67%">
            <select name="EC_accessLevel" id="EC_accessLevel">
              <option value="public"><?php _e('Public','events-calendar'); ?></option>
              <option value="level_10"><?php _e('Administrator','events-calendar'); ?></option>
              <option value="level_7"><?php _e('Editor','events-calendar'); ?></option>
              <option value="level_2"><?php _e('Author','events-calendar'); ?></option>
              <option value="level_1"><?php _e('Contributor','events-calendar'); ?></option>
              <option value="level_0"><?php _e('Subscriber','events-calendar'); ?></option>
            </select>
          </td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="center" style="text-align:right;"><label for="doPost"><?php _e('Create Post for Event','events-calendar'); ?></label></th>
          <td width="67%"><input type="checkbox" name="EC_doPost" id="EC_doPost"/></td>
        </tr>
      </table>
      <span id="showSelectStatusPost">
      <table summary="Edit Form2" class="editform" width="100%" cellspacing="2" cellpadding="5">
        <tr>
          <th width="33%" scope="row" valign="center" style="text-align:right;"><label for="statusPost"><?php _e('Which Post Status ?','events-calendar'); ?></label></th>
          <td width="67%">
            <select name="EC_statusPost" id="EC_statusPost">
              <option value="draft" selected="selected" ><?php _e('Draft','events-calendar'); ?></option>
              <option value="publish" ><?php _e('Publish','events-calendar'); ?></option>
            </select>
          </td>
        </tr>
      </table>
      </span>
      <input type="hidden" name="EC_addEventFormSubmitted" value="1" />
      <p class="submit">
        <input type="submit" name="submit" value="<?php _e('Add Event','events-calendar'); ?> &raquo;">
      </p>
    </form>
    </div>
    <script language="javascript">
    // <![CDATA[
      function ec_parse_float(valtime) {
// var idx = valtime.indexOf(":");
        var hr = valtime.substr(0,2);
        var mm = valtime.substr(3,2);
        return parseFloat(hr+"."+mm);
      }
      function valid_addEventForm() {
        if (document.forms.EC_addEventForm.EC_title.value=="") {
          alertmsgbox("<?php _e('Event Title can not be blank!','events-calendar'); ?>");
          document.forms.EC_addEventForm.EC_title.focus();
          return false;
        }
        var stt = ec_parse_float(document.forms.EC_addEventForm.EC_startTime.value);
        var edt = ec_parse_float(document.forms.EC_addEventForm.EC_endTime.value);
        if ( edt < stt ) {
          alertmsgbox("<?php _e('The end time is earlier than the start time ;-)','events-calendar'); ?>");
          document.forms.EC_addEventForm.EC_endTime.focus();
          return false;
        }
      }
      jQuery.noConflict();
      function alertmsgbox(msg) {
        jQuery("#alertmsg p").text(msg);
        jQuery("#alertmsg").show();
        jQuery("#alertmsg").animate({ top: "885px" }, 0 ).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
      }
      jQuery("form[@name='EC_addEventForm']").ready(function(){
          if (jQuery("#EC_doPost").is(":checked")) {
            jQuery("#showSelectStatusPost").show("slow");
          } else {
            jQuery("#showSelectStatusPost").hide("slow");
          }
      });
      jQuery("#EC_doPost").click(function(){
          if (jQuery("#EC_doPost").is(":checked")) {
            jQuery("#showSelectStatusPost").show("slow");
          } else {
            jQuery("#showSelectStatusPost").hide("slow");
          }
      });
      jQuery(document).ready(function() {
          jQuery("#close_message_alert").click(function() {
              jQuery("#alertmsg").fadeOut("slow");
          });
          jQuery("#alertmsg").hide();
      });
    //]]>
    </script>
<?php
}

  function editEventForm($id) {
    $event = $this->db->getEvent($id);
    $event = $event[0];
    $linkout = !is_null($event->eventLinkout) ? stripslashes($event->eventLinkout) : $this->deflinkout;
?>
    <h2><?php _e('Edit Event','events-calendar'); ?></h2>
    <form name="EC_editEventForm" method="post" action="?page=events-calendar" onSubmit="return valid_editEventForm();" onClick='jQuery("#alertmsg").fadeOut("slow");'>
      <p class="submit">
        <input type="submit" name="submit" value="<?php _e('Update Event','events-calendar'); ?> &raquo;">
      </p>
    <div id="alertmsg" class="alertmsg">
      <img id="close_message_alert" style="float:right;cursor:pointer" src="<?php echo EVENTSCALENDARIMAGESURL."/cross.png";?>" />
      <img src="<?php echo EVENTSCALENDARIMAGESURL."/alert.png";?>" style="vertical-align:middle;" /> <strong><?php _e('Warning','events-calendar'); ?></strong>
      <p>message</p>
    </div>
      <table summary="Edit Form" class="editform" width="100%" cellspacing="2" cellpadding="5">
        <tr>
          <th width="33%" scope="row" valign="center" style="text-align:right;"><label for="title"><?php _e('Title','events-calendar'); ?></label></th>
          <td width="67%"><input type="text" name="EC_title" id="EC_title" value="<?php echo stripslashes($event->eventTitle);?>" /></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="center" style="text-align:right;"><label for="location"><?php _e('Location','events-calendar'); ?></label></th>
          <td width="67%"><input type="text" name="EC_location" id="EC_location" value="<?php echo stripslashes($event->eventLocation);?>" /></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="center" style="text-align:right;"><label for="linkout"><?php _e('Link out','events-calendar'); ?></label></th>
          <td width="67%"><input type="text" name="EC_linkout" id="EC_linkout" style="width:300px;" value="<?php echo $linkout;?>"/></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="top" style="text-align:right;"><label for="description"><?php _e('Description','events-calendar'); ?></label></th>
          <td width="67%"><textarea name="EC_description" id="EC_description"><?php echo stripslashes($event->eventDescription);?></textarea></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="center" style="text-align:right;"><label for="startDate"><?php _e('Start Date (YYYY-MM-DD, if blank will be today)','events-calendar'); ?></label></th>
          <td width="67%"><input autocomplete="OFF" type="text" name="EC_startDate" id="EC_startDate" value="<?php echo $event->eventStartDate;?>" /></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="center" style="text-align:right;"><label for="startTime"><?php _e('Start Time (HH:MM, can be blank)','events-calendar'); ?></label></th>
          <td width="67%"><input autocomplete="OFF" type="text" name="EC_startTime" id="EC_startTime" value="<?php echo $event->eventStartTime;?>" /><img src="<?php echo EVENTSCALENDARIMAGESURL."/time.png";?>" width="20" height="20" id="EC_start_clockpick" style="vertical-align:middle;" onClick='jQuery("#alertmsg").fadeOut("slow");'></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="center" style="text-align:right;"><label for="endDate"><?php _e('End Date (YYYY-MM-DD, if blank will be same as start date)','events-calendar'); ?></label></th>
          <td width="67%"><input autocomplete="OFF" type="text" name="EC_endDate" id="EC_endDate" value="<?php echo $event->eventEndDate;?>" /></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="center" style="text-align:right;"><label for="endTime"><?php _e('End Time (HH:MM, can be blank)','events-calendar'); ?></label></th>
          <td width="67%"><input autocomplete="OFF" type="text" name="EC_endTime" id="EC_endTime" value="<?php echo $event->eventEndTime;?>" /><img src="<?php echo EVENTSCALENDARIMAGESURL."/time.png";?>" width="20" height="20" id="EC_end_clockpick" style="vertical-align:middle;" onClick='jQuery("#alertmsg").fadeOut("slow");'></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="center" style="text-align:right;"><label for="endTime"><?php _e('Visibility Level','events-calendar'); ?></label></th>
          <td width="67%">
            <select name="EC_accessLevel" id="EC_accessLevel">
              <option value="public" <?php if($event->accessLevel == 'public') echo 'selected="selected"';?>><?php _e('Public','events-calendar'); ?></option>
              <option value="level_10" <?php if($event->accessLevel == 'level_10') echo 'selected="selected"';?>><?php _e('Administrator','events-calendar'); ?></option>
              <option value="level_7" <?php if($event->accessLevel == 'level_7') echo 'selected="selected"';?>><?php _e('Editor','events-calendar'); ?></option>
              <option value="level_2" <?php if($event->accessLevel == 'level_2') echo 'selected="selected"';?>><?php _e('Author','events-calendar'); ?></option>
              <option value="level_1" <?php if($event->accessLevel == 'level_1') echo 'selected="selected"';?>><?php _e('Contributor','events-calendar'); ?></option>
              <option value="level_0" <?php if($event->accessLevel == 'level_0') echo 'selected="selected"';?>><?php _e('Subscriber','events-calendar'); ?></option>
            </select>
          </td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="center" style="text-align:right;"><label for="postID"><?php _e('Post ID','events-calendar'); ?></label></th>
          <td width="67%"><input autocomplete="OFF" type="text" size="10" name="EC_postID" id="EC_postID" value="<?php echo $event->postID;?>" onChange="postIDtst()"/></td>
        </tr>
      </table>
      <input type="hidden" name="EC_editEventFormSubmitted" value="1" />
      <input type="hidden" name="EC_id" value="<?php echo $id;?>" />
      <p class="submit">
        <input type="submit" name="submit" value="<?php _e('Update Event','events-calendar'); ?> &raquo;">
      </p>
    </form>
    <script language="javascript">
    // <![CDATA[
      function onfocus_addEventForm() {
        jQuery("#alertmsg").fadeOut("slow");
      }
      function ec_parse_float(valtime) {
        // var idx = valtime.indexOf(":");
        var hr = valtime.substr(0,2);
        var mm = valtime.substr(3,2);
        return parseFloat(hr+"."+mm);
      }
      function valid_editEventForm() {
        if (document.forms.EC_editEventForm.EC_title.value=="") {
          alertmsgbox("<?php _e('Event Title can not be blank!','events-calendar'); ?>");
          document.forms.EC_editEventForm.EC_title.focus();
          return false;
        }
        var stt = ec_parse_float(document.forms.EC_editEventForm.EC_startTime.value);
        var edt = ec_parse_float(document.forms.EC_editEventForm.EC_endTime.value);
        if ( edt < stt ) {
          alertmsgbox("<?php _e('The end time is earlier than the start time ;-)','events-calendar'); ?>");
          document.forms.EC_editEventForm.EC_endTime.focus();
          return false;
        }
        if (!postIDtst()) return false;
        return true;
      }
      function postIDtst() {
        var pid = document.forms.EC_editEventForm.EC_postID.value;
        if (pid == '') return true;
        var m = parseFloat(pid);
        if (isNaN(m)) {
          alertmsgbox("<?php _e('Post ID must be a number!','events-calendar'); ?>");
          return false;
        } else {
          m=Number(document.forms.EC_editEventForm.EC_postID.value);
          if (isNaN(m)) {
            alertmsgbox("<?php _e('Post ID must be a number!','events-calendar'); ?>");
            return false;
          }
        }
        return true;
      }
      jQuery.noConflict();
      function alertmsgbox(msg) {
        jQuery("#alertmsg p").text(msg);
        jQuery("#alertmsg").show();
        jQuery("#alertmsg").animate({ top: "300px" }, 0 ).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
      }
      jQuery(document).ready(function() {
          jQuery("#close_message_alert").click(function() {
              jQuery("#alertmsg").fadeOut("slow");
          });
          jQuery("#alertmsg").hide();
          jQuery("a[@href='#addEventform']").hide();
      })(jQuery);
    //]]>
    </script>
<?php
  }

  function widgetControl() {
    $options = get_option('widgetEventsCalendar');
    if ( !is_array($options) ){
      $options = array();
      $options['title'] = _e('Events Calendar','events-calendar');
    }
    if ( $_POST['eventscalendar']['submit'] ) {
      unset($_POST['eventscalendar']['submit']);
      foreach ( $_POST['eventscalendar'] as $key => $option ) {
        $options[$key] = strip_tags(stripslashes($option));
      }
      update_option('widgetEventsCalendar', $options);
    }
    $title = htmlspecialchars($options['title'], ENT_QUOTES);
    echo '<p style="text-align:center;"><label for="eventscalendar-title">' . __("Title","events-calendar") . ': <input style="width: 200px;" id="eventscalendar-title" name="eventscalendar[title]" type="text" value="'.$title.'" /></label></p>';
    ?>
      <p style="text-align:center;">
        <label for="eventscalendar-type">
          <?php printf(__("Calendar Type","events-calendar")) ?>:
          <select name="eventscalendar[type]" id="eventscalendar-type">
            <option value="calendar"><?php printf(__("Calendar","events-calendar")) ?></option>
            <option value="list" <?php if ( isset($options['type']) && 'list' == $options['type'] ) echo 'selected="selected"'; ?>><?php printf(__("Event List","events-calendar")) ?></option>
          </select>
        </label>
      </p>
      <div id="eventscalendar-EventListOptions" style="<?php if ( !isset($options['type']) || 'list' != $options['type'] ) echo 'display: none;'; ?>">
        <p>
          <span style="font-weight: bold"><?php printf(__("Event List options","events-calendar")) ?></span>
        </p>
        <p>
          <label for="eventscalendar-listCount">
            <?php printf(_e("Number of events","events-calendar")) ?>:
            <input style="width: 30px;" type="text" id="eventscalendar-listCount" name="eventscalendar[listCount]" value="<?php echo ( isset($options['listCount']) && !empty($options['listCount']) ) ? $options['listCount'] : '5'; ?>" />
          </label>
        </p>
      </div>

    <?php
    echo '<input type="hidden" id="eventscalendar-submit" name="eventscalendar[submit]" value="1" />';
    ?>
      <script type="text/javascript">
      // <![CDATA[
        jQuery.noConflict();
        jQuery("select#eventscalendar-type").change(function(){
          if ( "list" == this.value ) {
            jQuery("#eventscalendar-EventListOptions").show();
          } else {
            jQuery("#eventscalendar-EventListOptions").hide();
          }
        })(jQuery);
      //]]>
      </script>
    <?php
  }

  function calendarOptions() {
    $options = get_option('optionsEventsCalendar');
    if(!is_array($options)) {
      $options = array();
      $options['dateFormatWidget'] = 'm-d';
      $options['timeFormatWidget'] = 'h:i a';
      $options['dateFormatLarge'] = 'n/j/Y';
      $options['timeFormatLarge'] = 'h:i a';
      $options['adaptedCSS'] = '';
      $options['todayCSS'] = 'border:thin solid blue;font-weight: bold;';
      $options['dayHasEventCSS'] = 'color:red';
      $options['timeStep'] = '30';
      $options['daynamelength'] = '3';
      $options['daynamelengthLarge'] = '3';
      $options['jqueryextremstatus'] = 'false';
    }
    if ( $_POST['optionsEventsCalendarSubmitted'] ) {
      //echo var_dump($_POST);
      $options['dateFormatWidget'] = isset($_POST['dateFormatWidget']) && !empty($_POST['dateFormatWidget']) ? $_POST['dateFormatWidget'] : 'm-d';
      $options['timeFormatWidget'] = isset($_POST['timeFormatWidget']) && !empty($_POST['timeFormatWidget']) ? $_POST['timeFormatWidget'] : 'g:i a';
      $options['dateFormatLarge'] = isset($_POST['dateFormatLarge']) && !empty($_POST['dateFormatLarge']) ? $_POST['dateFormatLarge'] : 'n/j/Y';
      $options['timeFormatLarge'] = isset($_POST['timeFormatLarge']) && !empty($_POST['timeFormatLarge']) ? $_POST['timeFormatLarge'] : 'g:i a';
      $options['timeStep'] = isset($_POST['timeStep']) && !empty($_POST['timeStep']) ? $_POST['timeStep'] : '30';
      $options['adaptedCSS'] = isset($_POST['adaptedCSS']) ? $_POST['adaptedCSS'] : '';
      $options['todayCSS'] = isset($_POST['todayCSS']) && !empty($_POST['todayCSS']) ? $_POST['todayCSS'] : 'border:thin solid blue;font-weight: bold;';
      $options['dayHasEventCSS'] = isset($_POST['dayHasEventCSS']) && !empty($_POST['dayHasEventCSS']) ? $_POST['dayHasEventCSS'] : 'color:red;';
      $options['daynamelength'] = isset($_POST['daynamelength']) && !empty($_POST['daynamelength']) ? $_POST['daynamelength'] : '3';
      $options['daynamelengthLarge'] = isset($_POST['daynamelengthLarge']) && !empty($_POST['daynamelengthLarge']) ? $_POST['daynamelengthLarge'] : '3';
      $options['jqueryextremstatus'] = isset($_POST['jqxstatus']) ? $_POST['jqxstatus'] : 'false';
      $options['accessLevel'] = $_POST['EC_accessLevel'];


      update_option('optionsEventsCalendar', $options);
    }
?>
    <div class="wrap"><h2 style="border:none;margin-top:12px;"><?php _e('Events Calendar Options','events-calendar'); ?></h2></div>
    <form name="optionsEventsCalendar" method="post" action="?page=events-calendar-options" onLoad="">
      <p class="submit">
        <input type="submit" name="submit" value="<?php _e('Update Options','events-calendar'); ?> &raquo;">
      </p>
      <table summary="Edit Form1" class="form-table" width="100%" cellspacing="2" cellpadding="5">
        <tr>
          <th width="33%" scope="row" valign="top" style="text-align:right;"><label for="EC_accessLevel"><?php _e('Access Level','events-calendar'); ?></label></th>
          <td width="67%">
            <select name="EC_accessLevel" id="accessLevel">
              <option value="level_10" <?php if($options['accessLevel'] == 'level_10') echo 'selected="selected"';?>><?php _e('Administrator','events-calendar'); ?></option>
              <option value="level_7" <?php if($options['accessLevel'] == 'level_7') echo 'selected="selected"';?>><?php _e('Editor','events-calendar'); ?></option>
              <option value="level_2" <?php if($options['accessLevel'] == 'level_2') echo 'selected="selected"';?>><?php _e('Author','events-calendar'); ?></option>
              <option value="level_1" <?php if($options['accessLevel'] == 'level_1') echo 'selected="selected"';?>><?php _e('Contributor','events-calendar'); ?></option>
              <option value="level_0" <?php if($options['accessLevel'] == 'level_0') echo 'selected="selected"';?>><?php _e('Subscriber','events-calendar'); ?></option>
            </select>
          </td>
        </tr>
        </table>
        <table summary="Edit Form2" class="form-table" width="100%" cellspacing="2" cellpadding="5">
        <tr>
          <th style="text-align:right;border:none;"><label><?php _e('Date/Time Formatting(see','events-calendar'); ?> <a href="http://us2.php.net/date" target="_blank">PHP Date</a>) :</label></th>
          <td style="border:none;"></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="top" style="text-align:right;border:none;"><label for="dateFormatWidget"><?php _e('Widget Calendar Dates','events-calendar'); ?></label></th>
          <td width="67%" style="border:none;"><input type="text" name="dateFormatWidget" id="dateFormatWidget" value="<?php echo $options['dateFormatWidget'];?>" /></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="top" style="text-align:right;border:none;"><label for="timeFormatWidget"><?php _e('Widget Calendar Times','events-calendar'); ?></label></th>
          <td width="67%" style="border:none;"><input type="text" name="timeFormatWidget" id="timeFormatWidget" value="<?php echo $options['timeFormatWidget'];?>" /></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="top" style="text-align:right;border:none;"><label for="dateFormatLarge"><?php _e('Large Calendar Dates','events-calendar'); ?></label></th>
          <td width="67%" style="border:none;"><input type="text" name="dateFormatLarge" id="dateFormatLarge" value="<?php echo $options['dateFormatLarge'];?>" /></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="top" style="text-align:right;border:none;"><label for="timeFormatLarge"><?php _e('Large Calendar Times','events-calendar'); ?></label></th>
          <td width="67%" style="border:none;"><input type="text" name="timeFormatLarge" id="timeFormatLarge" value="<?php echo $options['timeFormatLarge'];?>" /></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="top" style="text-align:right;"><label for="timeStep"><?php _e('Step Increment for Time Selector (in minutes)','events-calendar'); ?></label></th>
          <td width="67%"><input type="text" name="timeStep" id="timeStep" value="<?php echo $options['timeStep'];?>" /></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="top" style="text-align:right;"><label for="adaptedCSS"><?php _e('I have adapted the Events-Calendar stylesheet (checked = yes)','events-calendar'); ?></label></th>
          <td width="67%"><input type="checkbox" <?php echo ($options['adaptedCSS']==true) ? "checked " : "";?>name="adaptedCSS" id="EC_adaptedCSS" value="on" /></td>
        </tr>
        </table>
        <span id="switchCSSoptions">
        <table summary="Edit Form3" class="form-table" width="100%" cellspacing="2" cellpadding="5">
        <tr>
          <th width="33%" scope="row" valign="top" style="text-align:right;" id="EC_todayCSS"><label for="todayCSS"><?php _e('CSS for Today Date','events-calendar'); ?></label></th>
          <td width="67%"><input type="text" name="todayCSS" id="EC_todayCSS" value="<?php echo $options['todayCSS'];?>" /></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="top" style="text-align:right;" id="EC_dayHasEventCSS"><label for="dayHasEventCSS"><?php _e('CSS for Day With Events','events-calendar'); ?></label></th>
          <td width="67%"><input type="text" name="dayHasEventCSS" id="EC_dayHasEventCSS" value="<?php echo $options['dayHasEventCSS'];?>" /></td>
        </tr>
        </table>
        </span>
        <table summary="Edit Form4" class="form-table" width="100%" cellspacing="2" cellpadding="5">
        <tr>
          <th width="33%" scope="row" valign="top" style="text-align:right;"><label for="daynamelength"><?php _e('Length of day names in Widget Calendar','events-calendar'); ?></label></th>
          <td width="67%"><input type="text" name="daynamelength" id="EC_daynamelength" value="<?php echo $options['daynamelength'];?>" /></td>
        </tr>
        <th width="33%" scope="row" valign="top" style="text-align:right;"><label for="daynamelengthLarge"><?php _e('Length of day names in Large Calendar','events-calendar'); ?></label></th>
          <td width="67%"><input type="text" name="daynamelengthLarge" id="EC_daynamelengthLarge" value="<?php echo $options['daynamelengthLarge'];?>" /></td>
        </tr>
        <tr>
          <th width="33%" scope="row" valign="top" style="text-align:right;"><label for="jqxstatus"><?php _e('jQuery Extrem Protection (checked = yes)','events-calendar'); ?></label></th>
          <td width="67%"><input type="checkbox" <?php echo ($options['jqueryextremstatus']=="true") ? " checked" : "";?> name="jqxstatus" id="EC_jqxstatus" value="true" /> <?php _e('(Check if you don\'t see the Tooltips)','events-calendar'); ?></td>
        </tr>
      </table>
      <input type="hidden" name="optionsEventsCalendarSubmitted" value="1" />
      <p class="submit">
        <input type="submit" name="submit" value="<?php _e('Update Options','events-calendar'); ?> &raquo;">
      </p>
    </form>
    <script type="text/javascript">
    // <![CDATA[
      jQuery.noConflict();
      jQuery("form[@name='optionsEventsCalendar']").ready(function () {
          if (jQuery("#EC_adaptedCSS").is(":checked")) {
            jQuery("#switchCSSoptions").hide("slow");
          } else {
            jQuery("#switchCSSoptions").show("slow");
          };
      });
      jQuery("#EC_adaptedCSS").click(function () {
          if (jQuery("#EC_adaptedCSS").is(":checked")) {
            jQuery("#switchCSSoptions").hide("slow");
          } else {
            jQuery("#switchCSSoptions").show("slow");
          };
      });
      jQuery(document).ready(function() {
          jQuery("a[@href='#addEventform']").hide();
      })(jQuery);
    //]]>
    </script>
<?php
  }
}
endif;
?>