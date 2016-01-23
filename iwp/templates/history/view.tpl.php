<?php

/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
 
/*
* history/view.tpl
*/
$statusMessages = array(
"writingRequest" => "Adding to queue",
"pending" => "Pending",
"initiated" => "Initiated",
"running" => "Running",
"scheduled" => "Waiting in queue",
"processingResponse" => "Processing response",
"multiCallWaiting" => "Running"
  );
$uncompletedExists = 0;
if(!empty($d['actionsHistoryData'])){
  
  $sitesData = Reg::tplGet('sitesData');
foreach($d['actionsHistoryData'] as $actionID => $actionHistory){
	
	$showByDetailedActionGroup = ( $actionHistory['type'] == 'PTC'|| (($actionHistory['action'] == 'manage' || $actionHistory['action'] == 'install') && ($actionHistory['type'] == 'plugins' || $actionHistory['type'] == 'themes') ) );
	
?>
<?php if(in_array($actionHistory['status'], array('writingRequest','pending', 'initiated', 'running', 'scheduled', 'processingResponse', 'multiCallWaiting'))){ $uncompletedExists = 1; } ?>
<div class="ind_row_cont" actionID="<?php echo $actionID; ?>">
	<div class="row_summary<?php $stoppingClass='rep_sprite_remove_task removeThisAct'; if(in_array($actionHistory['status'], array('pending', 'running', 'initiated', 'multiCallWaiting'))){ ?> in_progress<?php $stoppingClass='rep_sprite_backup btn_stop_progress stop_pending'; } ?>"><?php if($actionHistory['status'] == 'multiCallWaiting'){ ?> <div class="rep_sprite btn_stop_rep_sprite_activity_log" ><span class = "rep_sprite_backup btn_stop_progress stop_multicall"  mechanism = "multiCall" actionID = "<?php echo $actionID; ?>"></span> </div><?php } ?>
      <div class="row_arrow"></div>
      <div class="timestamp"><?php echo @date(Reg::get('dateFormatLong'), $actionHistory['time']); ?></div>
      <div class="row_name"><?php echo TPLPrepareHistoryBriefTitle($actionHistory); ?></div>
      <?php if($actionHistory['statusSummary']['success']) { ?><div class="success_bu rep_sprite_backup"><?php echo $actionHistory['statusSummary']['success']; ?></div><?php } ?>
      <?php if($errorCount = ($actionHistory['statusSummary']['error'] + $actionHistory['statusSummary']['netError'])) { ?><div class="failed_bu rep_sprite_backup"><?php echo $errorCount; ?></div><?php } ?>
      <div class="clear-both"></div>
    </div>
              <div class="row_detailed" style="display:none;">
            <div class="rh <?php if(in_array($actionHistory['status'], array('pending', 'running', 'initiated', 'processingResponse', 'multiCallWaiting'))){ ?> in_progress<?php } ?>">
                  <div class="row_arrow"></div>
                  <div class="timestamp"><?php echo @date(Reg::get('dateFormatLong'), $actionHistory['time']); ?></div>
                  <?php if(userStatus() == "admin"){ ?><div class="rep_sprite btn_rep_sprite_remove_task"><span class="<?php echo $stoppingClass; ?>" actionid="<?php echo $actionID; ?>"></span></div><?php } ?>
                  <a class="btn_send_report float-right droid400 sendReport" actionid="<?php echo $actionID; ?>">Report Issue</a>
                  <div class="row_name"><?php echo TPLPrepareHistoryBriefTitle($actionHistory); ?></div>
                  <?php if($actionHistory['statusSummary']['success']) { ?><div class="success_bu rep_sprite_backup"><?php echo $actionHistory['statusSummary']['success']; ?></div><?php } ?>
                  <?php if($errorCount = ($actionHistory['statusSummary']['error'] + $actionHistory['statusSummary']['netError'])) { ?><div class="failed_bu rep_sprite_backup"><?php echo $errorCount; ?></div><?php } ?>
                  <div class="clear-both"></div>
                </div>
            <div class="rd">
            
<!--            -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
            
            <!-- Group updates which has more than one type -->
            
<?php 
//Grouping by siteID, detailedAction, status
$fullGroupedActions = array();
$siteWithErrors = array();

foreach($actionHistory['detailedStatus'] as $singleAction){
	
	//to display plugin slug instead of plugin main file say hello-dolly/hello_dolly.php => hello-dolly
	if($actionHistory['type'] == 'PTC' && $singleAction['detailedAction'] == 'plugin'){
		$uniqueNameTemp = explode('/', $singleAction['uniqueName']);
		$singleAction['uniqueName'] = reset($uniqueNameTemp);
		$singleAction['uniqueName'] = str_replace('.php', '', $singleAction['uniqueName']);
	}
	
	if(in_array($actionHistory['type'], array('themes', 'plugins')) && $actionHistory['action'] == 'install' && strpos($singleAction['uniqueName'], '%20') !== false){//this to replace %20 in the file name
		$singleAction['uniqueName'] = str_replace('%20', ' ', $singleAction['uniqueName']);
	}
	
	
	if($singleAction['status'] == 'success'){
    $fullGroupedActions[ $singleAction['siteID'] ][ $singleAction['detailedAction'] ][ 'success' ] [] = array('name' => $singleAction['uniqueName'],'detailedAction' => $singleAction['detailedAction'],'type' => $actionHistory['type'], 'action' => $actionHistory['action'] );
		
	}
	elseif($singleAction['status'] == 'error' || $singleAction['status'] == 'netError'){	
		
		$fullGroupedActions[ $singleAction['siteID'] ][ $singleAction['detailedAction'] ][ 'error' ] [] = array('name' => $singleAction['uniqueName'], 'errorMsg' => $singleAction['errorMsg'], 'error' => $singleAction['error'], 'type' => $actionHistory['type'], 'action' => $actionHistory['action'], 'detailedAction' => $singleAction['detailedAction'], 'microtimeInitiated' => $singleAction['microtimeInitiated'], 'status' => $singleAction['status']);
		$siteWithErrors[$singleAction['siteID']] = $singleAction['historyID'];
	}
	else{
    $fullGroupedActions[ $singleAction['siteID'] ][ $singleAction['detailedAction'] ][ 'others' ] [] = array('name' => $singleAction['uniqueName'], 'errorMsg' => $singleAction['mainStatus'], 'microtimeInitiated' => $singleAction['microtimeInitiated'], 'status' => $singleAction['status']);
		
	}
	$sitesDataTemp[$singleAction['siteID']]['name'] = isset($sitesData[$singleAction['siteID']]['name']) ?  $sitesData[$singleAction['siteID']]['name'] : $singleAction['URL'];
}
?>
<?php foreach($fullGroupedActions as $siteID => $siteGroupedActions){ ?>           
 <!--           For each Site starts-->
                  <div class="row_updatee">
                <div class="row_updatee_ind">
                      <div class="label_updatee">
                    <div class="label droid700 float-left"><?php if(!empty($siteWithErrors[$siteID])){ ?><a style="float:left; position:absolute; left:0; bottom:2px;" class="moreInfo" historyID="<?php echo $siteWithErrors[$siteID]; ?>">View site response</a><?php unset($siteWithErrors[$siteID]); } ?><?php echo $sitesDataTemp[$siteID]['name']; ?></div>
                    <div class="clear-both"></div>
                  </div>
                      <div class="item_cont_right_cont">                      
       <?php foreach($siteGroupedActions as $detailedAction => $statusGroupedActions){ ?>     
                    <!--  For each type starts-->                   
                    <div class="item_cont">
                        <?php if($showByDetailedActionGroup){ ?><div class="item_label float-left"><span><?php echo ($detailedAction == 'core') ? 'WP' : $detailedAction; ?></span></div><?php } ?>
                        <div class="float-left">                        
                        <?php if(!empty($statusGroupedActions['success'])){ ?> 
                        <div class="item_success rep_sprite_backup float-left">                    
                        
                        <?php 
                  foreach($statusGroupedActions['success'] as $oneAction){
                    if($showByDetailedActionGroup){
                      echo '<span>'.$oneAction['name'].'</span>'; 
                    }else{ 
                      echo "<span>".TPLActionTitle($oneAction)."</span>"; 
                    }
                  }
                  ?>  
                        </div>
                        <?php } ?>
                        <?php if(!empty($statusGroupedActions['others'])){ ?>
                        <div class="item_failure rep_sprite_backup float-left">
							  <?php foreach($statusGroupedActions['others'] as $oneAction){ ?>
                              <!--For each plugins failed plugins starts-->
                              <?php if($showByDetailedActionGroup){ ?><div class="failure_name"><?php echo $oneAction['name']; ?></div><?php } ?>
                              <div class="failure_reason<?php if(!$showByDetailedActionGroup){ ?> only<?php } ?>"><?php echo $statusMessages[$oneAction['errorMsg']]; ?></div>
                              <div class="clear-both"></div>
                              <!--For each plugins successful plugins ends-->
							  <?php } ?>
                        </div>
                        <?php } ?>
                        <?php if(!empty($statusGroupedActions['error'])){ ?> 
                        
                        <div class="item_failure rep_sprite_backup float-left">
                        	 <?php foreach($statusGroupedActions['error'] as $oneAction){ ?>
                        	  <!--For each plugins failed plugins starts-->
                              <?php if($showByDetailedActionGroup){ ?><div class="failure_name"><?php echo $oneAction['name']; ?></div><?php } ?>
                              <div class="failure_reason<?php if(!$showByDetailedActionGroup){ ?> only<?php } ?>"><?php echo TPLAddErrorHelp($oneAction); ?></div>                              
                              <div class="clear-both"></div>
                              <!--For each plugins successful plugins ends-->
                              <?php } ?>                              
                        </div>
                        <?php } ?>
                      </div>
                          <div class="clear-both"></div>
                        </div>
                              <!--  For each type ends-->
      <?php } //END foreach($siteGroupedActions as $detailedAction => $statusGroupedActions) ?>  
                  </div>
                      <div class="clear-both"></div>
                    </div>
              </div>
               <!--           For each Site ends-->
<?php } //END foreach($fullGroupedActions as $siteID => $siteGroupedActions) ?>   
                </div>
          </div>
            </div>
<?php

} //end foreach($d['actionsHistoryData'] as $actionHistory)

} //end if(!empty($d['actionsHistoryData']))


