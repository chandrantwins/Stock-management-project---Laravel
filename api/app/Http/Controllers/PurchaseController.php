<?php

namespace App\Http\Controllers;
require_once(app_path() . '/constants.php');
use App\Login;
use Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Purchase;
use App\Order;
use App\Product;
use App\Common;
use DB;
use File;
use PDF;
use Request;
use Response;
use Mail;


class PurchaseController extends Controller { 

    public function __construct(Purchase $purchase,Common $common,Product $product,Order $order) 
    {
        parent::__construct();
        $this->purchase = $purchase;
        $this->product = $product;
        $this->order = $order;
        $this->common = $common;
    }

    public function createPO()
    {
        $post = Input::all();

        if(!empty($post['company_id']) && !empty($post['order_id']) && !empty($post['login_id']))
        {
            $po_type = !empty($post['po_type'])?$post['po_type']:'';
            $order_data = $this->purchase->getOrderData($post['company_id'],$post['order_id'],$po_type);
            
            if(count($order_data)>0)
            {
                $this->common->UpdateTableRecords('purchase_order',array('order_id'=>$post['order_id']),array('is_active'=>0),'');
                foreach ($order_data as $key=>$value) 
                {
                    $purchase_order_id = $this->purchase->insert_purchaseorder($post['order_id'],$key,'po',$post['company_id'],$post['login_id']);
                    $this->common->UpdateTableRecords('orders',array('id'=>$post['order_id']),array('is_complete'=>1),'');
                    /*if($purchase_order_id=='0')
                    {
                        $response = array('success' => 0, 'message' => "Purchase order is already created.");
                        return response()->json(["data" => $response]);
                    }
                    else
                    {*/
                        foreach($order_data[$key] as $detail_key=>$detail_value) 
                        {
                            $purchase_order_line = $this->purchase->insert_purchase_order_line($detail_value,$purchase_order_id);
                        }
                    //}
                }
                $response = array('success' => 1, 'message' => "Purchase order created successfully.",'data'=>$order_data);
            }
            else
            {
                $response = array('success' => 0, 'message' => "Please select Product.");
            }
        }
        else
        {
            $order_data='';
            $response = array('success' => 0, 'message' => MISSING_PARAMS);
        }
       // print_r($post);exit;
       return response()->json(["data" => $response]);
    }

    /*=====================================
    TO GET PO AND SG SCREEN FIELDS VALUES 
    =====================================*/

/** 
 * @SWG\Definition(
 *      definition="listPurchase",
 *      type="object",
 *      required={"company_id", "id"},
 *      @SWG\Property(
 *          property="company_id",
 *          type="integer"
 *      ),
 *      @SWG\Property(
 *          property="type",
 *          type="string"
 *      )
 * )
 */

 /**
 * @SWG\Post(
 *  path = "/api/public/purchase/ListPurchase",
 *  summary = "Purchasing List",
 *  tags={"Purchasing"},
 *  description = "Purchasing List",
 *  @SWG\Parameter(
 *     in="body",
 *     name="body",
 *     description="Purchasing List",
 *     required=true,
 *     @SWG\Schema(ref="#/definitions/listPurchase")
 *  ),
 *  @SWG\Response(response=200, description="Purchasing List"),
 *  @SWG\Response(response="default", description="Purchasing List"),
 * )
 */

/** 
 * @SWG\Definition(
 *      definition="listPurchase",
 *      type="object",
 *      required={"company_id", "id"},
 *      @SWG\Property(
 *          property="company_id",
 *          type="integer"
 *      ),
 *      @SWG\Property(
 *          property="type",
 *          type="string"
 *      )
 * )
 */

 /**
 * @SWG\Post(
 *  path = "/api/public/purchase/ListPurchase",
 *  summary = "Purchasing List",
 *  tags={"Purchasing"},
 *  description = "Purchasing List",
 *  @SWG\Parameter(
 *     in="body",
 *     name="body",
 *     description="Purchasing List",
 *     required=true,
 *     @SWG\Schema(ref="#/definitions/listPurchase")
 *  ),
 *  @SWG\Response(response=200, description="Purchasing List"),
 *  @SWG\Response(response="default", description="Purchasing List"),
 * )
 */

