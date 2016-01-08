<?php

namespace App\Http\Controllers;
require_once(app_path() . '/constants.php');
use App\Login;
use Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Order;
use App\Common;
use App\Purchase;
use DB;
use App;
use Request;
use Barryvdh\DomPDF\PDF;


class OrderController extends Controller { 

	public function __construct(Order $order,Common $common,Purchase $purchase) 
 	{
        $this->order = $order;
        $this->purchase = $purchase;
        $this->common = $common;
    }

    /**
    * Get Array List of All Order details
    * @return json data
    */
    public function listOrder()
    {
        $post = Input::all();
    	$result = $this->order->getOrderdata($post[0]);
    	return $this->return_response($result);
    }
    /**
     * Delete Data
     *
     * @param  post.
     * @return success message.
     */
	public function DeleteOrder()
	{
		$post = Input::all();


		if(!empty($post[0]))
		{
			$getData = $this->order->deleteOrder($post[0]);
			if($getData)
			{
				$message = DELETE_RECORD;
				$success = 1;
			}
			else
			{
				$message = MISSING_PARAMS;
				$success = 0;
			}
		}
		else
		{
			$message = MISSING_PARAMS;
			$success = 0;
		}
		$data = array("success"=>$success,"message"=>$message);
		return response()->json(['data'=>$data]);
	}

    /**
    * Order Detail controller      
    * @access public detail
    * @param  array $data
    * @return json data
    */
    public function orderDetail() {
 
        $data = Input::all();

        $result = $this->order->orderDetail($data);

        if(empty($result['order']))
        {

           $response = array(
                                'success' => 0, 
                                'message' => NO_RECORDS
                                ); 
           return response()->json(["data" => $response]);
           

        }

        if(!empty($result['order_line_data']))
        {
            $sum = 0;
            foreach($result['order_line_data'] as $row)
            {
                $row->orderline_id = $row->id;
                $row->products = $this->common->GetTableRecords('products',array('vendor_id' => $row->vendor_id),array());
                $row->colors = $this->order->GetProductColor($row->product_id);

                $order_line_items = $this->order->getOrderLineItemById($row->id);
                $count = 1;
                $order_line = array();
                foreach ($order_line_items as $line) {
                 
                    $line->number = $count;
                    $order_line[] = $line;
                    $count++;
                }
                $row->items = $order_line;

                $result['order_line'][] = $row;
            }
        }
        else
        {
            $result['order_line'] = array();
        }
        $order_items = $this->order->getOrderItemById($result['order'][0]->price_id);

        if(!empty($order_items))
        {
            $items = $this->order->getItemsByOrder($data['id']);

            foreach ($order_items as $order_item)
            {
                $i = 0;
                foreach ($items as $item)
                {
                    if($item->item_id == $order_item->id)
                    {
                        $i = 1;
                    }
                }
                
                if($i == 1)
                {
                    $order_item->selected = '1';
                    $result['order_item'][] = $order_item;
                }
                else
                {
                    $order_item->selected = '0';
                    $result['order_item'][] = $order_item;
                }
            }
        }
        else
        {
            $result['order_item'] = array();
        }

        $price_id = $result['order'][0]->price_id;
        $client_id = $result['order'][0]->client_id;

        
        $price_garment_mackup = array();
        $price_screen_primary = array();
        $price_screen_secondary = array();
        $price_direct_garment = array();
        $embroidery_switch_count = array();

        $price_grid = $this->common->GetTableRecords('price_grid',array('id' => $price_id),array());
        if($price_id > 0)
        {
            $price_garment_mackup = $this->common->GetTableRecords('price_garment_mackup',array('price_id' => $price_id),array());
            $price_screen_primary = $this->common->GetTableRecords('price_screen_primary',array('price_id' => $price_id),array());
            $price_screen_secondary = $this->common->GetTableRecords('price_screen_secondary',array('price_id' => $price_id),array());
            $price_direct_garment = $this->common->GetTableRecords('price_direct_garment',array('price_id' => $price_id),array());
            $embroidery_switch_count = $this->common->GetTableRecords('embroidery_switch_count',array('price_id' => $price_id),array());
        }

        $client = $this->common->GetTableRecords('client',array('status' => '1','is_delete' => '1','company_id' => $data['company_id']),array());
        $products = $this->common->GetTableRecords('products',array('status' => '1','is_delete' => '1'),array());

        $vendors = $this->common->getAllVendors();
        $staff = $this->common->getStaffList();
        $brandCo = $this->common->getBrandCordinator();

        if (count($result) > 0) {
            $response = array(
                                'success' => 1, 
                                'message' => GET_RECORDS,
                                'records' => $result['order'],
                                'client_data' => $result['client_data'],
                                'client_main_data' => $result['client_main_data'],
                                'order_position' => $result['order_position'],
                                'order_line' => $result['order_line'],
                                'order_item' => $result['order_item'],
                                'price_grid' => $price_grid,
                                'price_garment_mackup' => $price_garment_mackup,
                                'price_screen_primary' => $price_screen_primary,
                                'price_screen_secondary' => $price_screen_secondary,
                                'price_direct_garment' => $price_direct_garment,
                                'embroidery_switch_count' => $embroidery_switch_count,
                                'vendors' => $vendors,
                                'client' => $client,
                                'products' => $products,
                                'staff' => $staff,
                                'brandCo' => $brandCo
                                );
        } else {
            $response = array(
                                'success' => 0, 
                                'message' => NO_RECORDS,
                                'records' => $result['order'],
                                'client_data' => $result['client_data'],
                                'client_main_data' => $result['client_main_data'],
                                'order_position' => $result['order_position'],
                                'order_line' => $result['order_line'],
                                'order_item' => $result['order_item'],
                                'order_po_data' => $result['order_po_data']);

        } 
        
        return response()->json(["data" => $response]);

    }


