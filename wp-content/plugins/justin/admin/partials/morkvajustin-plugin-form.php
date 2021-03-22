<?php
require("functions.php");

mnp_display_nav(); ?>

<?php
require 'api.php';
require 'createttn.php';

if (!isset($_SESSION)) {
    session_start();
}

//set order id if  HTTP REFFERRER  is woocommerce order
if (isset($_SERVER['HTTP_REFERER'])) {
    $qs = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY);
    if (!empty($qs)) {
        parse_str($qs, $output);
        // TODO check for key existence
        if (isset($output['post'])) {
            $order_id =  $output['post'];  // id
        }
    }
}

//if isset order from previous step id and not null srialize order id to session
//else do  not show ttn form
if (isset($order_id) && ($order_id != 0) &&  wc_get_order($order_id)) {
    $order_data0 = wc_get_order($order_id);
    if (isset($order_data0) && (!$order_data0 == false)) {
        $order_data = $order_data0->get_data();
        $_SESSION['order_id'] = serialize($order_id);
    } else {
        $showpage =false;
    }
}

//if isset order id only from session  get it
elseif (isset($_SESSION['order_id'])) {
    //$order_id = 0;
    $ret = @unserialize($_SESSION['order_id']);
    if (gettype($ret) == 'boolean') {
        $order_id = $_SESSION['order_id'];
    } else {
        $order_id = unserialize($_SESSION['order_id']);
    }
    if (wc_get_order($order_id)) {
        $order_data0 = wc_get_order($order_id);
        $order_data = $order_data0->get_data();
    }
    // echo '<pre>';
    // print_r($order_data);
    // echo '</pre>'
}
//else do not show form ttn
else {
    $showpage =false;
}
if(!isset($order_data['id'])){
  $order_data = [
    'id'=> null,
    'billing'=>[
      'first_name'=>'',
      'last_name' =>'',
      'city'=>'',
      'phone'=>'',
      'address_1'=>''
    ]
  ];
}