$pagination = Reg::tplget('pagination');

if(empty($pagination['totalPage'])){ ?>
<div class="empty_data_set"> <div class="line2">Bummer, there are no activites logged in the given search criteria.</div></div>
<?php } ?>
<script>
<?php if($pagination['page'] == 1){ ?>
$("#historyPagination").show().jPaginator({
	nbVisible:5,
	nbPages:<?php echo $pagination['totalPage']; ?>,
	selectedPage:<?php echo $pagination['page']; ?>,
	overBtnLeft:'#historyPagination_o_left',
	overBtnRight:'#historyPagination_o_right',
	maxBtnLeft:'#historyPagination_m_left',
	maxBtnRight:'#historyPagination_m_right',
	withSlider: false,
	widthPx: 25,
	marginPx: 0,
	onPageClicked: function(a,num) {
      var userID = 0;
      if($("#activityUsers").length)  userID = $("#activityUsers").find('option:selected').attr('id');
      var searchByUser = 1;
      if(!userID){searchByUser = 0;}
      var keywords = $("#activityKeywordFilter").find('option:selected').attr('types');

      tempArray={};
      tempArray['requiredData']={};
      tempArray['requiredData']['getHistoryPageHTML']={};
      tempArray['requiredData']['getHistoryPageHTML']['dates']=$("#dateContainer").attr('exactdate');
      tempArray['requiredData']['getHistoryPageHTML']['page']=num;

      tempArray['requiredData']['getHistoryPageHTML']['searchByUser'] = searchByUser;
      tempArray['requiredData']['getHistoryPageHTML']['userID']=userID;
      tempArray['requiredData']['getHistoryPageHTML']['getKeyword']=keywords;

      doCall(ajaxCallPath,tempArray,'loadHistoryPageContent');
  	}
  });
<?php } ?>
<?php if(empty($pagination['totalPage'])){ ?>
$("#historyPagination").hide();
<?php } ?>

<?php if(!empty($d['searching']) && $d['searching'] ){ ?>
<?php if($d['searching'] > 1){ $d['searching'] .= " activities"; }else{$d['searching'] .= " activity";} ?>
  $(".clearHistory[what='searchList']").attr('title','Clear these search result logs - <?php echo $d["searching"]; ?>').removeClass('nothing').show();
<?php }elseif($d['searching']==0 && $pagination['totalPage'] == 0){ ?>
  $(".clearHistory[what='searchList']").attr('title','Nothing to clear in this searched list').addClass('nothing').hide();
<?php }else{ ?>
  $(".clearHistory[what='searchList']").attr('title','Clear all activities').removeClass('nothing').show();
<?php } ?>

<?php if($uncompletedExists){ ?>
  $("#clearUncompleted").show();
<?php }else{ ?>
  $("#clearUncompleted").hide();
<?php } ?>

</script>