   /**
   * Get Order notes.
   * @return json data
   */
    public function getOrderNoteDetails($id)
    {

        $result = $this->order->getOrderNoteDetails($id);
        return $this->return_response($result);
        
    }

    /**
    * Get Client Details by ID
    * @params order_id
    * @return json data
    */
    public function getOrderDetailById($id)
    {
        $result = $this->order->getOrderDetailById($id);
        return $this->return_response($result);
    }


    /**
    * Update Order Note tab record
    * @params order note array
    * @return json data
    */
    public function updateOrderNotes()
    {
        $post = Input::all();
        $result = $this->order->updateOrderNotes($post['data'][0]);
        $data = array("success"=>1,"message"=>UPDATE_RECORD);
        return response()->json(['data'=>$data]);
    }

    /**
    * Delete order note tab record.
    * @params note_id
    * @return json data
    */
    public function deleteOrderNotes($id)
    {
        $result = $this->order->deleteOrderNotes($id);
        $data = array("success"=>1,"message"=>UPDATE_RECORD);
        return response()->json(['data'=>$data]);
    }


   /**
   * Save Order notes.
   * @return json data
    */
    public function saveOrderNotes()
    {

        $post = Input::all();
        $post['data']['created_date']=date('Y-m-d');
 
        if(!empty($post['data']['order_id']) && !empty($post['data']['order_notes']))
        {
            $result = $this->order->saveOrderNotes($post['data']);
            $message = INSERT_RECORD;
            $success = 1;
        }
        else
        {
            $message = MISSING_PARAMS.", id";
            $success = 0;
        }
        
        $data = array("success"=>$success,"message"=>$message);
        return response()->json(['data'=>$data]);
    }

    /**
    * Save Orderline order.
    * @return json data
    */
    public function orderLineadd()
    {

        $post = Input::all();
        

        $post['data']['created_date']=date('Y-m-d');
 
       
            $result = $this->order->saveOrderLineData($post['data']);
            $message = INSERT_RECORD;
            $success = 1;
        
        $data = array("success"=>$success,"message"=>$message);
        return response()->json(['data'=>$data]);
    }


