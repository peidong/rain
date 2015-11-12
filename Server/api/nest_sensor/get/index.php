<?php
    header("Content-Type:application/json");

    // start the session
    session_start();

    // close the session
    session_write_close();

    $data_array = get_nest_sensor_data();
    $humidity = $data_array['thermostats']['tM0i4N8lworgCTw1RFF1B5NeGNgy1FqW']['humidity'];
    $current_temperature = $data_array['thermostats']['tM0i4N8lworgCTw1RFF1B5NeGNgy1FqW']['ambient_temperature_f'];
    $co_alarm_state = $data_array['smoke_co_alarms']['rI1VFhpj1DFSXGHO6QnisJNeGNgy1FqW']['co_alarm_state'];
    $smoke_alarm_state = $data_array['smoke_co_alarms']['rI1VFhpj1DFSXGHO6QnisJNeGNgy1FqW']['smoke_alarm_state'];

    $response['humidity'] = $humidity;
    $response['current_temperature'] = $current_temperature;
    $response['co_alarm_state'] = $co_alarm_state;
    $response['smoke_alarm_state'] = $smoke_alarm_state;

    deliver_response(200, "The nest sensors' data has been got", $response);

    function process_sensor_data(){
        if ($smoke_alarm_state == "ok" && $co_alarm_state == "ok"){
            //stop watering
            send_water_command(0);
        }else if ($smoke_alarm_state == "emergency" || $co_alarm_state == "emergency"){
            //water 1min
            send_water_command(1);
        }else{
            //water 5min
            send_water_command(5);
        }
    }
            
    function send_water_command($water_time_min){
        $device_id = "";
        $water_zone_number = 1;
        $url = "";
        $headers = array("Content-type: application/json;charset='utf-8'", "Accept: application/json", "Cache-Control: no-cache", "Pragma: no-cache",);
        $ch = curl_init();
        $timeout = 30;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "devid=$device_id&zone=$water_zone_number&watertime=$water_time_min");
        $server_output = curl_exec($ch);
        curl_close($ch);
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