    public function ListPurchase()
    {
        $post = Input::all();
        
        //echo "<pre>"; print_r($post); echo "</pre>"; die;
        if(empty($post))
        {
            $response = array('success' => 0, 'message' => MISSING_PARAMS."- Po_type");
        }
        else
        {
            $result = $this->purchase->ListPurchase($post['type'],$post['company_id']);
            if (count($result) > 0) 
            {
                $response = array('success' => 1, 'message' => GET_RECORDS,'records' => $result);
            } 
            else 
            {
                $response = array('success' => 0, 'message' => NO_RECORDS,'records' => $result);
            }
        }
        return  response()->json(["data" => $response]);
    }

    /*=====================================
    / TO GET PO AND SG SCREEN DATA
    / ITS MAIN QUERY FOR WHOLE SCREEN DATA
    =====================================*/

    public function GetPodata($po_id,$company_id)
    {
        if(empty($po_id) || empty($company_id))
        {
            $response = array('success' => 0, 'message' => MISSING_PARAMS);
            return  response()->json(["data" => $response]);
            die();
        }
        else
        {
            $this->purchase->Update_Ordertotal($po_id,$company_id);
            $poline = $this->purchase->GetPoLinedata($po_id,$company_id);


            if(count($poline)>0)
            {
                
                $order_total = $this->purchase->getOrdarTotal($po_id,$company_id);
                $po_data = $poline[0];
                $result = array('po_data'=>$po_data,'poline'=>$poline,'order_total'=>$order_total);//,'received_total'=>$received_total,'received_line'=>$received_line,'order_id'=>$order_id,'list_vendors'=> $list_vendors );
                $response = array('success' => 1, 'message' => GET_RECORDS,'records' => $result);
            }
            else
            {
                $response = array('success' => 0, 'message' => NO_RECORDS);
                return  response()->json(["data" => $response]);
                die();
            }
        }
        return  response()->json(["data" => $response]);
    }

    /*=====================================
    TO UNASSIGN AND ASSIGN ORDER LINE ITEMS, CHANGE FLAG AND UPDATE PO
    =====================================*/

    public function ChangeOrderStatus($id,$val,$po_id)
    {
        if(empty($id))
        {
            $response = array('success' => 0, 'message' => MISSING_PARAMS."- id");
            return  response()->json(["data" => $response]);
            die();
        }
        else
        {
            $result = $this->purchase->ChangeOrderStatus($id,$val,$po_id);
            $response = array('success' => 1, 'message' => GET_RECORDS);
        }
        return  response()->json(["data" => $response]);
    }

    /*=====================================
    TO CALCULATION ONE PO AND SG SCREEN TOTAL AMOUNT
    =====================================*/

    public function EditOrderLine()
    {
         $post = Input::all();
         if(empty($post['po_id']) || empty($post['id']))
        {
            $response = array('success' => 0, 'message' => MISSING_PARAMS);
            return  response()->json(["data" => $response]);
            die();
        }
        else
        {
            $result = $this->purchase->EditOrderLine($post);
            $response = array('success' => 1, 'message' => UPDATE_RECORD);
            return  response()->json(["data" => $response]);
        }
    }
    /*=====================================
    TO CALCULATION ONE CP AND CE SCREEN TOTAL AMOUNT
    =====================================*/

    public function EditScreenLine()
    {
         $post = Input::all();
         $result = $this->purchase->EditScreenLine($post);
         $response = array('success' => 1, 'message' => GET_RECORDS);
         return  response()->json(["data" => $response]);
    }

    /*=====================================
    TO GET RECEIVED ORDER FOR PO AND SG TAB
    =====================================*/

    public function Receive_order()
    {
        $post = Input::all();
        $result = $this->purchase->Receive_order($post);
        $response = array('success' => 1, 'message' => GET_RECORDS);
        return  response()->json(["data" => $response]);
    }

    /*=====================================
    UPDATE SHIFTLOCK FIELD
    =====================================*/

    public function Update_shiftlock()
    {
        $post = Input::all();
        $result = $this->purchase->Update_shiftlock($post);
        $response = array('success' => 1, 'message' => UPDATE_RECORD);
        return  response()->json(["data" => $response]);
    }

    /*=====================================
    TO MAINTAIN SHORT AND OVER COUNT, MATCH RECEIVED QNTY WITH ORDER QNTY
    =====================================*/