    /**
   * Update Orderline order.
   * @return json data
    */
    public function orderLineupdate()
    {
        $post = Input::all();

        $post['data']['created_date']=date('Y-m-d');
       
        $result = $this->order->updateOrderLineData($post['data']);
        $message = INSERT_RECORD;
        $success = 1;
        
        $data = array("success"=>$success,"message"=>$message);
        return response()->json(['data'=>$data]);
    }

     /**
   * delete order line.
   * @return json data
    */
    public function deleteOrderLine()
    {
        $post = Input::all();
       
        $this->common->DeleteTableRecords('order_orderlines',array('id' => $post['id']));

        $purchase_detail = $this->common->GetTableRecords('purchase_detail',array('orderline_id' => $post['id']),array());

        foreach ($purchase_detail as $row) {
            $this->common->DeleteTableRecords('item_address_mapping',array('item_id' => $row->id));
        }
                
        $data = array("success"=>1,"message"=>UPDATE_RECORD);
        return response()->json(['data'=>$data]);
    }    


   /**
   * Save Button Data.
   * @return json data
   */

    public function saveButtonData()
    {
        $post = Input::all();

        $post['created_date']=date('Y-m-d');
        
        $result = $this->order->saveButtonData($post);
        $message = INSERT_RECORD;
        $success = 1;
        
        $data = array("success"=>$success,"message"=>$message);
        return response()->json(['data'=>$data]);
    }



    /**
    * Insert record for any single table.
    * @params Table name, Post array
    * @return json data
    */
     public function insertPositions()
     {
        $post = Input::all();

        //echo "<pre>"; print_r($post); echo "</pre>"; die;
        if(!empty($post['table']) && !empty($post['data']))
        {
            $result = $this->order->insertPositions($post['table'],$post['data']);
            $id = $result;
            $message = INSERT_RECORD;
            $success = 1;
        }
        else
        {
            $message = MISSING_PARAMS;
            $success = 0;
        }
        
        $data = array("success"=>$success,"message"=>$message,"id"=>$id);
        return response()->json(['data'=>$data]);
     }
    

    /**
    * UPDATE record for any single table.
    * @params Table name, Condition array, Post array
    * @return json data
    */
     public function updatePositions()
     {
        $post = Input::all();
        //echo "<pre>"; print_r($post); echo "</pre>"; die;

        if(!empty($post['table']) && !empty($post['data'])  && !empty($post['cond']))
        {
          $result = $this->order->updatePositions($post['table'],$post['cond'],$post['data']);
          $data = array("success"=>1,"message"=>UPDATE_RECORD);
        }
        else
        {
            $data = array("success"=>0,"message"=>MISSING_PARAMS);
        }
        return response()->json(['data'=>$data]);
     }


/**
    * Save po.
    * @return json data
    */
    public function savePO()
    {
        $post = Input::all();

      
        $post['created_date']=date('Y-m-d');
        $result = $this->order->savePO($post);
        $message = INSERT_RECORD;
        $success = 1;
        
        $data = array("success"=>$success,"message"=>$message);
        return response()->json(['data'=>$data]);
    }



    /**
    * Order Detail controller      
    * @access public detail
    * @param  array $data
    * @return json data
    */
    public function PODetail() {
 
        $data = Input::all();

        $result = $this->order->POorderDetail($data);

       
        if (count($result) > 0) {
            $response = array('success' => 1, 'message' => GET_RECORDS,'order_po_data' => $result['order_po_data']);
        } else {
            $response = array('success' => 0, 'message' => NO_RECORDS,'order_po_data' => $result['order_po_data']);
        }
        
        return response()->json(["data" => $response]);

    }

	/**
    * Get Array
    * @return json data
    */
    public function return_response($result)
    {
        if (count($result) > 0) 
        {
            $response = array('success' => 1, 'message' => GET_RECORDS,'records' => $result);
        } 
        else 
        {
            $response = array('success' => 0, 'message' => NO_RECORDS,'records' => $result);
        }
        return  response()->json(["data" => $response]);
    }

