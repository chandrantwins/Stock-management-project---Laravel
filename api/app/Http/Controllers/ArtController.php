<?php

namespace App\Http\Controllers;
require_once(app_path() . '/constants.php');
use Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Common;
use App\Art;
use DB;

use Request;

class ArtController extends Controller { 

	public function __construct(Art $art,Common $common) 
 	{
        $this->art = $art;
        $this->common = $common;
    }

    // ART LISTING PAGE
    public function listing($company_id)
    {
    	if(!empty($company_id) 	&& $company_id != 'undefined')
    	{
    		
        	
    		$result = $this->art->listing($company_id);
    		if(count($result)>0)
    		{
    			$response = array('success' => 1, 'message' => GET_RECORDS,'records' => $result);
    		}
    		else
    		{
    			$response = array('success' => 0, 'message' => NO_RECORDS);
    		}
    		
		}
    	else 
        {
            $response = array('success' => 0, 'message' => MISSING_PARAMS,'records' => $result);
        }
        return  response()->json(["data" => $response]);
    }

    //ARTJOB-  ART DETAIL TAB WITH POSITION AND ORDERLINE TAB DATA.
    public function Art_detail($art_id,$company_id)
    {
    	if(!empty($company_id) && !empty($art_id)	&& $company_id != 'undefined')
    	{
    		$art_position = $this->art->art_position($art_id,$company_id);
    		$art_orderline = $this->art->art_orderline($art_id,$company_id);
			$artjobscreen_list = $this->art->artjobscreen_list($art_id,$company_id);
			$graphic_size = $this->common->GetMicType('graphic_size');
			$artjobgroup_list = $this->art->artjobgroup_list($art_id,$company_id);



    		$art_array  = array('art_position'=>$art_position,'art_orderline'=>$art_orderline,'artjobscreen_list'=>$artjobscreen_list,'graphic_size'=>$graphic_size,'artjobgroup_list'=>$artjobgroup_list);
    		$response = array('success' => 1, 'message' => GET_RECORDS,'records' => $art_array);
		}
    	else 
        {
            $response = array('success' => 2, 'message' => MISSING_PARAMS);
        }
        return  response()->json(["data" => $response]);
    }

    //ARTJOB-  ART WORKPROOF POPUP DATA RETRIVE
    public function artworkproof_data($orderline_id, $company_id)
    {
    	if(!empty($company_id) && !empty($orderline_id)	&& $company_id != 'undefined')
    	{
    		$art_workproof = $this->art->artworkproof_data($orderline_id,$company_id);
    		$response = array('success' => 1, 'message' => GET_RECORDS,'records' => $art_workproof);
    	}
    	else 
        {
            $response = array('success' => 0, 'message' => MISSING_PARAMS);
        }
        return  response()->json(["data" => $response]);
    }

    // ARTJOB-  SCREEN SETS TAB DATA LISTING
    public function artjobscreen_list($art_id, $company_id)
    {
    	if(!empty($company_id) && !empty($art_id)	&& $company_id != 'undefined')
    	{
    		$artjobscreen_list = $this->art->artjobscreen_list($art_id,$company_id);
    		$response = array('success' => 1, 'message' => GET_RECORDS,'records' => $artjobscreen_list);
    	}
    	else 
        {
            $response = array('success' => 0, 'message' => MISSING_PARAMS);
        }
        return  response()->json(["data" => $response]);
    }
    // ARTJOB-  GROUP TAB DATA LISTING
    public function artjobgroup_list($art_id, $company_id)
    {
    	if(!empty($company_id) && !empty($art_id)	&& $company_id != 'undefined')
    	{
    		$artjobgroup_list = $this->art->artjobgroup_list($art_id,$company_id);
    		$response = array('success' => 1, 'message' => GET_RECORDS,'records' => $artjobgroup_list);
    	}
    	else 
        {
            $response = array('success' => 0, 'message' => MISSING_PARAMS);
        }
        return  response()->json(["data" => $response]);
    }
    public function update_orderScreen()
    {
    	$post = Input::all();
    	if(!empty($post['data']) && !empty($post['cond']))
    	{
    		$artjobgroup_list = $this->art->update_orderScreen($post);
    		$response = array('success' => 1, 'message' => UPDATE_RECORD);
    	}
    	else 
        {
            $response = array('success' => 0, 'message' => MISSING_PARAMS);
        }
        return  response()->json(["data" => $response]);

    }
    public function ScreenListing($art_id,$company_id)
    {
    	if(!empty($company_id) && $company_id != 'undefined')
    	{
    		$scren_listing = $this->art->ScreenListing($art_id,$company_id);
    		if(count($scren_listing)>0)
    		{
    			$response = array('success' => 1, 'message' => GET_RECORDS,'records' => $scren_listing);
    		}
    		else
    		{
    			$response = array('success' => 0, 'message' => NO_RECORDS);
    		}
    	}
    	else 
        {
            $response = array('success' => 2, 'message' => MISSING_PARAMS);
        }
        return  response()->json(["data" => $response]);
    }

}