    public function short_over($id)
    {   
        if(empty($id))
        {
            $response = array('success' => 0, 'message' => MISSING_PARAMS."- PoLine ID");
            return  response()->json(["data" => $response]);
            die();
        }
        else
        {
            $short_over = $this->purchase->short_over($id);
            $response = array('success' => 1, 'message' => UPDATE_RECORD);
            return  response()->json(["data" => $response]);
        }
    }

    /*=====================================
    TO GET SCREEN PRINT AND EMBRODIERY DATA
    =====================================*/

    public function GetPoReceived($po_id,$company_id)
    {

        if(empty($po_id) || empty($company_id))
        {
            $response = array('success' => 0, 'message' => MISSING_PARAMS."- po_id, company_id");
            return  response()->json(["data" => $response]);
            die();
        }
        else
        {
            //$this->purchase->Update_Ordertotal($po_id);
            $result = $this->purchase->GetPoReceived($po_id,$company_id);
           
            if(count($result)>0)
            {
                $order_total = $this->purchase->getOrdarTotal($po_id);
                $response = array('success' => 1, 'message' => GET_RECORDS,'records'=>$result,'order_total'=>$order_total);
            } 
            else
            {
                $response = array('success' => 0, 'message' => NO_RECORDS);
            }
        }
        return  response()->json(["data" => $response]);
    }

