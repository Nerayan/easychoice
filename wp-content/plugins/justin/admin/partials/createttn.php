<?php

  $justinapi = new JustinApi();

if(!empty($_POST['manual_ttn'])){



  $senddata = array(
    "number"                  =>"20190205",//"number":"201902010010",
    //Унікальний ідентифікатор замовлення користувача. Зверніть увагу!
    //По даному номеру необхідно звертатися в методах отримання стікеру (див. розділ 19).
    //Запланована дата передачі відправлення. Записується в одному із двох форматів:
    "date"                    =>$_POST['invoice_datetime'],
    //Унікальний ідентифікатор міставідправника
    "sender_city_id"          => "50a09bef-dc05-11e7-80c6-00155dfbfb00",
    //Відправник.
    "sender_company"          => "TESTAPI",
    //ПІП контактної особи відправника.
    "sender_contact"          => $_POST['invoice_sender_name'],
    //Номер контактного телефона відправника.
    "sender_phone"            => $_POST['invoice_sender_phone'],
    //Адреса відправника для отримання (забору) відправлення.
    "sender_pick_up_address"  => "01030, Київ, вул. Б.Хмельницького, 44",
    //Унікальний ідентифікатор складу відправника для отримання (забору) відправлення.
    "pick_up_is_required"     => true,
    //Номер відділення відправки (дані можна найти в API req_Departments – див. розділ 3).
    // Поле повинно бути обов’язково заповнено в випадку, якщо поле  «pick_up_is_required» має значення «false».
    "sender_branch"           => "58748012",
    //ПІП отримувача
    "receiver"                => $_POST['invoice_recipient_name'],
    //ПІП контакту отримувача (допускається пусте значення, якщо отримувач = контакт).
    "receiver_contact"        => $_POST['invoice_recipient_name'],
    //Телефон отримувача.
    "receiver_phone"          => $_POST["invoice_recipient_phone"],
    //Кількість вантажних місць. Якщо значення поля не дорівнює кількості елементів параметру «cargo_places_array»
    // (при умові що цей параметр не пустий) – буде повернена помилка.
    "count_cargo_places"      =>  $_POST['invoice_places'],
    //Номер відділення доставки (дані можна найти в API req_Departments – див. розділ 3).
    "branch"                  => "7100104224",
    //Об’єм відправлення (в м3)
    "volume"                  => ($_POST['invoice_volume']!=0) ? $_POST['invoice_volume'] : 0.02,
    //Вага відправлення (в кг).
    "weight"                  => ($_POST['invoice_cargo_mass']!=0) ? $_POST['invoice_cargo_mass'] : 0.5,
    //Вид ЕН: «0» (по замовчуванню) – B2C; «1» - C2C; «2» - B2B; «3» - C2B.
    "delivery_type"           => 0,
    //Вартість ЕН що декларується. Оголошена вартість
    "declared_cost"           => $_POST['invoice_price'],
    //Вартість доставки (в грн). Якщо 0 і значення поля delivery_payment_is_required =  true - розрахунок буде проводиться по тарифам.
    "delivery_amount"         => 0,
    //Вартість комісії за післяплату (в грн). Якщо 0 і redelivery_payment_is_required = true - розрахунок буде проводиться по тарифам.
    "redelivery_amount"       => 0,
    //Сума післяплати (в грн). Якщо сума післяплати не заокруглена буде повернена помилка.
    "order_amount"            => ($_POST['invoice_redelivery']=="ON") ? $_POST['invoice_price'] : 0,
    //Ідентифікатор, що визначає необхідність оплати комісії післяплати.
    "redelivery_payment_is_required"=> true,
    //Платник комісії післяплати (0 – відправник, 1 – отримувач, 2 – третя особа).
    "redelivery_payment_payer"=> 1,
    //Ідентифікатор, що визначає необхідність оплати доставки.
    "delivery_payment_is_required"=> true,
    //Платник доставки (0 – відправник, 1 – отримувач, 2 – третя особа).
    "delivery_payment_payer"=> ($_POST['invoice_payer']=="Recipient") ? 1 : 0,
    //Ідентифікатор, що визначає необхідність оплати післяплати.
    "order_payment_is_required"=> ($_POST['invoice_redelivery']=="ON") ? true : false,
  );


  $create_ttn_data = null;

  echo '<pre>';

  print_r($senddata);

  echo '</pre>';

  $result = $justinapi->createTtn(get_option('morkvajustin_apikey'), $senddata );




  echo '<pre>';

  print_r($_POST);

  echo '</pre>';

  echo '<pre>';
  print_r($result);
  echo '</pre>';
}

//$result = $justinapi->getAreas();
//$result = $justinapi->getCity();
//$result = $justinapi->getCity('Гостомель');
//$result = $justinapi->getCityStreets('32b69b95-9018-11e8-80c1-525400fb7782');
//$result = $justinapi->getWarehouses();
//$result = $justinapi->getWarehouses('acd34def-1d55-11e8-8e88-bc5ff4b8e882');


?>
