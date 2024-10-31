<?php
function nelsFormFunctionAjx(){
    if ( isset($_REQUEST) ) 
    {
        $email = $_REQUEST['email'];
        $m_a_id = $_REQUEST['m_a_id'];
        $m_a_k = $_REQUEST['m_a_k'];
        $array_m_a_k = explode('-',$m_a_k);
        $r_e_a = $_REQUEST['r_e_a'];

        $list_id = $m_a_id;
        $authToken = $m_a_k;
        // The data to send to the API
        $postData = array(
            "email_address" => $email,
            "status" => "subscribed", 
        );
        // Setup cURL
        $ch = curl_init('https://'.$array_m_a_k[1].'.api.mailchimp.com/3.0/lists/'.$list_id.'/members/');
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array(
                'Authorization: apikey '.$authToken,
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => json_encode($postData)
        ));
        // Send the request
        $response = curl_exec($ch);
        $Object = json_decode($response);
        $Array  = (array)$Object;

        global $wpdb;
        $table_name = $wpdb->prefix . 'newsletter_email';        
        $user_id = $wpdb->get_row("SELECT * FROM " . $table_name . " WHERE email = '".$email."' ");
        if(empty($user_id)){
            $success = $wpdb->insert(
                $table_name, 
                array(
                    'email' => $email,
                    'status' => 'active',
                    'created_date' => date('Y-m-d h:i:s')
                )
            );
            if($success){
                echo 1;
                $to = $r_e_a;
                $subject = 'Newsletter Email Subscribe From '.$email;
                $message_body = "<table width='600' border='0' cellpadding='3' cellspacing='3'>
                                    <tr>
                                        <td colspan='2' align='center'>
                                            <table width='600' cellpadding='3' cellspacing='3' border='0'>
                                                <tr>
                                                    <td>
                                                        <table width='100%' cellspacing='0' cellpadding='12' border='1' bordercolor='#919191'>
                                                            <tr>
                                                                <td width='32%' align='left'><font face='Arial, Helvetica, sans-serif' size='4'>Email Address :</font></td>
                                                                <td width='68%' align='left'><font face='Arial, Helvetica, sans-serif' size='4'>".$email."</font></td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>";
                $headers = array('Content-Type: text/html; charset=UTF-8');
                $EmailSent = wp_mail( $to, $subject, $message_body, $headers);
            }else{
                echo 0;
            }
        }else{
            echo 2;
        }
    }
    die;
}
add_action( 'wp_ajax_nelsFormFunctionAjx', 'nelsFormFunctionAjx' );
add_action( 'wp_ajax_nopriv_nelsFormFunctionAjx', 'nelsFormFunctionAjx' );
?>