<?php

  require __DIR__.'/../vendor/autoload.php';
  use net\authorize\api\contract\v1 as AnetAPI;
  use net\authorize\api\controller as AnetController;

  define("AUTHORIZENET_LOG_FILE", "phplog");

  function chargeCreditCard($data){
  	  $amount = $data['amount'];
  		

      // Common setup for API credentials
      $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
      $merchantAuthentication->setName(MERCHANT_LOGIN_ID);
      $merchantAuthentication->setTransactionKey(MERCHANT_TRANSACTION_KEY);
      $refId = 'ref' . time();

      // Create the payment data for a credit card
      $creditCard = new AnetAPI\CreditCardType();
      $creditCard->setCardNumber($data['card_num']);
      $creditCard->setExpirationDate($data['exp_date']);
      $creditCard->setCardCode($data['cardCode']);
      $paymentOne = new AnetAPI\PaymentType();
      $paymentOne->setCreditCard($creditCard);

      $order = new AnetAPI\OrderType();
      $order->setDescription("New Item");
      $order->setInvoiceNumber($data['invoice_num']);

      //create a transaction
      $transactionRequestType = new AnetAPI\TransactionRequestType();
      $transactionRequestType->setTransactionType( "authCaptureTransaction"); 
      $transactionRequestType->setAmount($amount);
      $transactionRequestType->setOrder($order);
      $transactionRequestType->setPayment($paymentOne);
      

      $request = new AnetAPI\CreateTransactionRequest();
      $request->setMerchantAuthentication($merchantAuthentication);
      $request->setRefId( $refId);
      $request->setTransactionRequest( $transactionRequestType);
      $controller = new AnetController\CreateTransactionController($request);
      if(AUTHORIZENET_SANDBOX){
      	$response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::SANDBOX);
      }else{
      	$response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::PRODUCTION);
      }
      
	  if ($response != null)
      {
        $tresponse = $response->getTransactionResponse();

        if (($tresponse != null) && ($tresponse->getResponseCode() == "1") )   
        {
          //echo "Charge Credit Card AUTH CODE : " . $tresponse->getAuthCode() . "\n";
          //echo "Charge Credit Card TRANS ID  : " . $tresponse->getTransId() . "\n";
        }
        else
        {
        	return $response;
        }
        
      }
      else
      {
        //echo  "Charge Credit card Null response returned";
      }
      return $response;
  }

?>