    /**
    * Order Detail controller
    * @access public detail
    * @param  array $data
    * @return json data
    */
    public function distributionDetail()
    {
        $data = Input::all();
        $dist_addr = $this->common->GetTableRecords('client_distaddress',array('client_id' => $data['client_id']),array());

        $client_distaddress = array();
        foreach ($dist_addr as $addr) {
            $addr->full_address = $addr->address ." ". $addr->address2 ." ". $addr->city ." ". $addr->state ." ". $addr->zipcode ." ".$addr->country;
            $client_distaddress[] = $addr;
        }

        $array = array('order.id' => $data['order_id'],'is_distribute' => '0');
        $order_items = $this->order->getDistributionItems($array);

        if(empty($order_items))
        {
            $this->common->UpdateTableRecords('orders',array('id' => $data['order_id']),array('fully_shipped' => date('Y-m-d')));
        }
        else
        {
            $this->common->UpdateTableRecords('orders',array('id' => $data['order_id']),array('fully_shipped' => ''));        
        }

        if(isset($data['address_id']) && !empty($data['address_id']))
        {
            $array2 = array('order.id' => $data['order_id'],'is_distribute' => '1','ia.address_id' => $data['address_id']);
            $distributed_items = $this->order->getDistributedItems($array2);
        }
        else
        {
            $distributed_items = array();
        }

        $array3 = array('ia.order_id' => $data['order_id']);
        $distributed_address = $this->order->getDistributedAddress($array3);

        $distributed_address2 = array();

        foreach ($distributed_address as $addr) {
            $addr->full_address = $addr->address ." ". $addr->address2 ." ". $addr->city ." ". $addr->state ." ". $addr->zipcode ." ".$addr->country;
            $box_arr = $this->common->GetTableRecords('shipping_box',array('shipping_id' => $addr->shipping_id),array());

            $actual_total = 0;
            foreach ($box_arr as $row) {
                $actual_total += $row->actual;
            }
            
            $addr->total_box = count($box_arr);
            $addr->actual_total = $actual_total;
            $distributed_address2[] = $addr;
        }


        if (count($client_distaddress) > 0) {
            $response = array(
                                'success' => 1, 
                                'message' => GET_RECORDS,
                                'dist_addr' => $client_distaddress,
                                'order_items' => $order_items,
                                'distributed_items' => $distributed_items,
                                'distributed_address' => $distributed_address2
                            );
        } else {
            $response = array(
                                'success' => 0, 
                                'message' => NO_RECORDS
                                );
        } 
        
        return response()->json(["data" => $response]);
    }

