<?php

namespace App\Models;

use DB;

class ProductVariant extends BaseModel

{
    protected $fillable = ['sku', 'product_id', 'variant_title', 'attribute_values', 'variant_details', 'purchase_price', 'selling_price', 'enabled', 'bar_code', 're_order', 'imageURL'];

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public static function allProductVariantById($id)
    {
        return ProductVariant::where('product_id', $id)->get();
    }

    public static function inventoryReports($filtersData, $searchValue, $columnName, $columnSortedBy, $limit, $offset, $requestType)
    {
        $query = ProductVariant::select(
            'product_variants.id',
            'product_variants.sku as sku',
            'product_variants.product_id',
            'product_variants.variant_title as variantTitle',
            'product_variants.purchase_price',
            'product_variants.selling_price',
            'products.title as porductTitle',
            'products.taxable',
            'product_categories.name as categoryTitle',
            'product_brands.name as brandTitle',
            'product_groups.name as groupTitle',
            'products.taxable',
            'products.tax_type',
            'products.tax_id as taxID',
            'product_variants.re_order as re_order',
            DB::raw('CONVERT(sum(order_items.quantity),SIGNED INTEGER) as inventory')
        )
            ->leftJoin('order_items', 'order_items.variant_id', '=', 'product_variants.id')->groupBy('order_items.variant_id')
            ->leftJoin('products', 'products.id', '=', 'product_variants.product_id')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->leftJoin('product_brands', 'products.brand_id', '=', 'product_brands.id')
            ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('product_groups', 'products.group_id', '=', 'product_groups.id')->where('product_variants.enabled', 1);

        if (!empty($filtersData)) {
            foreach ($filtersData as $singleFilter) {
                if ($singleFilter['key'] == "branchName") {
                    $query->where('orders.branch_id', '=', $singleFilter['value']);
                } else if ($singleFilter['key'] == "categoryName") {
                    $query->where('products.category_id', '=', $singleFilter['value']);
                } else if ($singleFilter['key'] == "brandName") {
                    $query->where('products.brand_id', '=', $singleFilter['value']);
                } else if ($singleFilter['key'] == "groupName") {
                    $query->where('products.group_id', '=', $singleFilter['value']);
                } else if ($singleFilter['value'] == "yes") {
                    $query->groupBy('order_items.variant_id')
                        ->having('product_variants.re_order', '>=', DB::raw('sum(order_items.quantity)'));
                } else if ($singleFilter['value'] == "no") {
                    $query->groupBy('order_items.variant_id')
                        ->having('product_variants.re_order', '<', DB::raw('sum(order_items.quantity)'));
                }
            }
        }
        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('products.title', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('product_variants.variant_title', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('product_variants.sku', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('product_categories.name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('product_groups.name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('product_brands.name', 'LIKE', '%' . $searchValue . '%');
            });
        }
        if (empty($requestType)) {
            $count = $query->get()->count();
            $data = $query->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

            return ['data' => $data, 'count' => $count];
        } else {
            $data =  $query->orderBy($columnName, $columnSortedBy)->get();

            return ['data' => $data];
        }
    }

    public static function getVariantData($id, $value)
    {
        return ProductVariant::where('product_id', $id)->where('attribute_values', $value)->first();
    }

    public static function getProductVariant($productId, $orderType, $outOfStock)
    {

        $query = ProductVariant::leftJoin('order_items', 'order_items.variant_id', '=', 'product_variants.id')->groupBy('product_variants.id')
            ->leftJoin('products', 'products.id', '=', 'product_variants.product_id')
//            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
//            ->leftJoin('product_brands', 'products.brand_id', '=', 'product_brands.id')
            ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
//            ->leftJoin('product_groups', 'products.group_id', '=', 'product_groups.id')
            ->where('product_variants.enabled', 1)
            ->where('products.id', '=', $productId);




        if ($orderType == 'sales') {
            $price = 'product_variants.selling_price as price';
        } else {
            $price = 'product_variants.purchase_price as price';
        }

        if ($outOfStock == 0){
            return $query->select('product_variants.id', 'product_variants.sku', 'product_variants.product_id', 'product_variants.variant_title', 'product_variants.attribute_values', 'product_variants.variant_details', 'product_variants.enabled', 'product_variants.imageURL', 'product_variants.bar_code', 'product_variants.re_order', 'product_variants.purchase_price', 'product_variants.selling_price', $price)->get();
        }else{
            //Without out of stock product

           $a = $query->select('product_variants.id', 'product_variants.sku', 'product_variants.product_id', 'product_variants.variant_title', 'product_variants.attribute_values', 'product_variants.variant_details', 'product_variants.enabled', 'product_variants.imageURL', 'product_variants.bar_code', 'product_variants.re_order', 'product_variants.purchase_price', 'product_variants.selling_price', $price)
           ->where( DB::raw('abs(sum(order_items.quantity))'), '>', 0 )->toSql();

           dd($a);
        }


    }

    public static function searchProduct($searchValueForBarCode, $orderType)
    {
        if ($orderType == 'sales') {
            $price = 'selling_price as price';
        } else {
            $price = 'purchase_price as price';
        }

        return  ProductVariant::leftJoin('products', 'products.id', '=', 'product_variants.product_id')
            ->select('products.title as productTitle', 'products.taxable', 'products.tax_type', 'products.tax_id as taxID', 'product_variants.selling_price', 'product_variants.purchase_price', 'product_variants.product_id as productID', 'product_variants.id as variantID', 'product_variants.variant_title as variantTitle', $price)
            ->where(function ($query) use ($searchValueForBarCode) {
                $query->where('product_variants.bar_code', '=', $searchValueForBarCode)
                    ->orWhere('product_variants.sku', '=', $searchValueForBarCode);
                // ->orWhere('product_variants.id', '=', $searchValueForBarCode);
            })
            ->orWhere(function ($query) use ($searchValueForBarCode) {
                $query->where('products.title', '=', $searchValueForBarCode)
                    ->where('products.product_type', '=', 'standard');
            })
            ->first();
    }

    public static function insertProductVariant($productId, $price)
    {
        return ProductVariant::insert(['product_id' => $productId, 'variant_title' => 'default_variant', 'attribute_values' => 'default_variant', 'price' => $price]);
    }

    public static function alreadyExisted($sku, $barcode, $title, $productType)
    {
        $data =  ProductVariant::select('product_variants.product_id as productId', 'product_variants.sku as sku', 'product_variants.bar_code as barCode','product_variants.id as variantId', 'product_variants.purchase_price as purchase_price', 'product_variants.selling_price as selling_price', 'products.title as title')
            ->join('products', 'products.id', '=', 'product_variants.product_id');

        if ($sku != null){
            $data->where('product_variants.sku', '=', $sku);
        }
        if ($barcode != null){
            $data->where('product_variants.bar_code', '=', $barcode);
        }
        if($productType =="standard"){
            $data->where('products.title', '=', $title);
        }
        return $data->count();
    }

    public static function ifExists($field, $data)
    {
        if ($data == null) $data = '';
        return ProductVariant::where($field, '=', $data)->count();
    }

    public static function getProductIdForStock($item)
    {
        $joinedTable = ProductVariant::select('product_variants.product_id as productId', 'product_variants.id as variantId', 'product_variants.purchase_price as purchase_price', 'product_variants.selling_price as selling_price', 'products.title as title')
            ->leftJoin('products', 'products.id', '=', 'product_variants.product_id');

        if (array_key_exists("SKU", $item)) {
            if ($item['SKU'] != null) {
                return $joinedTable->where('product_variants.sku', $item['SKU'])->first();
            } else {
                if (array_key_exists("BARCODE", $item)) {
                    if ($item['BARCODE'] != null) {
                        return $joinedTable->where('product_variants.bar_code', $item['BARCODE'])->first();
                    } else {
                        if($item['TITLE'] != null) {
                            return $joinedTable->where('products.title', $item['TITLE'])->where('products.product_type', '=', 'standard')->first();
                        } else return null;
                    }
                }
            }
        }
    }

    public static function productAlreadyExisted($sku, $barCode, $title, $productType)
    {
        $joinedTable = ProductVariant::select('product_variants.product_id as productId', 'product_variants.id as variantId', 'product_variants.purchase_price as purchase_price', 'product_variants.selling_price as selling_price', 'products.title as title')
            ->leftJoin('products', 'products.id', '=', 'product_variants.product_id');

        if ($sku != null) {
            $count = $joinedTable->where('product_variants.sku', $sku)->count();
            if ($count == 0) {
                if ($barCode != null) {
                    $count = $joinedTable->orWhere('product_variants.bar_code', $barCode)->count();
                    if ($count == 0) {
                        if ($productType == 'standard') {
                            $count = $joinedTable->orWhere('products.title', $title)->where('products.product_type', '=', 'standard')->count();
                            return $count;
                        } else {
                            return $count;
                        }
                    } else {
                        return $count;
                    }
                } else {
                    if ($productType == 'standard') {
                        $count = $joinedTable->orWhere('products.title', $title)->where('products.product_type', '=', 'standard')->count();
                        return $count;
                    } else {
                        return $count;
                    }
                }
            } else {
                return $count;
            }
        } else {
            if($barCode != null) {
                $count = $joinedTable->where('product_variants.bar_code', $barCode)->count();
                if ($count == 0) {
                    if ($productType == 'standard') {
                        $count = $joinedTable->orWhere('products.title', $title)->where('products.product_type', '=', 'standard')->count();
                        return $count;
                    } else {
                        return $count;
                    }
                } else {
                    return $count;
                }
            } else {
                if ($productType == 'standard') {
                    $count = $joinedTable->orWhere('products.title', $title)->where('products.product_type', '=', 'standard')->count();
                    return $count;
                } else {
                    return 0;
                }
            }
        }
    }

    public static function getSpecificColumn($column)
    {
        return ProductVariant::select($column)->get();
    }


    public static function getVariant($productId)
    {
        $variants =  ProductVariant::where('product_id', $productId)->select('id', 'variant_title', 'sku', 'bar_code')->get();

        foreach ($variants as $variant) {

            if ($variant->bar_code != null) {
                $barcode =  $variant->bar_code;
            } elseif ($variant->sku != null) {
                $barcode = $variant->sku;
            } else {

                $barcode = $variant->id;
            }

            $variant->newBarcode = $barcode;
        }

        return $variants;
    }

    public static function getSkuBarcode($column, $productId)
    {
        return ProductVariant::select($column)->where('product_id', '!=', $productId)->whereNotNull($column)->get();
    }

    public static function getVariantByProductId($id)
    {
        return ProductVariant::select(
            'id',
            'sku',
            'product_id',
            'variant_title',
            'attribute_values as variant',
            'variant_details',
            'purchase_price as purchasePrice',
            'selling_price as sellingPrice',
            'enabled',
            'imageURL',
            'bar_code as barcode',
            're_order as reOrder'
        )->where('product_id', $id)->get();
    }
    public static function getAllBarcode()
    {
        return ProductVariant::select('attribute_values as variant', 'bar_code as barcode')->where('bar_code', '!=', null)->get();
    }
    public static function getAllSku()
    {
        return ProductVariant::select('attribute_values as variant', 'sku as sku')->where('sku', '!=', null)->get();
    }
    public static function removeBranchFromIsNotity($variantId, $branchId)
    {
        $variant = ProductVariant::getOne($variantId);
        if (isset($variant->isNotify) && $variant->isNotify != null) {
            $notifyArr = explode(',', $variant->isNotify);
            $index = array_search($branchId,$notifyArr);
            if($index !== false) {
                unset($notifyArr[$index]);   
            }
            $newNotifyArr = implode(",", $notifyArr);
            ProductVariant::upDateIsNofity($variantId, $newNotifyArr);
        }
    }
    public static function filerNofityArr($value, $branchId){
        return $value != $branchId;
    }
    public static function upDateIsNofity($id, $data)
    {
        $data = ProductVariant::updateData($id, ["isNotify" => $data]);
    }
}
