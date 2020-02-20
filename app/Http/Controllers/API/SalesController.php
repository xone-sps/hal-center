<?php

namespace App\Http\Controllers\API;

use App\Libraries\AllSettingFormat;
use App\Libraries\Permissions;
use App\Libraries\Email;
use App\Models\Branch;
use App\Models\CashRegister;
use App\Models\CashRegisterLog;
use App\Models\CustomerGroup;
use App\Models\EmailTemplate;
use App\Models\InvoiceTemplate;
use App\Models\Order;
use App\Models\Setting;
use App\Models\OrderItems;
use App\Models\Payments;
use App\Models\PaymentType;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use App\Models\ShortcutKey;
use App\Models\Tax;
use App\Models\CustomUser;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\RestaurantTable;
use App\Models\Notification;
use App\User;
use function Couchbase\defaultDecoder;
use Faker\Provider\DateTime;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\API\BranchController;
use App\Http\Controllers\API\PaymentController;
use phpDocumentor\Reflection\Types\Array_;
use PDF;
use Config;

class SalesController extends Controller
{
    private $paymentController;

    public function __construct()
    {
        $this->paymentController = new PaymentController;
    }

    public function permissionCheck()
    {
        $controller = new Permissions;
        return $controller;
    }

    public function index()
    {
        $allSettings = new AllSettingFormat;
        $BranchController = new BranchController;
        $getBranch = $BranchController->index();
        $totalBranch = sizeof($getBranch);

        $paymentTypes = $this->paymentController->getData();
        $autoInvoice = $this->paymentController->getAutoInvoice();
        $customer = Customer::getCustomerDetails();

        $customerGroup = CustomerGroup::allData();
        $cashRegisterID = $this->getCashRegisterID();
        $salesReturnStatus = Setting::getSettingValue('sales_return_status')->setting_value;
        $salesType = Setting::getSaleOrReceivingType('sales_type');
        $invoiceData = $this->invoiceData();
        $userID = Auth::user()->id;
        $currentBranch = Setting::currentBranch($userID);
        $holdOrders = $this->getHoldOrder();
        $restaurantTables = RestaurantTable::all();

        $defaultInvoiceTemplate = InvoiceTemplate::getDefaultTemplate();
        $bookedTables = Order::getBookedTables();

        $output = [
            'currentBranch' => $allSettings->getCurrentBranch(),
            'totalBranch' => $totalBranch,
            'currentCashRegister' => $cashRegisterID,
            'salesReturnStatus' => $salesReturnStatus,
            'salesType' => $salesType,
            'branches' => $getBranch,
            'autoInvoice' => $autoInvoice['autoInvoice'],
            'paymentTypes' => $paymentTypes,
            'customer' => $customer,
            'customerGroup' => $customerGroup,
            'invoicePrefix' => $invoiceData['prefix'],
            'invoiceSuffix' => $invoiceData['suffix'],
            'lastInvoiceNum' => $invoiceData['lastInvoiceNum'],
            'appName' => '',
            'isBranchSelected' => false,
            'product' => null,
            'shortcutKeyCollection' => null,
            'holdOrders' => $holdOrders,
            'defaultInvoiceTemplateForSales' => $defaultInvoiceTemplate['sales_invoice']['invoice_template'],
            'restaurantTables' => $restaurantTables,
            'bookedTables' => $bookedTables,
        ];


        if ($currentBranch != null) {
            $product = $this->getProduct('sales', $currentBranch->setting_value);
            $output['isBranchSelected'] = true;
            $output['product'] = null;
            $output['shortcutKeyCollection'] = $product['shortcutKeyCollection'];
        }
        return view('sales.SalesIndex', $output);
    }

    public function invoiceData()
    {
        $invoicePrefix = Setting::getSettingValue('invoice_prefix')->setting_value;
        $invoiceSuffix = Setting::getSettingValue('invoice_suffix')->setting_value;
        $lastInvoiceNum = Setting::getSettingValue('last_invoice_number')->setting_value;

        return ['prefix' => $invoicePrefix, 'suffix' => $invoiceSuffix, 'lastInvoiceNum' => $lastInvoiceNum];
    }

    public function getRegisterAmount($id)
    {
        return CashRegisterLog::getRegisterAmount($id);
    }

    public function getCashRegisterID()
    {
        $userID = Auth::user()->id;
        $currentBranch = Setting::currentBranch($userID);
        $currentBranchID = 0;

        if ($currentBranch) {
            $currentBranchID = $currentBranch->setting_value;
        }

        $cashRegisterID = CashRegisterLog::getRegistersLog($currentBranchID, $userID);

        if ($cashRegisterID) {
            return $cashRegisterID = CashRegister::getCashRegisters($cashRegisterID->cash_register_id);
        } else {
            return $cashRegisterID = null;
        }
    }