    public function addToDistribute()
    {
        $post = Input::all();

        if(!isset($post['item_id']))
        {
            $post['data'] = $post;

            $post['cond'] = array('order_id' => $post['order_id'],'address_id' => $post['address_id']);
            $post['notcond'] = array();

            $result = $this->common->GetTableRecords('item_address_mapping',$post['cond'],$post['notcond']);
            if(empty($result))
            {
                $shipping_arr = array('order_id' => $post['order_id'],'address_id' => $post['address_id'],'shipping_by' => date('Y-m-d', strtotime("+9 days")),'in_hands_by' => date('Y-m-d', strtotime("+14 days")));
                $shipping_id = $this->common->InsertRecords('shipping',$shipping_arr);

                $post['data']['shipping_id'] = $shipping_id;

                $result = $this->common->InsertRecords('item_address_mapping',$post['data']);
                $id = $result;
            }
            $message = INSERT_RECORD;
            $success = 1;
        }
        else
        {
            $arr = $this->common->GetTableRecords('item_address_mapping',array('order_id' => $post['order_id'],'address_id' => $post['address_id']),array());
            $post['shipping_id'] = $arr[0]->shipping_id;
            $result = $this->common->InsertRecords('item_address_mapping',$post);
            $this->common->UpdateTableRecords('distribution_detail',array('id' => $post['item_id']),array('is_distribute' => '1'));
            
            $success=1;
            $message=UPDATE_RECORD;
        }
        $this->common->UpdateTableRecords('orders',array('id' => $post['order_id']),array('shipping_by' => date('Y-m-d', strtotime("+9 days")),'in_hands_by' => date('Y-m-d', strtotime("+14 days"))));
        $data = array("success"=>$success,"message"=>$message);
        return response()->json(['data'=>$data]);
    }
    public function removeFromDistribute()
    {
        $post = Input::all();

        if(!isset($post['item_id']))
        {
            $item_data = $this->common->GetTableRecords('item_address_mapping',array('order_id' => $post['order_id'],'address_id' => $post['address_id']),array());

            foreach ($item_data as $item) {
                if($item->item_id > 0)
                {
                    $this->common->UpdateTableRecords('distribution_detail',array('id' => $item->item_id),array('is_distribute' => '0'));
                }
            }

            $post['cond'] = array('order_id' => $post['order_id'],'address_id' => $post['address_id']);

            $this->common->DeleteTableRecords('item_address_mapping',$post['cond']);
            $this->common->DeleteTableRecords('shipping',$post['cond']);

            $data = array("success"=>1,"message"=>UPDATE_RECORD);
        }
        else
        {
            $this->common->UpdateTableRecords('distribution_detail',array('id' => $post['item_id']),array('is_distribute' => '0'));
            
            $post['cond'] = array('order_id' => $post['order_id'],'item_id' => $post['item_id']);
            $this->common->DeleteTableRecords('item_address_mapping',$post['cond']);

            $data = array("success"=>1,"message"=>UPDATE_RECORD);
        }
        return response()->json(['data'=>$data]);
    }



    /*=====================================
    / TO GET PO AND SG SCREEN DATA
    =====================================*/

    public function GetPodataAll($po_id)
    {
       
        $poline = $this->purchase->GetPoLinedata($po_id,'1');
              
        $result = array('poline'=>$poline);
        $response = array('success' => 1, 'message' => GET_RECORDS,'records' => $result);
        return  response()->json(["data" => $response]);
    }

    public function updateOrderTask()
    {
        $post = Input::all();

        if(isset($post['data']['task_name']) && $post['data']['task_name'] != '')
        {
            $task_arr = $this->common->GetTableRecords('task',array('task_name' => $post['data']['task_name']),array());

            if(empty($task_arr))
            {
                $task_id = $this->common->InsertRecords('task',array('task_name' => $post['data']['task_name']));
            }
            else
            {
                $task_id = $task_arr[0]->id;
            }
            $post['data']['task_id'] = $task_id;
            unset($post['data']['task_name']);
        }

        if(isset($post['data']['result_name']) && $post['data']['result_name'] != '')
        {
            $result_arr = $this->common->GetTableRecords('result',array('result_name' => $post['data']['result_name']),array());

            if(empty($result_arr))
            {
                $result_id = $this->common->InsertRecords('result',array('result_name' => $post['data']['result_name']));
            }
            else
            {
                $result_id = $result_arr[0]->id;
            }
            $post['data']['result_id'] = $result_id;
            unset($post['data']['result_name']);
        }

        $post['data']['user_id'] = implode(',', $post['data']['user_id']);

        if($post['action'] == 'update')
        {
            $this->common->UpdateTableRecords('order_tasks',$post['cond'],$post['data']);
            $data = array("success"=>1,"message"=>UPDATE_RECORD);
        }
        else
        {
            $post['data']['date_added'] = date('Y-m-d');
            $this->common->InsertRecords('order_tasks',$post['data']);
            $data = array("success"=>1,"message"=>UPDATE_RECORD);
        }
        return response()->json(['data'=>$data]);
    }

