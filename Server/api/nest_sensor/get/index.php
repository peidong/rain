<?php
    header("Content-Type:application/json");

    // start the session
    session_start();
    $sn_number = $_GET['sn_number'];

    // close the session
    session_write_close();


    $data_array = get_nest_sensor_data();
    $humidity = $data_array['thermostats']['tM0i4N8lworgCTw1RFF1B5NeGNgy1FqW']['humidity'];
    $current_temperature = $data_array['thermostats']['tM0i4N8lworgCTw1RFF1B5NeGNgy1FqW']['ambient_temperature_f'];
    $co_alarm_state = $data_array['smoke_co_alarms']['rI1VFhpj1DFSXGHO6QnisJNeGNgy1FqW']['co_alarm_state'];
    $smoke_alarm_state = $data_array['smoke_co_alarms']['rI1VFhpj1DFSXGHO6QnisJNeGNgy1FqW']['smoke_alarm_state'];

    //$response['humidity'] = $humidity;
    //$response['current_temperature'] = $current_temperature;
    $response['co_alarm_state'] = $co_alarm_state;
    $response['smoke_alarm_state'] = $smoke_alarm_state;

    $water_time_min = process_sensor_data($sn_number, $smoke_alarm_state, $co_alarm_state);
    $response['water_time_min'] = $water_time_min;

    deliver_response(200, "The nest sensors' data has been got", $response);

    function process_sensor_data($sn_number, $smoke_alarm_state, $co_alarm_state){
        $water_zone_number = 1;
        $devid = get_devid($sn_number);
        $status = get_device_status($sn_number);
        $n_status = (int)$status;
        if ($smoke_alarm_state == "ok" && $co_alarm_state == "ok"){
            //stop watering
            if($n_status == 0){
                return -1;
            }else{
                send_RainClose_command($devid);
                return 0;
            }
        }else if ($smoke_alarm_state == "emergency" || $co_alarm_state == "emergency"){
            //water 5min
            if($n_status == $water_zone_number){
                return -3;
            }else{
                send_RainOnce_command($devid, 5);
                return 5;
            }
        }else{
            //water 1min
            if($n_status == $water_zone_number){
                return -2;
            }else{
                send_RainOnce_command($devid, 1);
                return 1;
            }
        }
    }

    function get_device_status($sn_number){
        $url = "http://www.rainconn.com/api/rest/client/devstatus?sn=$sn_number";
        $headers = array("Content-type: application/json;charset='utf-8'", "Accept: application/json", "Cache-Control: no-cache", "Pragma: no-cache",);
        $ch = curl_init();
        $timeout = 30;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $server_output = curl_exec($ch);
        curl_close($ch);
        $server_output_array = json_decode($server_output, true);
        $watering_zone_number = $server_output_array['data']['now'];
        return $watering_zone_number;
    }

    function send_RainOnce_command($devid, $water_time_min){
        $water_zone_number = 1;
        $url = "http://www.rainconn.com/api/rest/client/sendcommand?&devid=$devid&command=RainOnce&params=param:3;type:1;whichvalve:$water_zone_number;howlong:$water_time_min&srcip=www.rainconn.com&srctype=2";
        $headers = array("Content-type: application/json;charset='utf-8'", "Accept: application/json", "Cache-Control: no-cache", "Pragma: no-cache",);
        $ch = curl_init();
        $timeout = 30;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, "devid=$device_id&zone=$water_zone_number&watertime=$water_time_min");
        $server_output = curl_exec($ch);
        curl_close($ch);
    }

    function send_RainClose_command($devid){
        $water_zone_number = 1;
        $url = "http://www.rainconn.com/api/rest/client/sendcommand?&devid=$devid&command=RainClose&params=param:2;type:1;whichvalve:$water_zone_number";
        $headers = array("Content-type: application/json;charset='utf-8'", "Accept: application/json", "Cache-Control: no-cache", "Pragma: no-cache",);
        $ch = curl_init();
        $timeout = 30;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $server_output = curl_exec($ch);
        curl_close($ch);
    }

    function get_devid($sn_number){
        $url = "http://www.rainconn.com/api/rest/client/getdevidbysno?sno=$sn_number";
        $headers = array("Content-type: application/json;charset='utf-8'", "Accept: application/json", "Cache-Control: no-cache", "Pragma: no-cache",);
        $ch = curl_init();
        $timeout = 30;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $data = curl_exec($ch);
        curl_close($ch);
        $data_array = json_decode($data, true);
        $devid = $data_array['data']['devid'];
        return $devid;
    }

    function get_nest_sensor_data(){
        $url = "https://developer-api.nest.com/devices.json?auth=c.T1yBuMT940gRUCMCrdNgyw9f3D8jPDcDrq2JunxVxbZ9qsJk9V6Ji8bcgTRocZvh3wvDaG8TDbQ0eR5yRUuXLMyqlNheArebuhNLv0tknBjzcvVVdPmHqFFvmE5z0GrBOJV4hkCvMksLHwCA";
        $headers = array("Content-type: application/json;charset='utf-8'", "Accept: application/json", "Cache-Control: no-cache", "Pragma: no-cache",);
        $ch = curl_init();
        $timeout = 30;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $data = curl_exec($ch);
        curl_close($ch);
        $data_array = json_decode($data, true);
        return $data_array;
    }


    function deliver_response($status,$status_message,$data){
        header("HTTP/1.1 $status $status_message");
        $response['status']=$status;
        $response['status_message']=$status_message;
        $response['data']=$data;

        $json_response=json_encode($response);
        echo $json_response;
    }
?>