    public function orderReceive()
    {
        $allSettings = new AllSettingFormat;
        $BranchController = new BranchController;
        $getBranch = $BranchController->index();
        $totalBranch = sizeof($getBranch);

        $paymentTypes = $this->paymentController->getData();
        $autoInvoice = $this->paymentController->getAutoInvoice();
        $supplier = Supplier::all();
        $cashRegisterID = $this->getCashRegisterID();
        $receivingType = Setting::getSaleOrReceivingType('receiving_type');
        $invoiceData = $this->invoiceData();
        $userID = Auth::user()->id;
        $currentBranch = Setting::currentBranch($userID);
        $defaultInvoiceTemplate = InvoiceTemplate::getDefaultTemplate();

        $output = [
            'currentBranch' => $allSettings->getCurrentBranch(),
            'totalBranch' => $totalBranch,
            'currentCashRegister' => $cashRegisterID,
            'receivingType' => $receivingType,
            'branches' => $getBranch,
            'paymentTypes' => $paymentTypes,
            'autoInvoice' => $autoInvoice['autoInvoice'],
            'supplier' => $supplier,
            'invoicePrefix' => $invoiceData['prefix'],
            'invoiceSuffix' => $invoiceData['suffix'],
            'lastInvoiceNum' => $invoiceData['lastInvoiceNum'],
            'appName' => '',
            'isBranchSelected' => false,
            'product' => null,
            'shortcutKeyCollection' => null,
            'defaultInvoiceTemplateForReceives' => $defaultInvoiceTemplate['receive_invoice']['invoice_template']
        ];
        if ($currentBranch != null) {
            $product = $this->getProduct('receiving', $currentBranch->setting_value);
            $output['isBranchSelected'] = true;
            $output['product'] = $product['products'];
            $output['shortcutKeyCollection'] = $product['shortcutKeyCollection'];
        }
        return view('receives.ReceivesIndex', $output);
    }

    public function getReturnProduct(Request $request)
    {
        $orderId = $request->orderId;

        $orderItems = Order::searchOrders($orderId);

        foreach ($orderItems as $rowOrderItem) {
            $rowOrderItem->cart = OrderItems::getAll(['price', 'discount', 'product_id as productID', 'type as orderType', 'tax_id as taxID', 'quantity', 'variant_id as variantID', 'note as cartItemNote'], 'order_id', $rowOrderItem->orderID);
            foreach ($rowOrderItem->cart as $rowItem) {
                if ($rowItem->taxID) {
                    $rowItem->productTaxPercentage = Tax::getFirst('percentage', 'id', $rowItem->taxID)->percentage;
                } else {
                    $rowItem->productTaxPercentage = 0;
                }
                if ($rowItem->variantID != null) {
                    $rowItem->variantTitle = ProductVariant::getFirst('variant_title', 'id', $rowItem->variantID)->variant_title;
                    $rowItem->productTitle = Product::getFirst('title', 'id', $rowItem->productID)->title;
                }

                $rowItem->showItemCollapse = false;
                $rowItem->calculatedPrice = $rowItem->quantity * $rowItem->price;
            }

            if ($rowOrderItem->customer != null) {
                $rowOrderItem->customer = Customer::getFirst(['first_name', 'last_name', 'email', 'id'], 'id', $rowOrderItem->customer);
                $rowOrderItem->customer->customer_group_discount = 0;
            }
        }

        return $orderItems;
    }

    public function setSalesReturnsType(Request $request)
    {
        $salesReturnType = $request->salesOrReturnType;
        Setting::updateSetting('sales_return_status', $salesReturnType);
    }

    public function getProductNew(Request $request)
    {
        $shortcutSettings = $this->getShortcutSettings();
        $options = array();
        $options['fields'] = 0;
        $options['limit'] = $request->rowLimit;
        $options['offset'] = $request->offset;
        $options['searchValue'] = $request->searchValue;
        $options['branchId'] = $request->currentBranch;
        $options['orderType'] = $request->orderType;
        $options['onlyInStockProducts'] = false;

        if ($options['orderType'] == "sales"){
            $options['onlyInStockProducts'] = Setting::getOneSetting("out_of_stock_products")->setting_value;
        }

        $products = Product::getAllProducts($options);
        $productVariants = Product::getProductVariantsList($options['branchId'], $options['orderType'], $options['onlyInStockProducts']);
        
        foreach ($products as $product) {
            $variants = [];
            foreach ($productVariants as $key => $value) {
                if ($value->product_id == $product->productID) {

                    $value->attribute_values = explode(",", $value->attribute_values);
                    array_push($variants, $value);
                }
            }

            $product->variants = $variants;
            $product->attributeName = explode(",", $product->attributeName );
        }

        return [
            'products' => $products,
            'count' => count($products),
            'barcodeResultValue' => null,
            'shortcutKeyCollection' => $shortcutSettings,
        ];
    }


