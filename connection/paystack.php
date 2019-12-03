<?php
require_once(__DIR__ .'/../includes/autoload.php');
$igniter = new Not_CIClass;
// $framework = new framework;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $session_data = $igniter->userdata('params'); 

    $paystack = new Yabacon\Paystack($configuration['pay_private_key']);

    $trx = $paystack->transaction->verify( [ 'reference' => $_GET['reference'] ] );
    if(!$trx->status){
        $Arr = array('status' => 0, 'header' => '', 'response' => $trx->message); 
    } elseif('success' == $trx->data->status){
        // use trx info including metadata and session info to confirm that cartid
        // matches the one for which we accepted payment
        $params = array(
            'status' => $trx->data->status,
            'reference' => $_GET['reference']
        );
        $response = array(
            'message' => 'Your payment was successful, please don\'t close this page, we\'re redirecting you!',
            'short_message' => 'Your payment was successful',
            'error_message' => 'Your payment was successful, but an error occurred and we were unable to process your payment!'
        );
        
        $igniter->set_userdata("paystack", $params);
        $success = success();
        if ($success) {
            $Arr = array('status' => 1, 'header' => $success, 'response' => $response);
        } else {
            $Arr = array('status' => 0, 'header' => '', 'response' => $response['error_message']);
        }
        $igniter->unset_userdata("paystack");
        $igniter->unset_userdata("params");
    } else {
        $Arr = array('status' => 0, 'header' => '', 'response' => $trx);
    }      
} else {
    $Arr = array('status' => 0, 'header' => site_url('parent/parents/getfees/'), 'response' => '');
}
echo json_encode($Arr, JSON_UNESCAPED_SLASHES);

function success() {
    global $igniter, $framework;
    $status = $igniter->userdata('paystack');
    $params = $igniter->userdata('params');

    // if ($status) { 
    if ($status['status'] == 'success' && $status['reference'] == $params['reference']) { 
        $params = array(  
            'email' => $params['email'],
            'user_id' => $params['user_id'],
            'firstname' => $params['firstname'],
            'lastname' => $params['lastname'],
            'total' => $params['total'],
            'release_id' => $params['release_id'], 
            'reference' => $params['reference'],
            'detail' => $params['payment_detail']
        ); 
        $data = [];
        foreach ($params as $p => $vl) {
            $data[] .= '\''.$vl.'\'';
        } 
        $data = implode(',', $data);
        $insert = $framework->dbProcessor("INSERT INTO payments (`email`, `uid`, `fname`, `lname`, `amount`, `rid`, `reference`, `details`) VALUES (".$data.")", 0, 3); 
        $inserted_id = $framework->dbProcessor("SELECT * FROM payments WHERE `id` = '$insert'", 1)[0];

        if ($inserted_id) {
            $invoice_detail = json_encode($inserted_id);
            // $invoice_detail = json_decode($inserted_id);
            // redirect(base_url("parent/payment/successinvoice/" . $invoice_detail->invoice_id . "/" . $invoice_detail->sub_invoice_id));
            return base_url('distribution&action=manage&set=publish&pay=success&rel_id='.$params['release_id'].'&invoice='.$inserted_id['id']);
        } else {
            return false;
        } 
    }
}
