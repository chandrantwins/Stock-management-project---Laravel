<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use DateTime;

class Shipping extends Model {

	
	public function getShippingList($post)
	{
        DB::enableQueryLog();

        $search = '';
        if(isset($post['filter']['name'])) {
            $search = $post['filter']['name'];
        }

        $listArray = [DB::raw('SQL_CALC_FOUND_ROWS o.id,o.display_number,o.name,c.client_company,SUM(pas.distributed_qnty) as distributed,SUM(pol.qnty_purchased - pol.short) as total,misc_type.value as approval,o.approval_id')];


        $shippingData = DB::table('orders as o')
                        ->leftJoin('client as c', 'o.client_id', '=', 'c.client_id')
                        ->leftJoin('purchase_order as po', 'po.order_id', '=', 'o.id')
                        ->leftJoin('purchase_order_line as pol','pol.po_id','=','po.po_id')
                        ->leftJoin('product_address_size_mapping as pas','pol.purchase_detail','=','pas.purchase_detail_id')
                        ->leftJoin('misc_type as misc_type','o.approval_id','=',DB::raw("misc_type.id AND misc_type.company_id = ".$post['company_id']))
                        ->select($listArray)
                        ->where('o.is_complete','=','1')
                        ->where('po.is_active','=','1')
                        ->where('o.company_id','=',$post['company_id']);
                        if($post['type'] == 'wait')
                        {
                            $shippingData = $shippingData->where('pol.qnty_purchased','>',0);
                            $shippingData = $shippingData->Where(function($query) use($search)
                            {
                                $query->orWhere('pas.distributed_qnty', '=', 0)
                                ->orWhere('pas.distributed_qnty', '=', NULL)
                                ->orWhere('pas.distributed_qnty', '=', '');
                            });
                        }
                        elseif($post['type'] == 'progress')
                        {
                            $shippingData = $shippingData->havingRaw('distributed < total');
                        }
                        else
                        {
                            $shippingData = $shippingData->where('pas.distributed_qnty','>',0);
                        }
                        if($search != '')
                        {
                            $shippingData = $shippingData->Where(function($query) use($search)
                            {
                                $query->orWhere('o.display_number', 'LIKE', '%'.$search.'%')
                                ->orWhere('misc_type.value', 'LIKE', '%'.$search.'%')   
                                ->orWhere('c.client_company', 'LIKE', '%'.$search.'%');
                            });
                        }
                        if($post['type'] == 'progress')
                        {
                            $shippingData = $shippingData->where('pas.distributed_qnty','>',0);
                        }
                        if($post['type'] == 'shipped')
                        {
                            $shippingData = $shippingData->havingRaw('distributed = total');
                        }
                        $shippingData = $shippingData->GroupBy('o.id');
                        $shippingData = $shippingData->orderBy($post['sorts']['sortBy'], $post['sorts']['sortOrder'])
                        ->skip($post['start'])
                        ->take($post['range'])
                        ->get();

        $count  = DB::select( DB::raw("SELECT FOUND_ROWS() AS Totalcount;") );

        if($post['type'] == 'shipped')
        {
            if(!empty($shippingData))
            {
                foreach ($shippingData as $shipping)
                {
                    $shipping_data = DB::table('shipping as s')
                                        ->leftJoin('orders as o', 's.order_id', '=', 'o.id')
                                        ->select('s.id','o.date_shipped as shipping_by','o.in_hands_by','s.display_number')
                                        ->where('s.order_id','=',$shipping->id)
                                        ->get();
                    
                    if(empty($shipping_data))
                    {
                        $shipping->shipping_created = 0;
                        $shipping->shipping_data = array();
                    }
                    else
                    {
                        $shipping->shipping_created = 1;
                        $shipping->shipping_data = $shipping_data;
                    }
                }
            }
        }

        if($post['type'] == 'wait')
        {
            foreach ($shippingData as $data)
            {
                $purchase_detail = DB::select("SELECT pol.purchase_detail, pol.qnty_purchased - pol.short as total FROM purchase_order as po 
                                            LEFT JOIN purchase_order_line as pol ON pol.po_id = po.po_id WHERE po.order_id = '".$data->id."' ");
                foreach($purchase_detail as $row)
                {
                    $value = DB::table('purchase_detail')
                            ->where('id','=',$row->purchase_detail)
                            ->update(array('remaining_qnty'=>$row->total));
                }            
            }
        }

        $returnData['allData'] = $shippingData;
        $returnData['count'] = $count[0]->Totalcount;

        return $returnData;

	}

/**
* Shipping Detail           
* @access public orderDetail
* @param  int $orderId and $clientId
* @return array $combine_array
*/

    public function shippingDetail($data) {

        $whereShippingConditions = ['s.id' => $data['shipping_id']];

        $listArray = ['s.id as shipping_id','mt.value as job_status','o.id as order_id','o.name','cd.id as client_distribution_id','o.client_id','c.client_company','o.approval_id','misc_type.value as approval','s.cost_to_ship','s.tracking_number','o.display_number',
                        's.boxing_type','o.date_shipped as shipping_by','o.in_hands_by','s.shipping_type_id','s.date_shipped','s.fully_shipped','s.shipping_note','s.cost_to_ship','cd.*','o.f_approval','s.sku','st.code','s.shipping_method','s.shipping_status',
                        'o.date_shipped','o.in_hands_by',DB::raw('SUM(pas.distributed_qnty) as distributed,SUM(pol.qnty_purchased - pol.short) as total'),'o.custom_po','s.shipping_note','s.display_number as shipping_display_number','c.display_number as client_display_number'];
       
        $shippingData = DB::table('shipping as s')
                        ->leftJoin('orders as o','s.order_id','=','o.id')
                        ->leftJoin('misc_type as mt','mt.id','=','o.f_approval')
                        ->leftJoin('client as c','o.client_id','=','c.client_id')
                        ->leftJoin('client_distaddress as cd','s.address_id','=','cd.id')
                        ->leftJoin('state as st','cd.state','=','st.id')
                        ->leftJoin('misc_type as misc_type','o.approval_id','=',DB::raw("misc_type.id AND misc_type.company_id = ".$data['company_id']))
                        ->leftJoin('purchase_order as po', 'po.order_id', '=', 'o.id')
                        ->leftJoin('purchase_order_line as pol','pol.po_id','=','po.po_id')
                        ->leftJoin('product_address_size_mapping as pas','pol.purchase_detail','=','pas.purchase_detail_id')
                        ->select($listArray)
                        ->where($whereShippingConditions)
                        ->GroupBy('o.id')->get();

        $whereItemConditions = ['pam.shipping_id' => $data['shipping_id']];
        $listItemsArray = ['pam.shipping_id','pd.id','pd.size','pd.qnty','pd.shipped_qnty','pd.boxed_qnty','pd.remaining_to_box','pd.max_pack','pd.hoody','p.name as product_name','mt.value as size_group_name','c.name as color_name'];

        if(isset($data['overview']))
        {
            $GroupBy = 'p.id';
        }
        else
        {
            $GroupBy = 'pd.id';
        }

        $shippingItems = DB::select("SELECT mt.value as misc_value,p.name,p.id as product_id,c.name as color_name,p.description,pd.id,pd.size,pol.qnty_purchased - pol.short as total, 
                                    pas.distributed_qnty as qnty,pd.remaining_qnty,pd.distributed_qnty,pas.product_address_id ,pd.boxed_qnty,
                                    pd.remaining_to_box,pd.max_pack,pd.hoody,pam.shipping_id,pd.design_product_id
                                FROM product_address_mapping as pam 
                                LEFT JOIN product_address_size_mapping as pas ON pam.id = pas.product_address_id 
                                LEFT JOIN purchase_detail as pd ON pas.purchase_detail_id = pd.id 
                                LEFT JOIN purchase_order_line as pol ON pd.id = pol.purchase_detail 
                                LEFT JOIN design_product as dp ON pd.design_product_id = dp.id
                                LEFT JOIN products as p ON dp.product_id = p.id
                                LEFT JOIN misc_type as mt ON dp.size_group_id = mt.id
                                LEFT JOIN color as c ON pd.color_id = c.id
                                WHERE pam.shipping_id = '".$data['shipping_id']."' 
                                GROUP BY ".$GroupBy." ");

        $combine_array = array();

        $combine_array['shipping'] = $shippingData;
        $combine_array['shippingItems'] = $shippingItems;

        return $combine_array;
    }

    public function getBoxItems($box_id)
    {
        $whereItemConditions = ['bi.box_id' => $box_id];
        $listItemsArray = ['sb.md','sb.id as box_id','sb.spoil','sb.actual','sb.re_allocate_to','bi.id as box_item_id','bi.item_id','pd.id','pd.size','sb.box_qnty as boxed_qnty','pd.remaining_to_box','pd.max_pack','pd.hoody','p.name as product_name','mt.value as size_group_name','c.name as color_name'];

        $shippingBoxItems = DB::table('box_product_mapping as bi')
                        ->leftJoin('shipping_box as sb','bi.box_id','=','sb.id')
                        ->leftJoin('purchase_detail as pd','bi.item_id','=','pd.id')
                        ->leftJoin('design_product as dp','pd.design_product_id','=','dp.id')
                        ->leftJoin('products as p','dp.product_id','=','p.id')
                        ->leftJoin('misc_type as mt','mt.id','=','dp.size_group_id')
                        ->leftJoin('color as c','pd.color_id','=','c.id')
                        ->select($listItemsArray)
                        ->where($whereItemConditions)
                        ->where('bi.item_id','!=','0')
                        ->get();

        return $shippingBoxItems;
    }

    public function deleteBox($id)
    {
        if(!empty($id))
        {
                $result = DB::table('shipping_box')->where('id', $id)->delete();
                $result = DB::table('box_product_mapping')->where('box_id', $id)->delete();
                return $result;
        }
        else
        {
                return false;
        }
    }

    public function getUnshippedProducts($order_id)
    {
        $listArr = ['mt.value as misc_value','p.name','c.name as color_name','p.description','pd.id','pd.size','pol.qnty_purchased','pd.remaining_qnty'];
        $where = ['po.order_id' => $order_id];

        $result = DB::select("SELECT mt.value as misc_value,p.name,p.id as product_id,c.name as color_name,p.description,pd.id,pd.size,pol.qnty_purchased - pol.short as total,pd.remaining_qnty,pd.distributed_qnty,pas.product_address_id 
                                FROM purchase_order as po 
                                LEFT JOIN purchase_order_line as pol ON po.po_id = pol.po_id 
                                LEFT JOIN purchase_detail as pd ON pol.purchase_detail = pd.id 
                                LEFT JOIN product_address_size_mapping as pas ON pol.purchase_detail = pas.purchase_detail_id 
                                LEFT JOIN design_product as dp ON pd.design_product_id = dp.id
                                LEFT JOIN products as p ON dp.product_id = p.id
                                LEFT JOIN misc_type as mt ON dp.size_group_id = mt.id
                                LEFT JOIN color as c ON pd.color_id = c.id
                                WHERE po.order_id = '".$order_id."' AND pol.qnty_purchased > 0 AND pd.remaining_qnty > 0 
                                GROUP BY pd.id ");
        return $result;
    }

    public function getAllocatedAddress($data)
    {
        $result = DB::table('client_distaddress as cd')
                    ->leftJoin('product_address_mapping as pam','cd.id','=','pam.address_id')
                    ->select(DB::raw('GROUP_CONCAT(cd.id) as id'))
                    ->where('pam.order_id','=',$data['id'])
                    ->GroupBy('pam.order_id')
                    ->get();

        return $result;
    }

    public function getUnAllocatedAddress($data)
    {
        $result = DB::table('product_address_mapping as pam')
                    ->rightJoin('client_distaddress as cd','cd.id','=','pam.address_id')
                    ->select('cd.*')
                    ->where('pam.order_id','=',$data->id)
                    ->where('pam.address_id','=',NULL)
                    ->where('cd.client_id','=',$data->client_id)
                    ->get();

        return $result;
    }

    public function getProductByAddress($data)
    {
        $listArr = ['mt.value as misc_value','p.name','c.name as color_name','p.description','pd.id','pd.size','pas.distributed_qnty'];
        $where = ['pam.order_id' => $data['order_id'], 'pam.address_id' => $data['address_id']];

        $result = DB::table('product_address_mapping as pam')
                    ->leftJoin('product_address_size_mapping as pas','pam.id','=','pas.product_address_id')
                    ->leftJoin('purchase_detail as pd','pas.purchase_detail_id','=','pd.id')
                    ->leftJoin('design_product as dp','pd.design_product_id','=','dp.id')
                    ->leftJoin('products as p','pd.product_id','=','p.id')
                    ->leftJoin('misc_type as mt','dp.size_group_id','=','mt.id')
                    ->leftJoin('color as c','pd.color_id','=','c.id')
                    ->select($listArr)
                    ->where($where)
                    ->GroupBy('pd.id')
                    ->get();
        
        return $result;
    }

    public function getShippingBoxes($data)
    {
        $whereBoxConditions = ['sb.shipping_id' => $data['shipping_id']];

        $listItemsArray = ['sb.id','sb.box_qnty','sb.tracking_number',DB::raw('COUNT(bi.id) as number_of_box'),'sb.box_qnty as boxed_qnty','c.name as color_name','p.description as product_desc','pd.size','sb.md','sb.spoil','sb.actual','mt.value as size_group_name','p.name as product_name'];

        $shippingBoxes = DB::table('shipping_box as sb')
                        ->leftJoin('box_product_mapping as bi','bi.box_id','=','sb.id')
                        ->leftJoin('purchase_detail as pd','bi.item_id','=','pd.id')
                        ->leftJoin('design_product as dp','pd.design_product_id','=','dp.id')
                        ->leftJoin('products as p','pd.product_id','=','p.id')
                        ->leftJoin('misc_type as mt','mt.id','=','dp.size_group_id')
                        ->leftJoin('color as c','pd.color_id','=','c.id')
                        ->select($listItemsArray)
                        ->where($whereBoxConditions)
                        ->GroupBy('sb.id')
                        ->get();

        return $shippingBoxes;
    }

    public function getTotalShipCharge($order_id)
    {
        $listArr = [DB::raw('SUM(cost_to_ship) as total')];
        $where = ['order_id' => $order_id];

        $result = DB::table('shipping')
                ->select($listArr)
                ->where($where)
                ->get();

        return $result;
    }

    public function getItemsByProductAddress($product_address_id)
    {
        $shippingItems = DB::select("SELECT 
                                    (pol.qnty_purchased - pol.short) - pd.remaining_qnty as qnty,pd.size
                                FROM product_address_mapping as pam 
                                LEFT JOIN product_address_size_mapping as pas ON pam.id = pas.product_address_id 
                                LEFT JOIN purchase_detail as pd ON pas.purchase_detail_id = pd.id 
                                LEFT JOIN purchase_order_line as pol ON pd.id = pol.purchase_detail 
                                LEFT JOIN design_product as dp ON pd.design_product_id = dp.id
                                LEFT JOIN products as p ON dp.product_id = p.id
                                LEFT JOIN misc_type as mt ON dp.size_group_id = mt.id
                                LEFT JOIN color as c ON pd.color_id = c.id
                                WHERE pas.product_address_id = '".$product_address_id."'
                                GROUP BY pd.id ");

        return $shippingItems;
    }

    public function getshippedProductsByOrder($order_id)
    {
        $listArr = ['mt.value as misc_value','p.name','c.name as color_name','p.description','pd.id','pd.size','pas.distributed_qnty','pas.product_address_id'];
        $where = ['pam.order_id' => $order_id];

        $result = DB::table('product_address_mapping as pam')
                    ->leftJoin('product_address_size_mapping as pas','pam.id','=','pas.product_address_id')
                    ->leftJoin('purchase_detail as pd','pas.purchase_detail_id','=','pd.id')
                    ->leftJoin('design_product as dp','pd.design_product_id','=','dp.id')
                    ->leftJoin('products as p','pd.product_id','=','p.id')
                    ->leftJoin('misc_type as mt','dp.size_group_id','=','mt.id')
                    ->leftJoin('color as c','pd.color_id','=','c.id')
                    ->select($listArr)
                    ->where($where)
                    ->GroupBy('p.id')
                    ->get();
        
        return $result;
    }

    public function getShipToAddress($address_id)
    {
        $result = DB::table('product_address_mapping as pam')
                    ->leftJoin('client_distaddress as cd','pam.address_id','=','cd.id')
                    ->leftJoin('state as st','cd.state','=','st.id')
                    ->select('cd.*','st.code')
                    ->where('pam.address_id','=',$address_id)
                    ->GroupBy('cd.id')
                    ->get();
        
        return $result;
    }
}