    public function getProduct($orderType, $currentBranch)
    {

        $outOfStock = 0;
        $shortcutSettings = $this->getShortcutSettings();
        $data = Product::index(['products.id as productID', 'products.title', 'products.taxable', 'products.tax_type', 'products.tax_id', 'products.imageURL as productImage', 'products.branch_id']);

        foreach ($data as $rowData) {

            if ($rowData->taxable == 0) {
                $rowData->taxPercentage = 0;
            } else {

                if ($rowData->tax_type == 'default') {

                    $branchTax = Branch::getFirst('*', 'id', $currentBranch);
                    if ($branchTax->taxable == 0) {
                        $rowData->taxPercentage = 0;
                    } else {

                        if ($branchTax->is_default == 0) {
                            $taxID = $branchTax->tax_id;
                        } else {
                            $taxID = Tax::getFirst('id', 'is_default', 1)->id;
                        }

                        $rowData->taxPercentage = Tax::getFirst('percentage', 'id', $taxID)->percentage;
                    }
                } else {
                    $rowData->taxPercentage = Tax::getFirst('percentage', 'id', $rowData->tax_id)->percentage;
                }
            }

            $productVariant = ProductVariant::getProductVariant($rowData->productID, $orderType, $outOfStock);

            foreach ($productVariant as $rowProductVariant) {
                $rowProductVariant->attribute_values = explode(',', $rowProductVariant->attribute_values);
                $rowProductVariant->availableQuantity = OrderItems::availableQuantity($rowProductVariant->id);
            }

            $attribute_name = [];
            $attribute_id = ProductAttributeValue::attributeValues($rowData->productID);

            foreach ($attribute_id as $key => $rowAttributeId) {
                $attribute_name[$key] = ProductAttribute::getFirst('name', 'id', $rowAttributeId->attribute_id)->name;
            }

            //$rowData->variants = $productVariant;
            $rowData->attributeName = $attribute_name;
        }

        return [
            'products' => $data,
            'shortcutKeyCollection' => $shortcutSettings,
        ];
    }

    private function getShortcutSettings()
    {
        $allKeyboardShortcut = ShortcutKey::allData();
        return ['allKeyboardShortcut' => $allKeyboardShortcut];
    }

    public function barCodeSearch($searchValueForBarCode, $orderType)
    {
        $barCodeSearch = ProductVariant::searchProduct($searchValueForBarCode, $orderType);

        if ($barCodeSearch) {
            $barCodeSearch->cartItemNote = '';
            $barCodeSearch->discount = 0;
            $barCodeSearch->quantity = 1;
            $barCodeSearch->showItemCollapse = false;
            $barCodeSearch->discountType = '%';
            $barCodeSearch->calculatedPrice = $barCodeSearch->price;

            if ($barCodeSearch->taxable == 0) {
                $barCodeSearch->productTaxPercentage = 0;
            } else {
                if ($barCodeSearch->tax_type == 'default') {
                    $branchTax = Branch::getFirst('is_default', 'id', $barCodeSearch->branch_id)->is_default;

                    if ($branchTax == 0) {
                        $taxID = Branch::getFirst('tax_id', 'id', $barCodeSearch->branch_id)->tax_id;
                    } else {
                        $taxID = Tax::getFirst('id', 'is_default', 1)->id;
                    }
                    $barCodeSearch->productTaxPercentage = Tax::getFirst('percentage', 'id', $taxID)->percentage;
                } else {
                    $barCodeSearch->productTaxPercentage = Tax::getFirst('percentage', 'id', $barCodeSearch->taxID)->percentage;
                }
            }
        }

        unset($barCodeSearch->tax_type);
        unset($barCodeSearch->taxable);
        return $barCodeSearch;
    }