    public function updateDistributedQty()
    {
        $post = Input::all();
        $dist_addr = $this->common->GetTableRecords('distribution_detail',array('id' => $post['id']),array());
        $qty = $dist_addr[0]->qnty - $post['qty'];

        $this->common->UpdateTableRecords('distribution_detail',array('id' => $post['id']),array('qnty' => $post['qty']));

        if($qty > 0)
        {
            $insert_data = array(
                                'orderline_id' => $dist_addr[0]->orderline_id,
                                'order_id' => $dist_addr[0]->order_id,
                                'size' => $dist_addr[0]->size,
                                'qnty' => $qty,
                                'status' => '1',
                                'date' => $dist_addr[0]->date,
                                'is_distribute' => '0'
                                );

            $this->common->InsertRecords('distribution_detail',$insert_data);
        }
        $data = array("success"=>1,"message"=>UPDATE_RECORD);
        return response()->json(['data'=>$data]);
    }

    /**
* Duplicate PO       
* @access public duplicatePoData
* @param  array $data
* @return json data
*/
    public function duplicatePoData() {

       $post = Input::all();
        $post['data']['created_date']=date('Y-m-d');
        $post['data']['po_id']=$post[0];
       
       
        $result = $this->order->poDuplicate($post['data']);
          
        $data = array("success"=>1,"message"=>INSERT_RECORD);
        
        return response()->json(["data" => $data]);
    }

    public function getTaskList()
    {
        $post = Input::all();
        $task_detail = $this->order->getTaskList($post['order_id']);

        if (count($task_detail) > 0) {
            $response = array(
                                'success' => 1, 
                                'message' => GET_RECORDS,
                                'task_detail' => $task_detail
                            );
        } else {
            $response = array(
                                'success' => 0, 
                                'message' => NO_RECORDS,
                                'task_detail' => array()
                                );
        } 
        
        return response()->json(["data" => $response]);
    }

    public function getTaskDetails()
    {
        $post = Input::all();
        $users = $this->common->GetTableRecords('users',array(),array('role_id' => '7'));
        $task = $this->common->GetTableRecords('task',array(),array());
        $result = $this->common->GetTableRecords('result',array(),array());

        $task_detail = array();

        if(!empty($post['id']) > 0)
        {
            $task_detail = $this->order->getTaskDetail($post['id']);
            $task_detail[0]->user_id = explode(',', $task_detail[0]->user_id);
        }

        $response = array(
                                'success' => 1, 
                                'message' => GET_RECORDS,
                                'users' => $users,
                                'tasks' => $task,
                                'result' => $result,
                                'task_detail' => $task_detail
                            );
        return response()->json(["data" => $response]);
    }


     /**
   * Save Color size.
   * @return json data
    */
    public function saveColorSize()
    {
        $post = Input::all();
       
        if(!empty($post['color_id']) && !empty($post['product_id']))
        {
            $result = $this->order->saveColorSize($post);
            $message = INSERT_RECORD;
            $success = 1;
        }
        else
        {
            $message = MISSING_PARAMS.", id";
            $success = 0;
        }
        
        $data = array("success"=>$success,"message"=>$message);
        return response()->json(['data'=>$data]);
    }

   /**
   * Get Product Color Size.
   * @return json data
   */
    public function getProductDetailColorSize($id)
    {
        $result = $this->order->getProductDetailColorSize($id);
        return $this->return_response($result);
    }

     /**
   * Save Color size.
   * @return json data
    */
    public function savePDF()
    {
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML('<h1>Test</h1>');
        return $pdf->stream();
    }

    public function AssignSize()
    {
        $post = Input::all();
        $purchase_detail = $this->common->GetTableRecords('purchase_detail',array('orderline_id' => $post['orderline_id']),array());
        $sizeData = $this->order->getOrderLineItemByColor($post['product_id'],$post['color_id']);

        foreach ($purchase_detail as $key => $value) {
            
            $update_data = array('size' => $sizeData[$key]->size,
                                'price' => $sizeData[$key]->price
                                );

            $this->common->UpdateTableRecords('purchase_detail',array('id' => $value->id),$update_data);
            $this->common->UpdateTableRecords('distribution_detail',array('id' => $value->id),$update_data);
        }
        $data = array("success"=>1,"message"=>INSERT_RECORD);
        return response()->json(['data'=>$data]);
    }
}