    public function getPurchaseNote($id)
    {
        $result = $this->purchase->getPurchaseNote($id);
        return $this->return_response($result);
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
            $response = array('success' => 0, 'message' => NO_RECORDS);
        }
        return  response()->json(["data" => $response]);
    }
    public function AllMsiData($compay_id)
    {
        $query = DB::table('misc_type')->where('company_id','=',$compay_id)->select('id','value','company_id')->get();
        $ret_array = array();
        foreach ($query as $key => $value) {
            $ret_array[$value->id] = $value->value;
        }

        //echo "<pre>"; print_r($query); echo "</pre>"; die;
        return $ret_array;
    }
    public function createPDF()
    {
        
        $pdf_array= json_decode($_POST['receiving']);
        
        if(count($pdf_array)>0)
        {
            $pdf_data = $this->purchase->GetPoReceived($pdf_array->po_id,$pdf_array->company_id);
            //echo "<pre>"; print_r($pdf_data); echo "</pre>"; die;
            if(count($pdf_data['receive'])>0)
            {
                $email_array = explode(",",$pdf_array->email);
                $file_path =  FILEUPLOAD.$pdf_array->company_id."/purchase/".$pdf_array->po_id;
               
                if (!file_exists($file_path)) { mkdir($file_path, 0777, true); } 
                else { exec("chmod $file_path 0777"); }
                
                PDF::AddPage('P','A4');
                PDF::writeHTML(view('pdf.receivepo',array('company'=>$pdf_data['po_data'],'receive_data'=>$pdf_data['receive']))->render());
           
                $pdf_url = "ReceivePO-".$pdf_array->po_id.".pdf"; 
                $filename = $file_path."/". $pdf_url;
                PDF::Output($filename, 'F');

                $login_email = $pdf_array->login_email;
                $login_name = $pdf_array->login_name;
                if(!empty($pdf_array->flag) && $pdf_array->flag=='1' && count($email_array)>0) // CHECK EMAIL ARRAY AND SEND MAIL CONDITION 
                {
                    foreach ($email_array as $email)
                    {

                        Mail::send('emails.receivepo', ['email'=>''], function($message) use ($pdf_data,$filename,$email,$login_email,$login_name)
                        {
                            $message->to(trim($email));
                            $message->replyTo($login_email,$login_name);
                            $message->subject('Receive order, for the Order '.$pdf_data['po_data']->order_name);
                            $message->attach($filename);
                        });
                    }
                }

                return Response::download($filename);
            }
            else
            {
                $response = array('success' => 0, 'message' => "Error, No Product or Size selected.");
                return  response()->json(["data" => $response]);
            }
        }
        else
        {
            $response = array('success' => 0, 'message' => MISSING_PARAMS);
            return  response()->json(["data" => $response]);
        }

    }
     public function PurchasePDF()
    {
        
        $pdf_array= json_decode($_POST['purchase']);
        
         if(!isset($pdf_array->mailMessage)){
          $pdf_array->mailMessage = '';
        }


        
        if(count($pdf_array)>0)
        {
            $order_total='';
            $pdf_data = $this->purchase->GetPoLinedata($pdf_array->po_id,$pdf_array->company_id);
            $positions_data = $this->purchase->GetPOpositions($pdf_array->po_id,$pdf_array->company_id);
            
            
            if(count($pdf_data)>0)
            {
                $order_total = $this->purchase->getOrdarTotal($pdf_array->po_id,$pdf_array->company_id);
                $email_array = explode(",",$pdf_array->email);
                $pass_array = array('company'=>$pdf_data['0'],'po_data'=>$pdf_data,'order_total'=>$order_total,'positions'=>$positions_data);
               
                
                $file_path =  FILEUPLOAD.$pdf_array->company_id."/purchase/".$pdf_array->po_id;
                if (!file_exists($file_path)) { mkdir($file_path, 0777, true); } 
                else { exec("chmod $file_path 0777"); }
                $pdf_url = "PurchaseOrder-".$pdf_array->po_id.".pdf"; 
                $filename = $file_path."/". $pdf_url;

                PDF::AddPage('P','A4');
                PDF::writeHTML(view('pdf.purchasepo',$pass_array)->render());
                PDF::Output($filename, 'F');


                //PDF::AddPage('P','A4');
                //PDF::writeHTML(view('pdf.api_label',$shipping)->render());
                //PDF::Output('api_label.pdf');

                if(!empty($pdf_array->flag) && $pdf_array->flag=='1' && count($email_array)>0) // CHECK EMAIL ARRAY AND SEND MAIL CONDITION 
                {
                    
                    $login_email = $pdf_array->login_email;
                    $login_name = $pdf_array->login_name;
                    foreach ($email_array as $email)
                    {
                        Mail::send('emails.purchasepo', ['email'=>'','mailMessage'=>$pdf_array->mailMessage], function($message) use ($pdf_data,$filename,$email,$login_email,$login_name)
                        {
                            $message->to(trim($email));
                            $message->replyTo($login_email,$login_name);
                            $message->subject('Purchase order, for the Order '.$pdf_data['0']->order_name);
                            $message->attach($filename);
                        });
                    }
                }

                return Response::download($filename);
            }
            else
            {
                $response = array('success' => 0, 'message' => "Sorry, No Products available for this Purchase.");
                return  response()->json(["data" => $response]);
            }
        }
        else
        {
            $response = array('success' => 0, 'message' => MISSING_PARAMS);
            return  response()->json(["data" => $response]);
        }

    }
    public function getAllReceiveProducts()
    {
        $post = Input::all();
        
        if(!empty($post['company_id']) && !empty($post['po_id']))
        {
           $this->purchase->getAllReceiveProducts($post['company_id'],$post['po_id']); 
           $response = array('success' => 1);
        }
        else
        {
            $response = array('success' => 0, 'message' => MISSING_PARAMS);
        }
       return response()->json(["data" => $response]);

    }

    public function DirectShipping()
    {
        $post = Input::all();

        if(!empty($post['company_id']) && !empty($post['order_id']) && !empty($post['login_id']))
        {
            $po_type = !empty($post['po_type'])?$post['po_type']:'';
            $order_data = $this->purchase->getOrderData($post['company_id'],$post['order_id'],$po_type);
            
            if(count($order_data)>0)
            {
                $this->common->UpdateTableRecords('purchase_order',array('order_id'=>$post['order_id']),array('is_active'=>0),''); // DEACTIVE PREVIOUS PURCHASE ORDER AND RECEIVE ORDER

                foreach ($order_data as $key=>$value) 
                {
                    $purchase_order_id = $this->purchase->insert_purchaseorder($post['order_id'],$key,'po',$post['company_id'],$post['login_id'],'1'); // CREATE PO VENDOR VICE
                    $this->common->UpdateTableRecords('orders',array('id'=>$post['order_id']),array('is_complete'=>1),'');
                    foreach($order_data[$key] as $detail_key=>$detail_value) 
                    {
                        $purchase_order_line = $this->purchase->insert_purchase_order_line($detail_value,$purchase_order_id,'1'); // CREATE PI LINEITEM AND RECEIVE ITEMS
                    }
                }
                $response = array('success' => 1, 'message' => "Direct Shipping Created.",'data'=>$order_data);
            }
            else
            {
                $response = array('success' => 0, 'message' => "Please select Product.");
            }
        }
        else
        {
            $order_data='';
            $response = array('success' => 0, 'message' => MISSING_PARAMS);
        }
       // print_r($post);exit;
       return response()->json(["data" => $response]);
    }

    
}