    public function setBranch(Request $request)
    {
        $allSetting = new AllSettingFormat;
        $authID = Auth::user('id')->id;
        $branchID = $request->branchID;
        $orderType = $request->orderType;
        $currentBranch = $allSetting->getCurrentBranch();
        $products = $this->getProduct($orderType, $branchID);
        if ($currentBranch) {
            Setting::updateCurrentBranch($authID, $branchID);
        } else {
            Setting::store([
                'setting_name' => 'current_branch',
                'setting_value' => $branchID,
                'setting_type' => 'user',
                'user_id' => $authID,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return ['products' => $products['products']];
    }

    public function salesStore(Request $request)
    {
        $cashRegister = $request->cashRagisterId;
        $allSettings = new AllSettingFormat;
        $userId = Auth::id();
        $userBranchId = Setting::getFirst('*', 'user_id', $userId)->setting_value;
        $date = Carbon::now()->toDateString();
        $orderType = $request->orderType;
        $salesOrReceivingType = $request->salesOrReceivingType;
        $orderStatus = $request->status;
        $createdBy = Auth::user()->id;
        $carts = $request->cart;
        $id = $request->customer ? $request->customer['id'] : null;
        $subTotal = $request->subTotal;
        $tax = $request->tax;
        $allDiscount = $request->discount;
        $grandTotal = $request->grandTotal;
        $payment = $request->payments;
        $orderID = $request->orderID;
        $transferBranch = $request->transferBranch;
        $transferBranchName = $request->transferBranchName;
        $dueAmount = 0;
        $time = $request->time;
        $restaurantTableId = $request->tableId;
        $lastInvoiceNumber = Setting::getSettingValue('last_invoice_number')->setting_value;


        if ($request->profit == null) {
            $profit = 0;
        } else {
            $profit = $request->profit;
        }

        $invoiceFixes = $allSettings->getInvoiceFixes();

        if ($allSettings->getCurrentBranch()->is_cash_register == 1) {
            $cashRegisterID = $this->getCashRegisterID()->id;
        } else {
            $cashRegisterID = null;
        }

        if ($allDiscount == null) {
            $allDiscount = 0;
        }

        if (!empty($payment)) {
            foreach ($payment as $key => $value) {
                if ($value['paymentType'] == 'credit') {
                    $dueAmount = floatval($value['paid']);
                }
            }
        }
        if (($orderStatus == 'done' && !$orderID) || ($orderStatus == 'pending' && !$orderID) || ($orderStatus == 'hold' && !$orderID)) {

            $orderData = array();
            $orderData['date'] = $date;
            $orderData['order_type'] = $orderType;
            $orderData['all_discount'] = $allDiscount;
            $orderData['sub_total'] = $subTotal;
            $orderData['total_tax'] = $tax;
            $orderData['due_amount'] = $dueAmount;
            $orderData['total'] = $grandTotal;
            $orderData['type'] = $salesOrReceivingType;
            $orderData['profit'] = $profit;
            $orderData['status'] = $orderStatus;
            $orderData['table_id'] = $restaurantTableId;

            if ($salesOrReceivingType == 'internal') {
                $orderData['transfer_branch_id'] = $transferBranch;
            }

            if ($orderType == 'sales') {
                $orderData['customer_id'] = $id;
            } else {
                $orderData['supplier_id'] = $id;
            }

            $orderData['created_by'] = $createdBy;
            $orderData['branch_id'] = $userBranchId;
            $orderData['created_at'] = Carbon::parse($time);

            if ($orderData['table_id']) {
                RestaurantTable::updateTableStatus($orderData['table_id'], 'booked');
            }

            $orderLastId = Order::store($orderData);

            $orderID = $orderLastId->id;

            Order::updateData($orderID, ['invoice_id' => $invoiceFixes['prefix'] . $lastInvoiceNumber . $invoiceFixes['suffix']]);
            $lastInvoiceNumber += 1;

            $lastUpdatedInvoice = Setting::where('setting_name', 'last_invoice_number')->first()->setting_value;
            if ($lastInvoiceNumber > $lastUpdatedInvoice) {
                Setting::updateSetting('last_invoice_number', $lastInvoiceNumber);
            }
        } else {

            $orders = array();
            $orders['date'] = $date;
            $orders['order_type'] = $orderType;
            $orders['all_discount'] = $allDiscount;
            $orders['sub_total'] = $subTotal;
            $orders['total_tax'] = $tax;
            $orders['total'] = $grandTotal;
            $orders['type'] = $salesOrReceivingType;
            $orders['status'] = $orderStatus;
            $orders['table_id'] = $restaurantTableId;
            $orders['due_amount'] = $dueAmount;

            if ($salesOrReceivingType == 'internal') {
                $orders['transfer_branch_id'] = $transferBranch;
            }
            if ($orderType == 'sales') {
                $orders['customer_id'] = $id;
            } else {
                $orders['supplier_id'] = $id;
            }
            $orders['created_by'] = $createdBy;

            if ($orders['table_id']) {
                RestaurantTable::updateTableStatus($orders['table_id'], 'available');
            }
            Order::updateData($request->orderID, $orders);
        }

        $orderItems = [];

        foreach ($carts as $cart) {
//            dd($cart);

            if ($orderType == 'sales') {
                $quantity = -$cart['quantity'];
            } else {
                $quantity = $cart['quantity'];
            }

            if (!array_key_exists('discount', $cart) || $cart['discount'] == null) {
                $cart['discount'] = 0;
            }

            array_push($orderItems, [
                'product_id' => $cart['productID'],
                'variant_id' => $cart['variantID'],
                'type' => $cart['orderType'],
                'quantity' => $quantity,
                'price' => $cart['price'],
                'discount' => $cart['discount'],
                'sub_total' => $cart["calculatedPrice"],
                'tax_id' => $cart['taxID'],
                'order_id' => $orderID,
                'note' => $cart['cartItemNote'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            // for update isNotify porduct_varient
            if (isset($cart['variantID'])) {
                ProductVariant::removeBranchFromIsNotity($cart['variantID'], $request->branchId);
            }
        }

        if ($orderStatus != 'hold') {
            if (sizeof($payment) > 0) {
                $paymentArray = [];

                foreach ($payment as $rowPayment) {
                    array_push($paymentArray, ['date' => $date, 'paid' => $rowPayment['paid'], 'exchange' => $rowPayment['exchange'], 'payment_method' => $rowPayment['paymentID'], 'options' => serialize($rowPayment['options']), 'order_id' => $orderID, 'cash_register_id' => $cashRegisterID, 'created_at' => $rowPayment['PaymentTime']]);
                }

                if (($orderStatus == 'done' && !$orderID) || ($orderStatus == 'pending' && !$orderID)) {
                    Payments::insertData($paymentArray);
                } else {
                    Payments::deleteRecord('order_id', $request->orderID);
                    Payments::insertData($paymentArray);
                }
            }
        }
        if (($orderStatus == 'done' && $orderID == null)) {
            OrderItems::insertData($orderItems);
            $response = [
                'invoiceID' => $invoiceFixes['prefix'] . $invoiceFixes['lastInvoiceNumber'] . $invoiceFixes['prefix'],
            ];
            return $response;
        } else if (($orderStatus == 'pending' && $orderID == null)) {
            OrderItems::insertData($orderItems);
            $response = [
                'orderID' => $orderID
            ];

            return $response;
        } else {
            OrderItems::deleteRecord('order_id', $request->orderID);
            OrderItems::insertData($orderItems);

            if ($orderStatus == 'done') {
                // send customer invoice
                try {
                    $invoiceTemplateEmail = new InvoiceTemplateController();
                    $invoiceTemplateData = $invoiceTemplateEmail->getInvoiceTemplateToPrint($orderID, $salesOrReceivingType, $transferBranchName, $cashRegister, $orderType, 'email');
                    $autoEmailReceive = Setting::getSettingValue('auto_email_receive')->setting_value;
                    $orderDetails = Order::orderDetails($orderID, $cashRegister);

                    if ($orderDetails->customer_id) {
                        $orderCustomer = Customer::getOne($orderDetails->customer_id);

                        if ($autoEmailReceive == 1 && $orderCustomer->email) {

                            $content = EmailTemplate::select('template_subject', 'default_content', 'custom_content')->where('template_type', 'pos_invoice')->first();
                            $subject = $content->template_subject;
                            if ($content->custom_content) {
                                $text = $content->custom_content;
                            } else {
                                $text = $content->default_content;
                            }

                            $mailText = str_replace('{first_name}', $orderCustomer->first_name, str_replace('{invoice_id}', $orderDetails->invoice_id, str_replace('{app_name}', Config::get('app_name'), $text)));

                            $this->sendPdf($invoiceTemplateData['data'], $orderID, $cashRegister, $mailText, $orderCustomer->email, $subject);
                        }
                    }
                } catch (\Exception $e) {
                }

                $invoiceTemplate = new InvoiceTemplateController();
                $templateData = $invoiceTemplate->getInvoiceTemplateToPrint($orderID, $salesOrReceivingType, $transferBranchName, $cashRegister, $orderType, 'receipt');
                $lastInvoiceId = Setting::getSettingValue('last_invoice_number')->setting_value;
                $response = [
                    'orderID' => $orderID,
                    'invoiceID' => $invoiceFixes['prefix'] . $invoiceFixes['lastInvoiceNumber'] . $invoiceFixes['suffix'],
                    'message' => Lang::get('lang.payment_done_successfully'),
                    'invoiceTemplate' => $templateData,
                    'lastInvoiceId' => $lastInvoiceId,
                ];

                return $response;
            } else {

                $lastInvoiceId = Setting::getSettingValue('last_invoice_number')->setting_value;
                $response = [
                    'orderID' => $orderID,
                    'invoiceID' => $invoiceFixes['prefix'] . $invoiceFixes['lastInvoiceNumber'] . $invoiceFixes['suffix'],
                    'message' => Lang::get('lang.payment_done_successfully'),
                    'lastInvoiceId' => $lastInvoiceId,
                ];

                return $response;
            }
        }
    }

    public function saveDueAmount(Request $request)
    {

        $data = $request->cartItemsToStore;

        $orderId = $data['rowData']['id'];
        $paymentType = $data['paymentType'];
        $date = Carbon::now()->toDateString();
        $payments = $data['payments'];
        $cashRegisterID = null;
        $output = null;

        $allSettings = new AllSettingFormat;
        $userId = Auth::id();

        if ($allSettings->getCurrentBranch()->is_cash_register == 1) {
            $cashRegisterID = $this->getCashRegisterID()->id;
        } else {
            $cashRegisterID = null;
        }

        $deleteRow = Payments::destroyByOrderAndType($orderId, $paymentType);

        if (isset($payments)) {
            $paymentArray = [];
            $due = 0;
            foreach ($payments as $rowPayment) {
                array_push(
                    $paymentArray,
                    [
                        'date' => $date,
                        'paid' => $rowPayment['paid'],
                        'exchange' => $rowPayment['exchange'],
                        'payment_method' => $rowPayment['paymentID'],
                        'options' => serialize($rowPayment['options']),
                        'order_id' => $orderId,
                        'cash_register_id' => $cashRegisterID,
                        'created_at' => $rowPayment['PaymentTime']
                    ]
                );

                if ($rowPayment['paymentType'] == 'credit') {
                    $due = $rowPayment['paid'];
                }
            }
            $updateData = [
                'due_amount' => $due
            ];
            Order::updateData($orderId, $updateData);
            if (isset($paymentArray)) {
                $output = Payments::insertData($paymentArray);
            }
        }

        if ($output) {
            return [
                'orderID' => $orderId,
                'message' => Lang::get('lang.payment_done_successfully')
            ];
        } else {
            return [
                'orderID' => $orderId,
                'message' => Lang::get('lang.something_went_wrong')
            ];
        }
    }

    public function salesCancel(Request $request)
    {
        $orderId = $request->orderID;

        if (Order::checkExists('id', $orderId)) {
            Order::updateData($orderId, ['status' => 'cancelled']);
        }
    }

    public function getPaymentsAndDetails(Request $request)
    {
        $orderId = $request->orderID;
        $payments = [];

        if ($orderId) {
            $payments = Payments::getAll('*', 'order_id', $orderId);
        }

        return $payments;
    }

    public function customerList(Request $request)
    {
        $searchValue = $request->customerSearchValue;

        if ($request->orderType == 'sales') {

            return Customer::customerData($searchValue);
        } else {
            return Supplier::supplierData($searchValue);
        }
    }

    public function getHoldOrder()
    {
        $orderHoldItems = Order::getHoldOrders();

        //check if it return empty
        if (count($orderHoldItems) > 0) {
            foreach ($orderHoldItems as $rowOrderItem) {

                $allOrderItems = OrderItems::getAll(['price', 'discount', 'product_id as productID', 'type as orderType', 'tax_id as taxID', 'quantity', 'variant_id as variantID', 'note as cartItemNote'], 'order_id', $rowOrderItem->orderID);

                //check if it return empty
                if (count($allOrderItems) > 0) {
                    $rowOrderItem->cart = $allOrderItems;
                    foreach ($rowOrderItem->cart as $rowItem) {

                        if ($rowItem->taxID) {
                            $rowItem->productTaxPercentage = Tax::getFirst('percentage', 'id', $rowItem->taxID)->percentage;
                        } else {
                            $rowItem->productTaxPercentage = 0;
                        }

                        if ($rowItem->variantID != null) {

                            $rowItem->variantTitle = ProductVariant::getFirst('variant_title', 'id', $rowItem->variantID)->variant_title;
                            $rowItem->productTitle = Product::getFirst('title', 'id', $rowItem->productID)->title;
                        }

                        $rowItem->quantity = abs($rowItem->quantity);
                        $rowItem->showItemCollapse = false;
                        $rowItem->calculatedPrice = $rowItem->quantity * $rowItem->price;
                    }

                    //time as per settings
                    $rowOrderItem->time = Carbon::parse($rowOrderItem->date)->format('H:i:s');

                    if ($rowOrderItem->customer != null) {
                        $rowOrderItem->customer = Customer::getFirst(['first_name', 'last_name', 'email', 'id'], 'id', $rowOrderItem->customer);
                        $rowOrderItem->customer->customer_group_discount = 0;
                    }
                }
            }
        }

        return $orderHoldItems;
    }

    public function sendPdf($templateData, $orderID, $cashRegister, $mailText, $email, $subject)
    {
        try {
            $allSettingFormat = new AllSettingFormat();
            $order = $this->formatOrdersDetails($orderID, $cashRegister);
            $order->due = $allSettingFormat->getCurrencySeparator($order->due);
            $orderItems = $this->formatOrdersItems($orderID);
            $appName = Config::get('app_name');
            $invoiceLogo = Config::get('invoiceLogo');
            $fileNameToStore = 'Gain-' . $order->invoice_id . '.pdf';
            $pdf = PDF::loadView('invoice.invoiceTemplate', compact('templateData', 'orderItems', 'order', 'appName', 'invoiceLogo'));
            $pdf->save('uploads/pdf/' . $fileNameToStore);
            $emailSend = new Email;
            $emailSend->sendEmail($mailText, $email, $subject, $fileNameToStore);
            unlink(public_path('uploads/pdf/' . $fileNameToStore));
        } catch (\Exception $e) {
        }
    }

    public function formatOrdersDetails($orderID, $cashRegister)
    {

        $orderDetails = Order::getInvoiceData($orderID, $cashRegister);

        $allSettingFormat = new AllSettingFormat();
        $orderDetails->due = $orderDetails->total - $orderDetails->paid;

        $orderDetails->paid = $allSettingFormat->getCurrencySeparator($orderDetails->paid);
        $orderDetails->total = $allSettingFormat->getCurrencySeparator($orderDetails->total);
        $orderDetails->sub_total = $allSettingFormat->getCurrencySeparator($orderDetails->sub_total);
        $orderDetails->change = $allSettingFormat->getCurrencySeparator($orderDetails->change);
        $orderDetails->date = $allSettingFormat->getDate($orderDetails->date);


        if ($orderDetails->change == null) {
            $orderDetails->change = 0;
        }

        return $orderDetails;
    }

    public static function formatOrdersItems($orderID)
    {
        $orderItems = OrderItems::getOrderDetails($orderID, true);
        $allSettingFormat = new AllSettingFormat();
        foreach ($orderItems as $item) {
            if ($item->type == 'discount') {
                $item->price = null;
                $item->quantity = null;
                $item->discount = null;
                $item->total = $allSettingFormat->getCurrencySeparator($item->sub_total);
            } else {
                $item->discount = $item->discount . '%';
                $item->price = $allSettingFormat->getCurrencySeparator($item->price);
                $item->total = $allSettingFormat->getCurrencySeparator($item->sub_total);
            }
        }

        return $orderItems;
    }

    public function setSalesReceivingType(Request $request)
    {
        $salesOrReceivingType = $request->salesOrReceivingType;
        $orderType = $request->orderType;
        Setting::saveSalesOrReceivingType($salesOrReceivingType, $orderType);
    }

    public function offlineSalesStore(Request $request)
    {
        $numberOfOrdersPlaced = count($request->all());
        $count = 0;
        //DB::beginTransaction();
        foreach ($request->all() as $singleOrder) {

            $dueAmount = 0;
            $customerId = 0;

            if ($singleOrder['isCashRegisterBranch'] == true) {
                $cashRegisterID = $singleOrder['cashRagisterId'];
            } else $cashRegisterID = null;

            //profit
            if ($singleOrder['profit'] == null) $profit = 0;
            else $profit = $singleOrder['profit'];

            //discount
            $allDiscount = 0;

            if (array_key_exists('discount', $singleOrder) && $singleOrder['discount'] != null) {
                $allDiscount = $singleOrder['discount'];
            }

            //due
            if ($singleOrder['status'] == 'done') {
                if (!empty($singleOrder['payments'])) {
                    foreach ($singleOrder['payments'] as $key => $value) {
                        if ($value['paymentType'] == 'credit') {
                            $dueAmount = floatval($value['paid']);
                        }
                    }
                }
            }
            $orderData = array();

            //customer id / supplier id
            if ($singleOrder['orderType'] == 'sales') {
                if (array_key_exists('customer', $singleOrder) && $singleOrder['customer'] != null) {
                    if (array_key_exists('id', $singleOrder['customer'])) {
                        $orderData['customer_id'] = $singleOrder['customer']['id'];
                        $customerId = $orderData['customer_id'];
                    } else {
                        $orderData['customer_id'] = Customer::getInsertedId($singleOrder['customer']);
                        $customerId = $orderData['customer_id'];
                    }
                } else {
                    $orderData['customer_id'] = null;
                }
            } else {
                if (array_key_exists('customer', $singleOrder) && $singleOrder['customer'] != null) {
                    if (array_key_exists('id', $singleOrder['customer'])) {
                        $orderData['supplier_id'] = $singleOrder['customer']['id'];
                    } else {
                        $orderData['supplier_id'] = Supplier::getInsertedId($singleOrder['customer']);
                    }
                } else {
                    $orderData['supplier_id'] = null;
                }
            }
            if (array_key_exists('transferBranch', $singleOrder)) $orderData['transfer_branch_id'] = $singleOrder['transferBranch'];

            $orderData['date'] = Carbon::parse($singleOrder['date']);
            $orderData['order_type'] = $singleOrder['orderType'];
            $orderData['sub_total'] = $singleOrder['subTotal'];
            $orderData['total_tax'] = $singleOrder['tax'];
            $orderData['due_amount'] = $dueAmount;
            $orderData['total'] = $singleOrder['grandTotal'];
            $orderData['type'] = $singleOrder['salesOrReceivingType'];
            $orderData['profit'] = $profit;
            $orderData['status'] = $singleOrder['status'];
            $orderData['all_discount'] = $allDiscount;
            $orderData['table_id'] = $singleOrder['tableId'];


            if (array_key_exists('selectedBranchID', $singleOrder)) $orderData['branch_id'] = $singleOrder['selectedBranchID'];
            if (array_key_exists('invoice_id', $singleOrder)) $orderData['invoice_id'] = $singleOrder['invoice_id'];
            if (isset($singleOrder['invoice_id'])) $invoiceIdExistOrNot = Order::getIdOfExisted('invoice_id', $singleOrder['invoice_id']);

            $orderData['created_by'] = $singleOrder['createdBy'];
            $orderData['created_at'] = Carbon::parse($singleOrder['time']);


            if ($singleOrder['orderID'] != null) {
                Order::updateData($singleOrder['orderID'], $orderData);
                $orderID = $singleOrder['orderID'];
            } else {
                $orderLastId = Order::store($orderData);
                $orderID = $orderLastId->id;
            }

            $lastUpdatedInvoice = Setting::where('setting_name', 'last_invoice_number')->first()->setting_value;

            if (empty($invoiceIdExistOrNot) && array_key_exists('current_invoice_number', $singleOrder)) {
                if ($singleOrder['current_invoice_number'] > $lastUpdatedInvoice) {
                    Setting::updateSetting('last_invoice_number', $singleOrder['current_invoice_number']);
                }
            }

            $orderItems = [];
            //cart data insert in order_items
            foreach ($singleOrder['cart'] as $cart) {

                if ($singleOrder['orderType'] == 'sales') $quantity = -$cart['quantity'];
                else $quantity = $cart['quantity'];

                if (!array_key_exists('discount', $cart) || $cart['discount'] == null) $cart['discount'] = 0;

                array_push($orderItems, [
                    'product_id' => $cart['productID'],
                    'variant_id' => $cart['variantID'],
                    'type' => $cart['orderType'],
                    'quantity' => $quantity,
                    'price' => $cart['price'],
                    'discount' => $cart['discount'],
                    'sub_total' => $cart["calculatedPrice"],
                    'tax_id' => $cart['taxID'],
                    'order_id' => $orderID,
                    'note' => $cart['cartItemNote'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            // payment items insert in payment table
            if ($singleOrder['status'] == 'done') {
                if (sizeof($singleOrder['payments']) > 0) {
                    $paymentArray = [];

                    foreach ($singleOrder['payments'] as $rowPayment) {
                        array_push($paymentArray, [
                            'date' => Carbon::parse($singleOrder['date']),
                            'paid' => $rowPayment['paid'],
                            'exchange' => $rowPayment['exchange'],
                            'payment_method' => $rowPayment['paymentID'],
                            'options' => serialize($rowPayment['options']),
                            'order_id' => $orderID,
                            'cash_register_id' => $cashRegisterID,
                            'created_at' => $rowPayment['PaymentTime']
                        ]);
                    }

                    Payments::insertData($paymentArray);
                }
            }
            if ($singleOrder['status'] == 'done' || $singleOrder['status'] == 'cancelled') {
                if ($singleOrder['orderID'] != null) OrderItems::deleteRecord('order_id', $singleOrder['orderID']);
                OrderItems::insertData($orderItems);
                //Send email and generate invoice
                try {
                    $salesOrReceivingType = $singleOrder['salesOrReceivingType'];
                    $transferBranchName = $request->transferBranchName;

                    $invoiceTemplateEmail = new InvoiceTemplateController();
                    $invoiceTemplateData = $invoiceTemplateEmail->getInvoiceTemplateToPrint($orderID, $salesOrReceivingType, $transferBranchName, $cashRegisterID, $singleOrder['orderType'], 'email');

                    $autoEmailReceive = Setting::getSettingValue('auto_email_receive')->setting_value;

                    if ($customerId) {
                        $orderCustomer = Customer::getOne($customerId);

                        if ($autoEmailReceive == 1 && $orderCustomer->email) {

                            $content = EmailTemplate::select('template_subject', 'default_content', 'custom_content')->where('template_type', 'pos_invoice')->first();
                            $subject = $content->template_subject;

                            if ($content->custom_content) $text = $content->custom_content;
                            else $text = $content->default_content;

                            $mailText = str_replace('{first_name}', $orderCustomer->first_name, str_replace('{invoice_id}', $singleOrder['invoice_id'], str_replace('{app_name}', Config::get('app_name'), $text)));


                            $this->sendPdf($invoiceTemplateData['data'], $orderID, $cashRegisterID, $mailText, $orderCustomer->email, $subject);
                        }
                    }
                } catch (\Exception $e) {
                }
                $count++;
            } elseif ($singleOrder['status'] == 'hold') {
                if ($singleOrder['orderID'] != null) OrderItems::deleteRecord('order_id', $singleOrder['orderID']);
                OrderItems::insertData($orderItems);
                $count++;
            } else {
                $count--;
            }
        }

        $lastInvoiceNumber = Setting::where('setting_name', 'last_invoice_number')->first()->setting_value;

        if ($numberOfOrdersPlaced == $count) {
            //DB::commit();
            $response = [
                'message' => Lang::get('lang.sync_complete_your_all_sales_now_up_to_date'),
                'lastInvoiceNumber' => $lastInvoiceNumber
            ];
            return response()->json($response, 201);
        } else {
            //DB::rollback();
            $response = [
                'message' => Lang::get('lang.something_went_wrong'),
            ];
            return response()->json($response, 400);
        }
    }

    public function salesDue(Request $request, $id)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $filtersData = $request->filtersData;
        $searchValue = $request->searchValue;
        $requestType = $request->reqType;

        $due = OrderItems::dueItems($id, $filtersData, $searchValue, $request->columnSortedBy, $limit, $request->rowOffset, $columnName, $requestType);

        if (empty($requestType)) {
            $dueData = $due['data'];
        } else {
            $dueData = $due;
        }

        if (empty($requestType)) {
            $dueItems = $this->calculateDues($dueData);

            $arrayCount = $dueItems['count'];
            $totalCount = count($due['allData']);
            $dueData[$arrayCount] = [
                'invoice_id' => Lang::get('lang.total'),
                'item_purchased' => $dueItems['netItem'],
                'tax' => $dueItems['netTax'],
                'discount' => $dueItems['discount'],
                'total' => $dueItems['netTotal'],
                'due_amount' => $dueItems['netDueAmount']
            ];

            $grandCalculation = $this->calculateDues($due['allData']);

            $dueData[$arrayCount + 1] = [
                'invoice_id' => Lang::get('lang.grand_total'),
                'item_purchased' => $grandCalculation['netItem'],
                'tax' => $grandCalculation['netTax'],
                'discount' => $grandCalculation['discount'],
                'total' => $grandCalculation['netTotal'],
                'due_amount' => $dueItems['netDueAmount']
            ];
            return ['datarows' => $dueData, 'count' => $totalCount];
        } else {
            $this->calculateDues($dueData);
            return ['datarows' => $dueData];
        }
    }

    public function calculateDues($dueData)
    {
        $netTotal = 0;
        $netTax = 0;
        $netItem = 0;
        $arrayCount = 0;
        $netDiscount = 0;
        $netDueAmount = 0;

        foreach ($dueData as $rowData) {
            if ($rowData->type == 'customer') {
                $rowData->type = Lang::get('lang.customer');
            } else {
                $rowData->type = Lang::get('lang.internal_sales');
                $rowData->customer = $rowData->transfer_branch_name;
            }
            if ($rowData->customer == '') $rowData->customer = Lang::get('lang.walk_in_customer');
            $netTax += $rowData->tax;
            $netTotal += $rowData->total;
            $netItem += $rowData->item_purchased;
            $netDiscount += $rowData->discount;
            $netDueAmount += $rowData->due_amount;
            $arrayCount++;
        }

        return [
            'netTotal' => $netTotal,
            'netTax' => $netTax,
            'netItem' => $netItem,
            'discount' => $netDiscount,
            'count' => $arrayCount,
            'netDueAmount' => $netDueAmount
        ];
    }
}
