<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ZohoAccess;

class ZohoToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update zoho access token';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //\Log::info("Running Zoho Access Token Cron.");

        $zoho = ZohoAccess::first(); // Get First Row
        //$zoho = ZohoAccess::latest()->first(); // Get Last Row

        $refresh_time = strtotime($zoho->updated_at) + 3300; //3300 = 55 min X 60 sec

        if($refresh_time <= time()) {        
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://accounts.zoho.com/oauth/v2/token",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "refresh_token=".$zoho->refresh_token."&client_id=".$zoho->client_id."&client_secret=".$zoho->client_secret."&grant_type=refresh_token",
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "content-type: application/x-www-form-urlencoded",
                    "postman-token: e6194f24-c078-2691-9266-4089f7f609ab"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if($err) {
                $error = "ZOHO Token Refresh Error #:" . $err;
                \Log::info($error);
            } else {
                $z_data = json_decode($response,true);
                ZohoAccess::where('id',1)->update(['access_token' => $z_data['access_token']]);
                \Log::info("Zoho Access Token Created: ".$response);
            }
        }
    }
}