// echo '<pre>';
// print_r($order_data);
// echo '</pre>';
?>
<h2>Функціонал на стадії тесування</h2>
<div class="">
   <div class="container">
      <form class="form-invoice form-invoice-3cols"  method="post" name="invoice">
         <div id="messagebox" class="messagebox_show">
         </div>
         <?php  justin_formlinkbox($order_data['id']); ?>
         <div class="tablecontainer">
            <table class="form-table full-width-input">
               <tbody id="tb1">
                  <tr>
                     <th colspan="2">
                        <h3 class="formblock_title">Відправник</h3>
                        <div id="errors"></div>
                     </th>
                  </tr>
                  <tr>
                     <th scope="row">
                        <label for="sender_name">Відправник (П. І. Б)</label>
                     </th>
                     <td>
                      <input style="display:text" type="text" id="sender_name" name="invoice_sender_name" class="input sender_name" value="<?php echo get_option('justin_names'); ?>">
                     </td>
                  </tr>
                  <tr>
                     <th scope="row">
                        <label for="sender_namecity">Місто</label>
                     </th>
                     <td>
                        <input id="sender_namecity" type="text" value="<?php echo get_option('woocommerce_morkvajustin_shipping_method_city_name'); ?>" readonly="" name="invoice_sender_city">
                     </td>
                  </tr>
                  <tr>
                     <th scope="row">
                        <label for="sender_phone">Телефон</label>
                     </th>
                     <td>
                        <input type="text" id="sender_phone" name="invoice_sender_phone" class="input sender_phone" value="<?php echo get_option('justin_phone'); ?>">
                     </td>
                  </tr>
                  <tr>
                     <th scope="row">
                        <label for="invoice_description">Опис відправлення</label>
                     </th>
                     <td class="pb7">
                        <textarea type="text" id="invoice_description" name="invoice_description" class="input" minlength="1" required=""><?php echo get_option('justin_invoice_description'); ?></textarea>
                        <p id="error_dec"></p>
                     </td>
                  </tr>
               </tbody>
            </table>
            <table class="form-table full-width-input">
               <tbody>
                  <tr>
                     <th colspan="2">
                        <h3 class="formblock_title">Одержувач</h3>
                        <div id="errors"></div>
                     </th>
                  </tr>
                  <tr>
                     <th scope="row">
                        <label for="recipient_name">Одержувач (П.І.Б)</label>
                     </th>
                     <td>
                        <input type="text" name="invoice_recipient_name" id="recipient_name" class="input" recipient_name="" value="<?php echo $order_data['billing']['first_name']." ".$order_data['billing']['last_name']; ?>">
                     </td>
                  </tr>
                  <tr>
                     <th scope="row">
                        <label for="recipient_city">Місто одержувача</label>
                     </th>
                     <td>
                        <input type="text" name="invoice_recipient_city" id="recipient_city" class="recipient_city" value="<?php echo $order_data['billing']['city']; ?>">
                     </td>
                  </tr>
                  <tr>
                     <th scope="row">
                        <label for="RecipientAddressName">відділення:</label>
                     </th>
                     <td>
                        <textarea name="addresstext"><?php echo $order_data['billing']['address_1']; ?></textarea>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row">
                        <label for="recipient_phone">Телефон</label>
                     </th>
                     <td>
                        <input type="text" name="invoice_recipient_phone" class="input recipient_phone" id="recipient_phone" value="<?php echo $order_data['billing']['phone']; ?>">
                     </td>
                  </tr>
               </tbody>
            </table>

         </div>
         <div class="tablecontainer">
            <table class="form-table full-width-input">
               <tbody>
                  <tr>
                     <th colspan="2">
                        <h3 class="formblock_title">Параметри відправлення</h3>
                        <div id="errors"></div>
                     </th>
                  </tr>
                  <tr>
                     <th scope="row"><label>Запланована дата:</label></th>
                     <?php $today = date('Y-m-d'); ?>
                     <td><input type="date" name="invoice_datetime" value="<? echo $today; ?>" min="<? echo $today; ?>">
                     </td>
                  </tr>
                  <tr>
                     <th scope="row">
                        <label for="invoice_payer">Платник</label>
                     </th>
                     <td>
                        <select id="invoice_payer" name="invoice_payer">
                           <option value="Recipient" selected="">Отримувач</option>
                           <option value="Sender">Відправник</option>
                        </select>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row"><label class="light" for="invoice_cargo_mass">Вага, кг</label></th>
                     <td><input type="text" name="invoice_cargo_mass" id="invoice_cargo_mass" value="1.2">
                     </td>
                  </tr>
                  <tr>
                     <td colspan="2">
                       <p class="light">Якщо залишити порожнім, буде використано мінімальне значення 0.5.</p>
                     </td>
                  </tr>
                  <tr>
                     <td class="pb0">
                        <label class="light" for="invoice_volumei">Об'єм, м<sup>3</sup></label>
                     </td>
                     <td class="pb0">
                        <input type="text" id="invoice_volumei" name="invoice_volume" value="0">
                     </td>
                  </tr>
                  <tr>
                     <td colspan="2">
                        <p></p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row">
                        <label for="invoice_placesi">Кількість місць</label>
                     </th>
                     <td>
                        <input type="text" id="invoice_placesi" name="invoice_places" value="1">
                     </td>
                  </tr>
                  <input type="hidden" name="InfoRegClientBarcodes" value="13812">
                  <tr>
                     <th scope="row">
                        <label for="invoice_priceid">Оголошена вартість</label>
                     </th>
                     <td>
                        <input id="invoice_priceid" type="text" name="invoice_price" required="" value="42.00">
                     </td>
                  </tr>
                  <tr>
                     <th colspan="2">
                        <p class="light">Якщо залишити порожнім, плагін використає вартість замовлення</p>
                     </th>
                  </tr>
                  <tr>
                     <th scope="row">
                        <label for="invoice_redelivery">Наложений платіж</label>
                     </th>
                     <td>
                        <select class="invoice_redelivery" name="invoice_redelivery">
                          <option value="ON">є</option>
                          <option value="OFF">немає</option>
                        </select>
                     </td>
                  </tr>
               </tbody>
            </table>
            <table class="form-table full-width-input">
               <tbody>
                  <tr>
                     <td>
                        <input name="manual_ttn" type="submit" value="Створити" class="checkforminputs button button-primary" id="submit">
                     </td>
                  </tr>
               </tbody>
            </table>
         </div>
         <div>
            <?php require 'card.php' ; ?>
            <div class="clear"></div>
         </div>
      </form>
   </div>
</div>
